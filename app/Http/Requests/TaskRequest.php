<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class TaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
            $this->merge([
                'user_id' => $this->input('user_id', Auth::id()),
                'status' => $this->input('status', 'to-do'),
                'is_draft' => $this->input('is_draft', 0),
                'parent_task_id' => (int)$this->input('parent_task_id') ? (int)$this->input('parent_task_id') : null,
            ]);
            // dd($this->all());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if ($this->isMethod('patch') && $this->routeIs('tasks.updateTaskStatus')) {
            return [
                'status' => 'required|in:to-do,in-progress,done',
            ];
        }

        return [
                'user_id' => 'required|exists:users,id',
                'title' => 'required|max:100',
                'content' => 'required',
                'status' => 'required|in:to-do,in-progress,done',
                'image_path' => 'nullable|image|max:4096',
                'parent_task_id' => 'nullable|exists:tasks,id',
                'is_draft' => 'required|boolean'
        ];
    }
}
