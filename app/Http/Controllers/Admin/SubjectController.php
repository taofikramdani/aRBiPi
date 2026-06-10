<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubjectRequest;
use App\Models\Subject;

class SubjectController extends Controller
{
    public function index()
    {
        return view('admin.subjects.index', ['subjects' => Subject::withCount(['questions', 'tryouts'])->latest()->paginate(10)]);
    }

    public function create()
    {
        return view('admin.subjects.form', ['subject' => new Subject]);
    }

    public function store(SubjectRequest $r)
    {
        Subject::create($r->validated());

        return redirect()->route('admin.subjects.index')->with('success', 'Mata pelajaran ditambahkan.');
    }

    public function show(Subject $subject)
    {
        return redirect()->route('admin.subjects.edit', $subject);
    }

    public function edit(Subject $subject)
    {
        return view('admin.subjects.form', compact('subject'));
    }

    public function update(SubjectRequest $r, Subject $subject)
    {
        $subject->update($r->validated());

        return redirect()->route('admin.subjects.index')->with('success', 'Mata pelajaran diperbarui.');
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();

        return back()->with('success', 'Mata pelajaran dihapus.');
    }
}
