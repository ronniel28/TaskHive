@extends('layouts.task-app')

@section('header')
    <h1>Update Task</h1>
@endsection

@section('content')
@include('tasks._task-form', [
    'action' => route('task.update', $task->id),
    'method' => 'PUT',
    'task' => $task,
    'buttonText' => 'Update Task'
])

@endsection