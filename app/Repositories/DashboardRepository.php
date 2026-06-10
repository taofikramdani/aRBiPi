<?php

namespace App\Repositories;

use App\Models\Question;
use App\Models\Result;
use App\Models\Subject;
use App\Models\Tryout;
use App\Models\User;

class DashboardRepository
{
    public function adminStats(): array
    {
        return [
            'students' => User::role('student')->count(),
            'subjects' => Subject::count(),
            'questions' => Question::count(),
            'tryouts' => Tryout::count(),
            'average' => round((float) Result::avg('score'), 1),
            'recentResults' => Result::with(['user', 'tryout'])->latest()->limit(8)->get(),
        ];
    }

    public function studentStats(User $user): array
    {
        return [
            'attempts' => $user->results()->count(),
            'average' => round((float) $user->results()->avg('score'), 1),
            'history' => $user->results()->with('tryout.subject')->latest()->limit(8)->get(),
            'recommendations' => $user->recommendations()->with('subject')->latest()->limit(4)->get(),
            'availableTryouts' => Tryout::with('subject')->withCount('questions')->where('is_published', true)->latest()->limit(6)->get(),
        ];
    }
}
