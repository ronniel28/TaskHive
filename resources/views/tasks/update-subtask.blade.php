@extends('layouts.task-app')

@section('header')
    <div class="container mt-4">
        <h1 class="text-center">Update SubTask</h1>
        <p class="text-center">Edit the details of your subtask for Task ID: {{ $parentTaskId }}</p>

        @if ($task->image_path)
        <div class="text-center my-3">
            <img src="{{ asset('storage/' . $task->image_path) }}" 
                 alt="Attachment" 
                 class="img-fluid rounded" 
                 style="max-width: 400px; max-height: 300px; width: 100%; height: auto;">
        </div>
    @endif
    </div>
@endsection


@section('content')
@include('tasks._task-form', [
    'action' => route('task.update', $task->id),
    'method' => 'PUT',
    'task' => $task,
    'buttonText' => 'Update Task',
    'parentTaskId' => $parentTaskId
])

@endsection