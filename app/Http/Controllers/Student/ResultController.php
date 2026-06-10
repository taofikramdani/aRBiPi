<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Result;

class ResultController extends Controller
{
    public function index()
    {
        return view('student.results.index', ['results' => auth()->user()->results()->with('tryout.subject')->latest()->paginate(12)]);
    }

    public function show(Result $result)
    {
        abort_unless($result->user_id === auth()->id(), 403);

        return view('student.results.show', ['result' => $result->load(['tryout.subject', 'attempt.answers.question.options', 'recommendation'])]);
    }
}
