<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

// Group all authenticated routes
Route::middleware('auth')->group(function () {

    // Task routes
    Route::resource('tasks', TaskController::class)->except([ 'destroy']);
    Route::patch('/tasks/{task}/toggle-draft', [TaskController::class, 'toggleDraft'])->name('tasks.toggleDraft');
    // Route::get('/task/{id}/edit', [TaskController::class, 'editTask'])->name('tasks.edit');
    // Route::put('/task/{id}', [TaskController::class, 'updateTask'])->name('tasks.update');
    // Route::delete('/task/{id}/force-delete', [TaskController::class, 'forceDelete'])->name('tasks.force-delete');

    // // Subtask routes grouped under task
    // Route::prefix('task/{taskId}/subtask')->group(function () {
    //     Route::get('add', [TaskController::class, 'addSubtask'])->name('subtask.add');
    //     Route::get('{subId}/edit', [TaskController::class, 'editSubtask'])->name('subtask.edit');
    //     Route::patch('{subId}/update-status', [TaskController::class, 'updateStatus'])->name('subtask.update-status');
    // });

    // // Trash and restore task routes
    // Route::patch('task/{id}/restore', [TaskController::class, 'restoreTask'])->name('tasks.restore');
    // Route::delete('task/{id}/trash', [TaskController::class, 'trash'])->name('tasks.trash');
});

require __DIR__.'/auth.php';

