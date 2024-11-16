@extends('layouts.app')

@section('title', 'TaskHive') <!-- This should override the default title -->

@section('content')
<header class="container my-4 p-4 border rounded bg-light position-relative">
    <div class="row align-items-center">
        <div class="col-md-12 d-flex justify-content-between align-items-center">
            <div>
                <h2>Task List</h2>
        
                @if(request('search'))
                    <div class="mb-0 d-flex align-items-center">
                        <!-- Search Term Display -->
                        <p class="mb-0">Search: <strong>{{ request('search') }}</strong></p>
                        <!-- Clear Search with X Icon -->
                        <a href="{{ route('tasks.index', array_merge(request()->except('search'))) }}" class="btn btn-danger p-1">
                            x <!-- X Icon -->
                        </a>
                    </div>
                @endif
            </div>
            <a href="{{ route('tasks.create') }}" class="btn btn-success">Add Task</a>
        </div>
    </div>
</header>

<div class="container mt-4">
    <div class="d-flex justify-content-center mb-3">
        <ul class="nav nav-pills rounded-pill shadow-sm">
            <li class="nav-item">
                <a class="nav-link {{ !request('status') ? 'active' : '' }}" href="{{ route('tasks.index', array_merge(request()->except('page'), ['status' => null])) }}">
                    <i class="bi bi-journal-check"></i> Published
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request('status') === 'to-do' ? 'active' : '' }}" href="{{ route('tasks.index', array_merge(request()->except('page'), ['status' => 'to-do'])) }}">
                    <i class="bi bi-clipboard"></i> To Do
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request('status') === 'in-progress' ? 'active' : '' }}" href="{{ route('tasks.index', array_merge(request()->except('page'), ['status' => 'in-progress'])) }}">
                    <i class="bi bi-hourglass-split"></i> In Progress
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request('status') === 'done' ? 'active' : '' }}" href="{{ route('tasks.index', array_merge(request()->except('page'), ['status' => 'done'])) }}">
                    <i class="bi bi-check-circle"></i> Done
                </a>
            </li>
        </ul>
    </div>
</div>
@if ($tasks->isEmpty())
    <div class="alert alert-info text-center">
        <p>No tasks available. Click "Add Task" to create one!</p>
    </div>
@else
    <table class="table table-striped table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th scope="col">Title</th>
                <th scope="col">Status</th>
                <th scope="col">Action</th>
            </tr>
        </thead>
        <tbody class="table-group-divider">
            @foreach ($tasks as $task)
            <tr>
                <td>{{ $task->title }}</td>
                <td>
                    @php
                        $badgeClass = '';
                        $statusText = '';

                        switch ($task->status) {
                            case 'to-do':
                                $badgeClass = 'text-bg-light';
                                $statusText = 'To Do';
                                break;

                            case 'in-progress':
                                $badgeClass = 'text-bg-warning';
                                $statusText = 'In Progress';
                                break;

                            case 'done':
                                $badgeClass = 'text-bg-success';
                                $statusText = 'Done';
                                break;

                            default:
                                $badgeClass = 'text-bg-secondary';
                                $statusText = 'Unknown';
                                break;
                        }
                    @endphp
                    <div>
                        <span class="badge {{ $badgeClass }}">{{ $statusText }}</span>
                        @if ($task->image_path)
                        <div>
                            <i class="fas fa-image" title="With Attachment"></i>
                        </div>
                        @endif
                    </div>

                    @if ($task->subtasks()->where('is_draft', 0)->count())
                        <div class="progress my-2">
                            <div class="progress-bar" style="width: {{ ($task->subtasks()->where('is_draft',0)->where('status', 'done')->count()/ $task->subtasks()->where('is_draft',0)->count()) * 100 }}%" role="progressbar" aria-valuenow="{{ ($task->subtasks()->where('is_draft',0)->where('status', 'done')->count()/ $task->subtasks()->where('is_draft',0)->count()) * 100 }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-code-branch"></i> <!-- Branch icon -->
                            <span class="ms-1">{{ $task->subtasks()->where('is_draft', 0)->count() }}</span>
                        </div>
                        
                    @else
                        <div class="progress my-2">
                            <div class="progress-bar" style="width: {{ $task->status == 'done' ? '100' : '0'}}%" role="progressbar" aria-valuenow="{{ $task->status == 'done' ? '100' : '0'}}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    @endif
                </td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="{{ route('tasks.show', $task)}}" class="btn btn-primary btn-sm">Manage Task</a>
                            <form action="{{ route('tasks.delete', $task)}}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </div>
                    </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="d-flex justify-content-center">
        <ul class="pagination pagination-sm">
            {{-- Previous Button --}}
            <li class="page-item {{ $tasks->onFirstPage() ? 'disabled' : '' }}">
                <a class="page-link" href="{{ $tasks->previousPageUrl() }}" tabindex="-1">Previous</a>
            </li>

            {{-- Page Numbers --}}
            @for ($i = 1; $i <= $tasks->lastPage(); $i++)
                <li class="page-item {{ $tasks->currentPage() == $i ? 'active' : '' }}">
                    <a class="page-link" href="{{ $tasks->url($i) }}">{{ $i }}</a>
                </li>
            @endfor

            {{-- Next Button --}}
            <li class="page-item {{ $tasks->hasMorePages() ? '' : 'disabled' }}">
                <a class="page-link" href="{{ $tasks->nextPageUrl() }}">Next</a>
            </li>
        </ul>
    </div>
@endif
@endsection