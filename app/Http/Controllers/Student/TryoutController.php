<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitAttemptRequest;
use App\Models\Attempt;
use App\Models\Tryout;
use App\Services\TryoutService;

class TryoutController extends Controller
{
    public function index()
    {
        return view('student.tryouts.index', ['tryouts' => Tryout::with('subject')->withCount('questions')->where('is_published', true)->latest()->paginate(9)]);
    }

    public function start(Tryout $tryout, TryoutService $s)
    {
        abort_unless($tryout->is_published, 404);

        return redirect()->route('student.attempts.show', $s->start($tryout, auth()->user()));
    }

    public function show(Attempt $attempt)
    {
        abort_unless($attempt->user_id === auth()->id() && $attempt->status === 'in_progress', 403);

        return view('student.tryouts.attempt', ['attempt' => $attempt->load('tryout.questions.options')]);
    }

    public function submit(SubmitAttemptRequest $r, Attempt $attempt, TryoutService $s)
    {
        abort_unless($attempt->user_id === $r->user()->id && $attempt->status === 'in_progress', 403);

        return redirect()->route('student.results.show', $s->submit($attempt, $r->validated('answers',[])));
    }
}
