@extends('layouts.task-app')


@section('header')
<div class="container my-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h1 class="card-title h4 font-weight-bold">{{ $task->title }}</h1>
            <p class="card-text">{{ $task->content }}</p>

            @if ($task->image_path)
                <div class="text-center my-3">
                    <img src="{{ asset('storage/' . $task->image_path) }}" 
                         alt="Attachment" 
                         class="img-fluid rounded" 
                         style="max-width: 400px; max-height: 300px; width: 100%; height: auto;">
                </div>
            @endif
        </div>
    </div>
</div>


@endsection

@section('content')
    <div>
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link {{ !request()->has('status') || request('status') == '' ? 'active' : '' }}" href="{{ route('task.manage', ['id' => $task->id]) }}">Published</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request('status') == 'to-do' ? 'active' : '' }}" href="{{ route('task.manage', array_merge(request()->only('order_by', 'page','search', 'per_page'), ['status' => 'to-do', 'id' => $task->id])) }}">To Do</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request('status') == 'in-progress' ? 'active' : '' }}" href="{{ route('task.manage', array_merge(request()->only('order_by', 'page','search', 'per_page'), ['status' => 'in-progress', 'id' => $task->id])) }}">In Progress</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request('status') == 'done' ? 'active' : '' }}" href="{{ route('task.manage', array_merge(request()->only('order_by', 'page','search', 'per_page'), ['status' => 'done', 'id' => $task->id])) }}">Done</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request('status') == 'draft' ? 'active' : '' }}" href="{{ route('task.manage', array_merge(request()->only('order_by', 'page','search', 'per_page'), ['status' => 'draft', 'id' => $task->id])) }}">Drafts</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request('status') == 'trash' ? 'active' : '' }}" href="{{ route('task.manage', array_merge(request()->only('order_by', 'page','search', 'per_page'), ['status' => 'trash', 'id' => $task->id])) }}">Trash</a>
            </li>
        </ul>
    </div>
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <a href="{{ route('subtask.add', ['id' => $task->id]) }}" class="btn btn-dark my-3">Add Task</a>
    @if($subtasks->isEmpty())
        <div class="alert alert-info">
            <strong>No tasks found!</strong> You have no tasks to display. Please add some tasks.
        </div>
    @else
    <div class="d-flex justify-content-between mb-3">
        <div>
            <a href="{{ route('task.manage', array_merge(request()->only('status', 'page','search', 'per_page'), ['order_by' => 'title','id' => $task->id])) }}" class="btn btn-link">Sort by Title (A-Z)</a>
            <a href="{{ route('task.manage', array_merge(request()->only('status', 'page','search', 'per_page'), ['order_by' => 'created_at','id' => $task->id])) }}" class="btn btn-link">Sort by Date Created</a>
        </div>
    </div>
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
                    @if ($task->image_path)
                        <div class="text-center my-3">
                            <img src="{{ asset('storage/' . $task->image_path) }}" 
                                alt="Attachment" 
                                class="img-fluid rounded" 
                                style="max-width: 150px; max-height: 150px; width: 100%; height: auto;">
                        </div>
                    @else
                        <span class="text-muted">No Image</span>
                    @endif
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
                @if (request('status') == 'trash')
                    <td>
                        <div class="d-flex gap-2">
                            <form action="{{ route('task.restore', ['id' =>$task->id, 'subtask' => true]) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-success btn-sm">Restore</button>
                            </form>
                            <form action="{{ route('task.force-delete', ['id' =>$task->id, 'subtask' => true]) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Delete Permanently</button>
                            </form>

                        </div>
                    </td>
                @else
                    <td>
                        <div class="d-flex justify-content-start align-items-center gap-2">
                            <a href="{{ route('subtask.edit', ['subId' => $task->id, 'id' => $task->parent_task_id]) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('subtask.update-status', ['subId' => $task->id, 'id' => $task->parent_task_id]) }}" method="POST" class="d-flex">
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
                            <form action="{{ route('task.trash', ['id' => $task->id, 'subtask' => true]) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </div>
                    </td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <!-- Tasks Per Page Dropdown -->
            <form action="{{ route('tasks') }}" method="GET" class="d-flex align-items-center">
                <label for="perPage" class="me-2 mb-0">Tasks per page:</label>
                <select name="per_page" id="perPage" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20</option>
                    <option value="30" {{ request('per_page') == 30 ? 'selected' : '' }}>30</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                </select>
            </form>
    
            <!-- Pagination -->
            <div class="d-flex justify-content-center">
                <ul class="pagination pagination-sm">
                    {{-- Previous Button --}}
                    <li class="page-item {{ $subtasks->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $subtasks->previousPageUrl() . '&' . http_build_query(array_merge(request()->except(['page', 'search']), ['status' => request('status'),'created_at' => request('created_at'),'order_by' => request('order_by'),'per_page' => request('per_page') ])) }}" tabindex="-1">Previous</a>
                    </li>
        
                    {{-- Page Numbers --}}
                    @for ($i = 1; $i <= $subtasks->lastPage(); $i++)
                        <li class="page-item {{ $subtasks->currentPage() == $i ? 'active' : '' }}">
                            <a class="page-link" href="{{ $subtasks->url($i) . '&' . http_build_query(array_merge(request()->except(['page', 'search']), ['status' => request('status'),'created_at' => request('created_at'),'order_by' => request('order_by') ,'per_page' => request('per_page') ])) }}">{{ $i }}</a>
                        </li>
                    @endfor
        
                    {{-- Next Button --}}
                    <li class="page-item {{ $subtasks->hasMorePages() ? '' : 'disabled' }}">
                        <a class="page-link" href="{{ $subtasks->nextPageUrl() . '&' . http_build_query(array_merge(request()->except(['page', 'search']), ['status' => request('status'),'created_at' => request('created_at'),'order_by' => request('order_by'),'per_page' => request('per_page') ])) }}">Next</a>
                    </li>
                </ul>
            </div>
        </div>
    </div> 
@endsection