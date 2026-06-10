<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Result;

class ResultController extends Controller
{
    public function __invoke()
    {
        return view('admin.results.index', ['results' => Result::with(['user', 'tryout.subject'])->latest()->paginate(15)]);
    }
}
