@extends('layouts.task-app')

@section('header')
<h1>Add SubTask {{$parentTaskId}}</h1>
@endsection

@section('content')
@include('tasks._task-form', [
    'action' => route('task.store'),
    'method' => 'POST',
    'buttonText' => 'Add Task',
    'parentTaskId' => $parentTaskId
])

@endsection