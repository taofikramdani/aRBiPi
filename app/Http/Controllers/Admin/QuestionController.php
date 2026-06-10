<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\QuestionRequest;
use App\Models\Question;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $legacyError = (string) $request->session()->get('ai_generation_error', '');
        if (str_contains(strtolower($legacyError), 'gemini')) {
            $request->session()->forget('ai_generation_error');
        }

        $draft = $request->session()->get('ai_question_draft');
        if (is_array($draft) && collect($draft['questions'] ?? [])->contains(fn ($question) => str_contains($question['options']['A'] ?? '', 'Konsep utama'))) {
            $request->session()->forget('ai_question_draft');
        }

        return view('admin.questions.index', [
            'subjects' => Subject::with(['questions' => fn ($query) => $query->withCount('tryouts')->latest()])
                ->withCount('questions')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create()
    {
        return view('admin.questions.form', ['question' => new Question, 'subjects' => Subject::where('is_active', true)->get()]);
    }

    public function store(QuestionRequest $r)
    {
        $this->save($r, new Question);

        return redirect()->route('admin.questions.index')->with('success', 'Soal ditambahkan.');
    }

    public function show(Question $question)
    {
        return redirect()->route('admin.questions.edit', $question);
    }

    public function edit(Question $question)
    {
        return view('admin.questions.form', ['question' => $question->load('options'), 'subjects' => Subject::all()]);
    }

    public function update(QuestionRequest $r, Question $question)
    {
        $this->save($r, $question);

        return redirect()->route('admin.questions.index')->with('success', 'Soal diperbarui.');
    }

    public function destroy(Question $question)
    {
        $question->delete();

        return back()->with('success', 'Soal dihapus.');
    }

    private function save(QuestionRequest $r, Question $q): void
    {
        DB::transaction(function () use ($r, $q) {
            $q->fill($r->safe()->except(['options', 'correct_answer']))->fill(['created_by' => $r->user()->id])->save();
            $q->options()->delete();
            foreach ($r->validated('options') as $label => $text) {
                $q->options()->create(['label' => $label, 'option_text' => $text, 'is_correct' => $label === $r->validated('correct_answer')]);
            }
        });
    }
}
