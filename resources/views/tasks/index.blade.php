@extends('layouts.task-app')


@section('header')
<header class="bg-primary text-white py-3 shadow-sm">
    <div class="container text-center">
        <h1 class="display-5 fw-bold">Task Manager Application</h1>
        <p class="lead">Stay organized and manage your tasks effectively</p>
    </div>
</header>
@endsection

@section('content')
    <div>
        <ul class="nav nav-tabs">
            <li class="nav-item">
              <a class="nav-link {{ !request()->has('status') || request('status') == '' ? 'active' : '' }}" href="{{ route('tasks', array_merge(request()->only('order_by', 'page'))) }}">Published</a>
            </li>
            <li class="nav-item">
              <a class="nav-link {{ request('status') == 'to-do' ? 'active' : '' }}" href="{{ route('tasks', array_merge(request()->only('order_by', 'page','search', 'per_page'), ['status' => 'to-do'])) }}">To Do</a>
            </li>
            <li class="nav-item">
              <a class="nav-link {{ request('status') == 'in-progress' ? 'active' : '' }}" href="{{ route('tasks', array_merge(request()->only('order_by', 'page','search', 'per_page'), ['status' => 'in-progress'])) }}">In Progress</a>
            </li>
            <li class="nav-item">
              <a class="nav-link {{ request('status') == 'done' ? 'active' : '' }}" href="{{ route('tasks', array_merge(request()->only('order_by', 'page','search', 'per_page'), ['status' => 'done'])) }}">Done</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request('status') == 'draft' ? 'active' : '' }}" href="{{ route('tasks', array_merge(request()->only('order_by', 'page','search', 'per_page'), ['status' => 'draft'])) }}">Drafts</a>
              </li>
              <li class="nav-item">
                <a class="nav-link {{ request('status') == 'trash' ? 'active' : '' }}" href="{{ route('tasks', array_merge(request()->only('order_by', 'page','search', 'per_page'), ['status' => 'trash'])) }}">Trash</a>
              </li>
          </ul>
    </div>
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <a href="{{ route('task.add') }}" class="btn btn-dark my-3">Add Task</a>
    @if($tasks->isEmpty())
        <div class="alert alert-info">
            <strong>No tasks found!</strong> You have no tasks to display. Please add some tasks.
        </div>
    @else
        <div class="d-flex justify-content-between mb-3">
            <div>
                <a href="{{ route('tasks', array_merge(request()->only('status', 'page', 'search', 'per_page'), ['order_by' => 'title'])) }}" class="btn btn-link">Sort by Title (A-Z)</a>
                <a href="{{ route('tasks', array_merge(request()->only('status', 'page','search', 'per_page'), ['order_by' => 'created_at'])) }}" class="btn btn-link">Sort by Date Created</a>
            </div>
        </div>
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
                                <span class="badge text-bg-info">With Attachment</span>
                            @endif
                        </div>
        
                        @if ($task->subtasks()->count())
                            <div class="progress my-2">
                                <div class="progress-bar" style="width: {{ ($task->subtasks()->where('status', 'done')->count()/ $task->subtasks()->count()) * 100 }}%" role="progressbar" aria-valuenow="{{ ($task->subtasks()->where('status', 'done')->count()/ $task->subtasks()->count()) * 100 }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        @else
                            <div class="progress my-2">
                                <div class="progress-bar" style="width: {{ $task->status == 'done' ? '100' : '0'}}%" role="progressbar" aria-valuenow="{{ $task->status == 'done' ? '100' : '0'}}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        @endif
                    </td>
        
                    @if (request('status') == 'trash')
                        <td>
                            <div class="d-flex gap-2">
                                <form action="{{ route('task.restore', $task->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success btn-sm">Restore</button>
                                </form>
                                <form action="{{ route('task.force-delete', $task->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete Permanently</button>
                                </form>
                            </div>
                        </td>
                    @else
                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{ route('task.edit', ['id' => $task->id ]) }}" class="btn btn-warning btn-sm">Edit Task</a>
                                <a href="{{ route('task.manage', ['id' => $task->id ]) }}" class="btn btn-primary btn-sm">Manage Task</a>
                                <form action="{{ route('task.trash', ['id' => $task->id]) }}" method="POST" class="d-inline">
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
                    <li class="page-item {{ $tasks->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $tasks->previousPageUrl() . '&' . http_build_query(array_merge(request()->except(['page', 'search']), ['status' => request('status'), 'created_at' => request('created_at'), 'order_by' => request('order_by'), 'search' => request('search'),'per_page' => request('per_page')])) }}" tabindex="-1">Previous</a>
                    </li>
    
                    {{-- Page Numbers --}}
                    @for ($i = 1; $i <= $tasks->lastPage(); $i++)
                        <li class="page-item {{ $tasks->currentPage() == $i ? 'active' : '' }}">
                            <a class="page-link" href="{{ $tasks->url($i) . '&' . http_build_query(array_merge(request()->except(['page', 'search']), ['status' => request('status'), 'created_at' => request('created_at'), 'order_by' => request('order_by'), 'search' => request('search'),'per_page' => request('per_page')])) }}">{{ $i }}</a>
                        </li>
                    @endfor
    
                    {{-- Next Button --}}
                    <li class="page-item {{ $tasks->hasMorePages() ? '' : 'disabled' }}">
                        <a class="page-link" href="{{ $tasks->nextPageUrl() . '&' . http_build_query(array_merge(request()->except(['page', 'search']), ['status' => request('status'), 'created_at' => request('created_at'), 'order_by' => request('order_by'), 'search' => request('search'),'per_page' => request('per_page')])) }}">Next</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
      
@endsection