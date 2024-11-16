@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


<form action="{{ isset($task) ? route('tasks.update', $task->id) : route('tasks.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if(isset($task))
        @method('PUT')
    @endif
    <input type="hidden" name="parent_task_id" value="{{ (int)$parentTaskId }}">
    <div class="mb-3">
        <label for="title" class="form-label">Task Title</label>
        <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $task->title ?? '') }}">
        @error('title')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control" id="description" name="content" rows="3">{{ old('content', $task->content ?? '') }}</textarea>
        @error('content')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="image_path" class="form-label">Task Attachment (Optional)</label>
        <input type="file" class="form-control" id="image_path" name="image_path" accept="image/*">
        @if(isset($task) && $task->image_path)
            <p class="mt-2">Current Attachment: <a href="{{ asset('storage/' . $task->image_path) }}" target="_blank">View</a></p>
        @endif
        @error('image_path')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>

    <button type="submit" class="btn btn-primary">{{ isset($task) ? 'Update Task' : 'Create Task' }}</button>
</form>