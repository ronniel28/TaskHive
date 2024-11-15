<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Jobs\DeleteExpiredTaskJob;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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

        $query = auth()->user()->tasks()->whereNull('parent_task_id');



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

        $query = $task->subtasks();

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

    public function create(Request $request)
    {
        // dd($request->all());
        $parentTaskId = $request->input('task');
        // dd($parentTaskId);
        return view('tasks.add', compact('parentTaskId'));
    }

    public function store(TaskRequest $request)
    {
        // dd($request->all());
        $parentTaskId = $request->input('parent_task_id');

        $validatedData = $request->validated();

        if ($request->hasFile('image_path')) {
            $path = $request->file('image_path')->store('attachments', 'public');
            $validatedData['image_path'] = $path;
        }

        $task = Task::create($validatedData);

        $task->parent_task_id = $request->input('parent_task_id');

        $task->save();

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
        
        $task->update($validatedData);

        return redirect()->route('tasks.show', $task->parent_task_id ? $task->parent_task_id : $task)->with('success', 'Task updated successfully.');
    }

    public function toggleDraft(Task $task)
    {
        
        $task->is_draft = !$task->is_draft;
        $task->save();

        return redirect()->route('tasks.show', $task)->with('success', 'Task updated successfully.');
    }


    public function manageTask(Request $request, $id)
    {
        $task = Task::where('user_id', auth()->user()->id)->findOrFail($id);
        $task->updateProgress();

        if (!$task) {
            abort(404, 'Task not found or you do not have permission to view it.');
        }    
       
        $subtasksQuery = $task->subtasks();

        if ($request->has('status') && $request->input('status')) {
            $status = $request->input('status');
            
            if ($status === 'draft') {
                $subtasksQuery->where('is_draft', true); // Filter by draft status
            } else if($status === 'trash') {
                $subtasksQuery->onlyTrashed();
            } else {
                $subtasksQuery->where('status', $status)  // Filter by specific status
                              ->where('is_draft', false); // Ensure not a draft
            }
        } else {
            $subtasksQuery->where('is_draft', false); // Default: only non-draft subtasks
        }

        if ($request->has('order_by')) {
            if ($request->input('order_by') == 'title') {
                $subtasksQuery->orderBy('title', 'asc');  // Ascending order for title (alphabetically)
            } elseif ($request->input('order_by') == 'created_at') {
                $subtasksQuery->orderBy('created_at', 'desc'); // Descending order for created_at (latest first)
            }
        } else {
            // Default order if no sorting is applied
            $subtasksQuery->orderBy('created_at', 'desc'); // Sort by date created (newest first)
        }

        $subtasks = $subtasksQuery->paginate(10);
    
        //dd($task->subtasks->onFirstPage());
        return view('tasks.task', compact('task', 'subtasks'));
        
    }

    public function addTask()
    {
        return view('tasks.add');
    }

    public function storeTask(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'title' => 'required|max:100',
            'content' => 'required',
            'status' => 'required|in:to-do,in-progress,done',
            'image_path' => 'nullable|image|max:4096',
            'parent_task_id' => 'nullable|exists:tasks,id'
        ]);

        $task = new Task();
        $task->user_id = Auth::id();
        $task->title = $request->title;
        $task->content = $request->content;
        $task->status = $request->status;
        $task->is_draft = $request->has('is_draft') ? 1 : 0;

        if ($request->hasFile('image_path')) {
            $path = $request->file('image_path')->store('attachments', 'public');
            $task->image_path = $path;
        }

        $task->parent_task_id = $request->input('parent_task_id');

        $task->save();

        if($request->input('parent_task_id')){
            return redirect()->route('task.manage', ['id' => $request->input('parent_task_id')])->with('success', 'Task added successfully.');
        }else{
            return redirect()->route('tasks')->with('success', 'Task added successfully.');
        }


    }

    public function editTask(Request $request, $id)
    {
        $task = Task::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        // dd($task);
        
        return view('tasks.update', compact('task'));
    }

    public function updateTask(Request $request, $id)
    {
        // dd($request->all());
        $task = Task::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $validated = $request->validate([
            'title' => 'required|unique:tasks,title,'.$task->id.'|max:100',
            'content' => 'required',
            'status' => 'required|in:to-do,in-progress,done',
            'image_path' => 'nullable|image|max:4096', // Validate the attachment as an image, max 4MB
            'parent_task_id' => 'nullable|exists:tasks,id',
        ]);
    
        $task->fill($validated);
    
        // Handle the file upload
        if ($request->hasFile('image_path')) {
            // Delete old attachment if it exists
            if ($task->image_path) {
                Storage::disk('public')->delete($task->image_path);
            }
            
            $path = $request->file('image_path')->store('attachments', 'public');
            $task->image_path = $path;
        }

        $task->parent_task_id = $request->input('parent_task_id');
        $task->is_draft = $request->input('is_draft') ? (int)$request->input('is_draft') : 0;
        $task->save();
    
        if($request->input('parent_task_id')){
            return redirect()->route('task.manage', ['id' => $request->input('parent_task_id')])->with('success', 'Task updated successfully.');
        }else{
            return redirect()->route('tasks')->with('success', 'Task updated successfully.');
        }
    
    }

    public function addSubtask(Request $request, $id)
    {
        // Find the parent task and ensure it belongs to the authenticated user
        $parentTask = Task::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();
        
        if (!$parentTask) {
            abort(404, 'Task not found or you do not have permission to edit this task.');
        }

        $parentTaskId = (int)$id;
        return view('tasks.add-subtask', compact('parentTaskId'));
    }

    public function editSubtask(Request $request, $id, $subId)
    {

        $task = Task::where('id', (int)$subId)
                    ->where('user_id', auth()->id())
                    ->first();

        if (!$task) {
            abort(404, 'Task not found or you do not have permission to edit this task.');
        }

        $parentTask = Task::where('id', $id)
                        ->where('user_id', auth()->id())
                        ->first();

        if (!$parentTask) {
            abort(404, 'Parent task not found or does not belong to you.');
        }

        // Ensure the task is a subtask of the given parent_task_id if provided
        if ((int)$task->parent_task_id !== (int)$id) {
            abort(404, 'This task is not a subtask of the specified parent task.');
        }

        // Ensure the parent task is not the same as the task itself
        if ((int)$task->id === (int)$id) {
            abort(404, 'A task cannot be its own parent.');
        }
                    
                
        $parentTaskId = (int)$id;

        return view('tasks.update-subtask', compact('task', 'parentTaskId'));
    }

    public function trash(Request $request,$id)
    {
        // dd($request->all());
        $task = Task::where('id',(int)$id)->where('user_id', auth()->id())->firstOrFail();
        $task->delete();

        if($request->input('subtask')){
            return redirect()->back()->with('success', 'Task soft deleted successfully.'); 
        }else{
            return redirect()->route('tasks')->with('success', 'Task soft deleted successfully.');
        }
    }

    public function restoreTask(Request $request,$id)
    {
        $task = Task::onlyTrashed()->where('id',(int)$id)->where('user_id', auth()->id())->firstOrFail();
        $task->restore(); // Restore the soft-deleted task
        if($request->input('subtask')){
            return redirect()->back()->with('success', 'Task soft deleted successfully.'); 
        }else{
            return redirect()->route('tasks')->with('success', 'Task soft deleted successfully.');
        }
    }

    public function forceDelete(Request $request,$id)
    {
        $task = Task::onlyTrashed()->where('id',(int)$id)->where('user_id', auth()->id())->firstOrFail();
        $task->forceDelete(); // Permanently delete the task
        if($request->input('subtask')){
            return redirect()->back()->with('success', 'Task soft deleted successfully.'); 
        }else{
            return redirect()->route('tasks')->with('success', 'Task soft deleted successfully.');
        }
    }

    public function updateStatus(Request $request, $id, $subId)
    {
        $task = Task::where('id', $subId)->where('user_id', auth()->id())->firstOrFail();
        $task->update(['status' => $request->input('status')]);
        return redirect()->back()->with('success', 'Subtask status updated successfully.');
    }
}
