<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::middleware('auth')->group(function () {

    Route::get('/tasks', [TaskController::class, 'tasks'])->name('tasks');
    Route::get('/task/{id}', [TaskController::class, 'manageTask'])->name('task.manage'); //manage-task
    Route::get('/tasks/add', [TaskController::class, 'addTask'])->name('task.add'); //add-task
    Route::get('/task/{id}/edit', [TaskController::class, 'editTask'])->name('task.edit'); //update-task
    Route::post('/tasks', [TaskController::class, 'storeTask'])->name('task.store'); //store-task
    Route::put('/task/{id}', [TaskController::class, 'updateTask'])->name('task.update');

    Route::get('/task/{id}/add-subtask', [TaskController::class, 'addSubtask'])->name('subtask.add');
    Route::get('/task/{id}/subtask/{subId}/edit', [TaskController::class, 'editSubtask'])->name('subtask.edit');

    Route::delete('/tasks/{id}/trash', [TaskController::class, 'trash'])->name('task.trash');

    Route::patch('task/{id}/subtask/{subId}/update-status', [TaskController::class, 'updateStatus'])->name('subtask.update-status');
    Route::patch('/tasks/{id}/restore', [TaskController::class, 'restoreTask'])->name('task.restore');
    Route::delete('/tasks/{id}/force-delete', [TaskController::class, 'forceDelete'])->name('task.force-delete');
});


require __DIR__.'/auth.php';
