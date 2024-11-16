<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Jobs\DeleteExpiredTaskJob;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class TaskController extends Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;
    public function index(Request $request)
    {
        // dd($request->input('search'));
        $perPage = $request->get('per_page', 10);

        $page = $request->get('page');

        $status = $request->get('status');

        $search = $request->input('search');

        $query = auth()->user()->tasks()->where('is_draft', 0)->whereNull('parent_task_id');



        if($status){
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                ->orWhere('content', 'like', '%' . $search . '%')
                ->orWhereHas('subtasks', function ($subQuery) use ($search) {
                    $subQuery->where('title', 'like', '%' . $search . '%');
                });
            });
        }

        $tasks = $query->paginate($perPage);

        $tasks->appends([
            'status' => $request->get('status'),
            'page' => $page,
            'per_page' => $perPage,
            'search' => $search
        ]);

        return view('tasks.index', compact('tasks'));
    }

    public function show(Request $request, Task $task) 
    {
        // dd($task);
        $perPage = $request->get('per_page', 10);

        $page = $request->get('page');

        $status = $request->get('status');

        $search = $request->input('search');

        $this->authorize('view', $task);

        $query = $task->subtasks()->where('is_draft', 0);

        if($status){
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                ->orWhere('content', 'like', '%' . $search . '%');
            });
        }

        $subtasks = $query->paginate($perPage);

        $subtasks->appends([
            'status' => $status,
            'page' => $page,
            'per_page' => $perPage,
            'search' => $search
        ]);

        return view('tasks.show', compact('task', 'subtasks'));
    }

    public function edit(Task $task)
    {
        $parentTaskId = $task->parent_task_id;
        $this->authorize('edit', $task);
        return view('tasks.edit', compact('task', 'parentTaskId'));
    }

    public function create(Request $request, Task $task)
    {
        // dd($task->title);
        $parentTaskId = $request->query('task');
   
        // Find the task
        $task = Task::find($parentTaskId);
        
            // Use the policy to check if the authenticated user can create a task based on the given task
        if (!$task || !Gate::allows('create', $task)) {
            return redirect()->route('tasks.index')->with('error', 'You cannot create a task with this task.');
        }


        return view('tasks.add', compact('parentTaskId'));
    }

    public function store(TaskRequest $request)
    {

        $parentTaskId = $request->input('parent_task_id');

        $validatedData = $request->validated();

        if ($request->hasFile('image_path')) {
            $path = $request->file('image_path')->store('attachments', 'public');
            $validatedData['image_path'] = $path;
        }

        $task = Task::create($validatedData);

        $task->parent_task_id = $request->input('parent_task_id');


        $task->save();


        if($task->parent_task_id) {
            $task->parentTask->updateProgress();
        }

        return redirect()->route($parentTaskId ? 'tasks.show' : 'tasks.index', $parentTaskId ?? [])->with('success', 'Task created successfully.');
    }

    public function update(TaskRequest $request, Task $task)
    {
        $this->authorize('update', $task);
        // $this->authorize('update', $task);
        $validatedData = $request->validated();
     
        // Check if a new image was uploaded
        if ($request->hasFile('image_path')) {
            // Delete the old image if it exists
            if ($task->image_path && file_exists(storage_path('app/public/' . $task->image_path))) {
                unlink(storage_path('app/public/' . $task->image_path)); // Delete the old image
            }

            // Store the new image and update the image path in the validated data
            $validatedData['image_path'] = $request->file('image_path')->store('attachments', 'public');
        }

        if($task->parent_task_id) {
            $task->parentTask->updateProgress();
        }
        
        $task->update($validatedData);

        return redirect()->route('tasks.show', $task->parent_task_id ? $task->parent_task_id : $task)->with('success', 'Task updated successfully.');
    }

    public function toggleDraft(Task $task)
    {
        $url = parse_url(URL::previous(), PHP_URL_PATH);
        $baseUrl = url($url);
        $task->is_draft = !$task->is_draft;
        $task->save();

        if ($baseUrl === route('tasks.drafts')) {
            return redirect()->route('tasks.drafts')->with('success', 'Draft updated successfully.');
        } else {
            return redirect()->route('tasks.show', $task)->with('success', 'Task updated successfully.');
        }
        
    }

    public function updateTaskStatus(TaskRequest $request, Task $task)
    {
        // Update only the status field
        $task->update($request->validated());

        if($task->parent_task_id) {
            $task->parentTask->updateProgress();
        }

        // Redirect back with a success message
        return redirect()->back()->with('success', 'Task status updated successfully.');
    }

    public function drafts()
    {
        $tasks = auth()->user()->tasks()->where('is_draft', 0)->paginate(10);
        return view('tasks.drafts', compact('tasks'));
    }

    public function trash()
    {
        $tasks = auth()->user()->tasks()->onlyTrashed()->paginate(10);
        return view('tasks.trash', compact('tasks'));
    }

    public function moveToTrash(Task $task)
    {
        $task->delete();
        return redirect()->back()->with('success', 'Task soft deleted successfully.'); 
    }
    
    public function restore($taskId)
    {
   
      // Use withTrashed() to include soft-deleted tasks
      $task = Task::withTrashed()->find($taskId);

      if (!$task) {
          return redirect()->route('tasks.drafts')->with('error', 'Task not found.');
      }
  
      if ($task->trashed()) {
          $this->authorize('restore', $task);
  
          $task->restore();
          return redirect()->route('tasks.trash')->with('success', 'Task restored successfully.');
      }
  
      return redirect()->route('tasks.trash')->with('error', 'Task is not soft-deleted.');
  
    }

    public function forceDelete($taskId)
    {
         // Find the task, including soft-deleted tasks
        $task = Task::withTrashed()->find($taskId);

        // If task is not found, redirect with an error message
        if (!$task) {
            return redirect()->route('tasks.drafts')->with('error', 'Task not found.');
        }

        // Force delete the task (permanently delete it)
        $task->forceDelete();

        // Redirect back with a success message
        return redirect()->route('tasks.drafts')->with('success', 'Task permanently deleted.');
       
    }

    public function updateStatus(Request $request, $id, $subId)
    {
        $task = Task::where('id', $subId)->where('user_id', auth()->id())->firstOrFail();
        $task->update(['status' => $request->input('status')]);
        return redirect()->back()->with('success', 'Subtask status updated successfully.');
    }
}
