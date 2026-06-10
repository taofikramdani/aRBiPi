<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TryoutRequest;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Tryout;

class TryoutController extends Controller
{
    public function index()
    {
        return view('admin.tryouts.index', ['tryouts' => Tryout::with('subject')->withCount(['questions', 'attempts'])->latest()->paginate(10)]);
    }

    public function create()
    {
        return view('admin.tryouts.form', ['tryout' => new Tryout, 'subjects' => Subject::all(), 'questions' => Question::with('subject')->get()]);
    }

    public function store(TryoutRequest $r)
    {
        $t = Tryout::create($r->safe()->except('question_ids') + ['created_by' => $r->user()->id]);
        $this->sync($t, $r->validated('question_ids'));

        return redirect()->route('admin.tryouts.index')->with('success', 'Tryout ditambahkan.');
    }

    public function show(Tryout $tryout)
    {
        return redirect()->route('admin.tryouts.edit', $tryout);
    }

    public function edit(Tryout $tryout)
    {
        return view('admin.tryouts.form', ['tryout' => $tryout->load('questions'), 'subjects' => Subject::all(), 'questions' => Question::with('subject')->get()]);
    }

    public function update(TryoutRequest $r, Tryout $tryout)
    {
        $tryout->update($r->safe()->except('question_ids'));
        $this->sync($tryout, $r->validated('question_ids'));

        return redirect()->route('admin.tryouts.index')->with('success', 'Tryout diperbarui.');
    }

    public function destroy(Tryout $tryout)
    {
        $tryout->delete();

        return back()->with('success', 'Tryout dihapus.');
    }

    private function sync(Tryout $t, array $ids): void
    {
        $t->questions()->sync(collect($ids)->mapWithKeys(fn ($id, $i) => [$id => ['order' => $i + 1, 'points' => 1]])->all());
    }
}
