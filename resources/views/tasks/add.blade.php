@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Add Task</h1>
        @include('partials._task-form')  {{-- Include the task form partial --}}
    </div>
@endsection