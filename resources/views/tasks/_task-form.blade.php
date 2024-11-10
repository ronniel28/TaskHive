<form action="{{ $action }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif

    @if (@$parentTaskId)
        <input type="hidden" name="parent_task_id" value="{{ $parentTaskId ? $parentTaskId : '' }}"> 
    @endif

    <!-- Title Field -->
    <div class="mb-3">
        <label for="title" class="form-label">Task Title</label>
        <input type="text" name="title" id="title" class="form-control" maxlength="100"
               value="{{ old('title', $task->title ?? '') }}" required>
        @error('title')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>

    <!-- Content Field -->
    <div class="mb-3">
        <label for="content" class="form-label">Task Content</label>
        <textarea name="content" id="content" class="form-control" rows="4" required>{{ old('content', $task->content ?? '') }}</textarea>
        @error('content')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>

    <!-- Status Field -->
    <div class="mb-3">
        <label for="status" class="form-label">Status</label>
        <select name="status" id="status" class="form-select" required>
            <option value="to-do" {{ old('status', $task->status ?? '') == 'to-do' ? 'selected' : '' }}>To Do</option>
            <option value="in-progress" {{ old('status', $task->status ?? '') == 'in-progress' ? 'selected' : '' }}>In Progress</option>
            <option value="done" {{ old('status', $task->status ?? '') == 'done' ? 'selected' : '' }}>Done</option>
        </select>
        @error('status')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>

    <!-- Attachment Field -->
    <div class="mb-3">
        <label for="image_path" class="form-label">Attachment (Optional, max 4MB)</label>
        <input type="file" name="image_path" id="image_path" class="form-control" accept="image/*">
        @error('image_path')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>

    <!-- Publish Checkbox -->
    <div class="mb-3 form-check">
        <input type="checkbox" name="is_draft" id="is_draft" class="form-check-input" value="1"
               {{ old('is_draft', $task->is_draft ?? 0) ? 'checked' : '' }}>
        <label class="form-check-label" for="is_draft">Save as Draft</label>
    </div>

    <!-- Submit Button -->
    <button type="submit" class="btn btn-primary">{{ $buttonText }}</button>
</form>
