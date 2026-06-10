<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class UserController extends Controller
{
    public function __invoke()
    {
        return view('admin.users.index', ['users' => User::with('roles')->latest()->paginate(12)]);
    }
}
