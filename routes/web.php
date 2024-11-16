<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

// Group all authenticated routes
Route::middleware('auth')->group(function () {

   // Restore Task Route (specific route before resource)
    Route::patch('tasks/{task}/restore', [TaskController::class, 'restore'])->name('tasks.restore');

    // Tasks Drafts Route
    Route::get('/tasks/drafts', [TaskController::class, 'drafts'])->name('tasks.drafts');

    // Tasks Trash Route
    Route::get('/tasks/trash', [TaskController::class, 'trash'])->name('tasks.trash');

    // Resourceful Route for Tasks (excluding destroy)
    Route::resource('tasks', TaskController::class)->except(['destroy']);

    // Toggle Draft Route
    Route::patch('/tasks/{task}/toggle-draft', [TaskController::class, 'toggleDraft'])->name('tasks.toggleDraft');

    // Delete Task (move to trash)
    Route::delete('tasks/{task}', [TaskController::class, 'moveToTrash'])->name('tasks.delete');

    Route::delete('tasks/{task}/force-delete', [TaskController::class, 'forceDelete'])->name('tasks.forceDelete');

    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateTaskStatus'])->name('tasks.updateTaskStatus');
});

require __DIR__.'/auth.php';

