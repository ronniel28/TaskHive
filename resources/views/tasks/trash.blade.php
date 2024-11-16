@extends('layouts.app')

@section('title', 'Trash') <!-- This should override the default title -->

@section('content')
<header class="container my-4 p-4 border rounded bg-light position-relative">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h1 class="display-4 mb-3">Trash</h1>
        </div>
    </div>
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
</div>



<table class="table table-striped table-bordered table-hover">
    <thead class="table-dark">
        <tr>
            <th scope="col">Title</th>
            <th scope="col">Parent Task</th>
            <th scope="col">Image</th>
            <th scope="col">Content</th>
            <th scope="col">Status</th>
            <th scope="col">Action</th>
        </tr>
    </thead>
    <tbody class="table-group-divider">
        @foreach ($tasks as $task)
        <tr>
            <td>{{ $task->title }}</td>
            <td>{{ $task->parentTask ? $task->parentTask->title : 'No Parent Task' }}</td>
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
                    <div class="d-flex gap-2">
                        <form action="{{ route('tasks.restore', $task->id) }}" method="POST" class="d-inline" >
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success btn-sm">Restore</button>
                        </form>
                        <form action="{{ route('tasks.forceDelete', $task->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to permanently delete this task?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Delete Permanently</button>
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
@endsection