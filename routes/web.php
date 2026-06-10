<?php

use App\Http\Controllers\Admin\AiQuestionController;
use App\Http\Controllers\Admin\LearningMaterialController as AdminLearningMaterialController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\ResultController as AdminResultController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TryoutController as AdminTryoutController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Student\AssistantController;
use App\Http\Controllers\Student\LearningMaterialController as StudentLearningMaterialController;
use App\Http\Controllers\Student\ResultController;
use App\Http\Controllers\Student\TryoutController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resources(['subjects' => SubjectController::class, 'questions' => QuestionController::class, 'tryouts' => AdminTryoutController::class]);
    Route::resource('learning-materials', AdminLearningMaterialController::class)->except('show');
    Route::get('learning-materials/{learning_material}/open', [AdminLearningMaterialController::class, 'open'])->name('learning-materials.open');
    Route::get('users', UserController::class)->name('users.index');
    Route::get('results', AdminResultController::class)->name('results.index');
    Route::post('ai/questions', AiQuestionController::class)->name('ai.questions');
    Route::get('ai/questions/poll', [AiQuestionController::class, 'poll'])->name('ai.questions.poll');
    Route::post('ai/questions/store', [AiQuestionController::class, 'store'])->name('ai.questions.store');
    Route::delete('ai/questions/draft', [AiQuestionController::class, 'discard'])->name('ai.questions.discard');
});
Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('tryouts', [TryoutController::class, 'index'])->name('tryouts.index');
    Route::get('learning-materials', [StudentLearningMaterialController::class, 'index'])->name('learning-materials.index');
    Route::get('learning-materials/{learning_material}/open', [StudentLearningMaterialController::class, 'open'])->name('learning-materials.open');
    Route::post('tryouts/{tryout}/start', [TryoutController::class, 'start'])->name('tryouts.start');
    Route::get('attempts/{attempt}', [TryoutController::class, 'show'])->name('attempts.show');
    Route::post('attempts/{attempt}', [TryoutController::class, 'submit'])->name('attempts.submit');
    Route::get('results', [ResultController::class, 'index'])->name('results.index');
    Route::get('results/{result}', [ResultController::class, 'show'])->name('results.show');
    Route::get('assistant/messages', [AssistantController::class, 'index'])->name('assistant.index');
    Route::post('assistant/messages', [AssistantController::class, 'store'])->name('assistant.store');
    Route::delete('assistant/messages', [AssistantController::class, 'destroy'])->name('assistant.destroy');
});
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
require __DIR__.'/auth.php';
