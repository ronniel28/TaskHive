@extends('layouts.app')

@section('title', $task->title) <!-- This should override the default title -->

@section('content')
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<header class="container my-4 p-4 border rounded bg-light position-relative">
    <div class="mt-4">
        <a href="{{ route('tasks.index') }}" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Back to Tasks
        </a>
    </div>
    <div class="row align-items-center">
        <div class="col-md-8">
            <h1 class="display-4 mb-3">{{ $task->title }}</h1>
            <p class="lead">{{ $task->content }}</p>

            <!-- Toggle draft status -->
            <form action="{{ route('tasks.toggleDraft', $task) }}" method="POST"> 
                @csrf
                @method('PATCH')
                <button type="submit" class="btn {{ $task->is_draft ? 'btn-warning' : 'btn-success' }} btn-sm">
                    {{ $task->is_draft ? 'Publish' : 'Mark as Draft' }}
                </button>
            </form>

            @if ($task->is_draft)
                <span class="badge bg-secondary ms-2">Draft</span>
            @endif
        </div>
        <div class="col-md-4 text-center">
            <img src="{{ $task->image_path ? asset('storage/' . $task->image_path) : 'https://via.placeholder.com/150' }}" 
                 alt="{{ $task->title }}" 
                 class="img-fluid rounded shadow-sm" />
        </div>
    </div>
    <!-- Pencil icon in the upper right corner of the header -->
    <a href="{{ route('tasks.edit', $task) }}" class="edit-icon position-absolute top-0 end-0 me-3 mt-3 text-primary">
        <i class="fas fa-pencil-alt"></i>
    </a>
</header>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2>Subtasks List</h2>

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

    <div class="d-flex gap-2">
        <!-- Dropdown to Update Task Status -->
        @if ($subtasks->isEmpty())
            <form action="{{ route('tasks.updateTaskStatus', $task) }}" method="POST" class="d-flex align-items-center">
                @csrf
                @method('PATCH')
                <div class="form-group me-2">
                    <label for="statusDropdown" class="visually-hidden">Update Parent Task Status</label>
                    <select id="statusDropdown" class="form-select form-select-sm" name="status" onchange="this.form.submit()">
                        <option value="to-do" {{ $task->status == 'to-do' ? 'selected' : '' }}>To Do</option>
                        <option value="in-progress" {{ $task->status == 'in-progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="done" {{ $task->status == 'done' ? 'selected' : '' }}>Done</option>
                    </select>
                </div>
            </form>
        @endif
        <!-- Add Subtask Button -->
        <a href="{{ route('tasks.create', ['task' => $task]) }}" class="btn btn-success">Add Subtask</a>
    </div>
</div>

<div class="container mt-4">
    <div class="d-flex justify-content-center mb-3">
        <ul class="nav nav-pills rounded-pill shadow-sm">
            <li class="nav-item">
                <a class="nav-link {{ !request('status') ? 'active' : '' }}" href="{{ route('tasks.show', array_merge(request()->except('page'), ['status' => null, $task->id])) }}">
                    <i class="bi bi-journal-check"></i> Published
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request('status') === 'to-do' ? 'active' : '' }}" href="{{ route('tasks.show', array_merge(request()->except('page'), ['status' => 'to-do', $task->id])) }}">
                    <i class="bi bi-clipboard"></i> To Do
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request('status') === 'in-progress' ? 'active' : '' }}" href="{{ route('tasks.show', array_merge(request()->except('page'), ['status' => 'in-progress', $task->id])) }}">
                    <i class="bi bi-hourglass-split"></i> In Progress
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request('status') === 'done' ? 'active' : '' }}" href="{{ route('tasks.show', array_merge(request()->except('page'), ['status' => 'done', $task->id])) }}">
                    <i class="bi bi-check-circle"></i> Done
                </a>
            </li>
        </ul>
    </div>
</div>

@if ($subtasks->isEmpty())
    <div class="alert alert-info text-center">
        <p>No subtasks available for this task. Click "Add Subtask" to create one!</p>
    </div>
@else
    <table class="table table-striped table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th scope="col">Title</th>
                <th scope="col">Image</th>
                <th scope="col">Content</th>
                <th scope="col">Status</th>
                <th scope="col">Action</th>
            </tr>
        </thead>
        <tbody class="table-group-divider">
            @foreach ($subtasks as $task)
            <tr>
                <td>{{ $task->title }}</td>
                <td>
                    <div class="text-center">
                        <img src="{{ $task->image_path ? asset('storage/' . $task->image_path) : 'https://via.placeholder.com/150' }}" 
                             alt="Attachment" 
                             class="img-fluid rounded" 
                             style="max-width: 150px; max-height: 150px; width: 100%; height: auto;">
                    </div>
                </td>
                <td>
                    <div class="text-truncate" style="max-width: 250px;">
                        {{ $task->content }}
                    </div>
                </td>
                <td>
                    <span class="badge 
                        @if($task->status == 'done') bg-success 
                        @elseif($task->status == 'in-progress') bg-warning 
                        @else bg-secondary @endif">
                        {{ ucfirst($task->status) }}
                    </span>
                </td>
                <td>
                    <div class="d-flex justify-content-start align-items-center gap-2">
                        <a href="{{ route('tasks.edit', $task->id) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('tasks.updateTaskStatus', $task) }}" method="POST" class="d-flex">
                            @csrf
                            @method('PATCH')
                            <div class="form-group me-2">
                                <select class="form-select form-select-sm" aria-label="Select Task Status" name="status" onchange="this.form.submit()">
                                    <option value="to-do" {{ $task->status == 'to-do' ? 'selected' : '' }}>To Do</option>
                                    <option value="in-progress" {{ $task->status == 'in-progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="done" {{ $task->status == 'done' ? 'selected' : '' }}>Done</option>
                                </select>
                            </div>
                        </form>
                        <form action="{{ route('tasks.delete', $task) }}" method="POST">
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
            <li class="page-item {{ $subtasks->onFirstPage() ? 'disabled' : '' }}">
                <a class="page-link" href="{{ $subtasks->previousPageUrl() }}" tabindex="-1">Previous</a>
            </li>
    
            {{-- Page Numbers --}}
            @for ($i = 1; $i <= $subtasks->lastPage(); $i++)
                <li class="page-item {{ $subtasks->currentPage() == $i ? 'active' : '' }}">
                    <a class="page-link" href="{{ $subtasks->url($i) }}">{{ $i }}</a>
                </li>
            @endfor
    
            {{-- Next Button --}}
            <li class="page-item {{ $subtasks->hasMorePages() ? '' : 'disabled' }}">
                <a class="page-link" href="{{ $subtasks->nextPageUrl() }}">Next</a>
            </li>
        </ul>
    </div>
@endif

@endsection