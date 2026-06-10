<?php

namespace App\Http\Controllers;

use App\Repositories\DashboardRepository;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardRepository $repo)
    {
        return $request->user()->hasRole('admin') ? view('admin.dashboard', $repo->adminStats()) : view('student.dashboard', $repo->studentStats($request->user()));
    }
}
