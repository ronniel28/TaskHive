<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory;
    use SoftDeletes;
    
    protected $fillable = [
        'user_id',
        'parent_task_id',
        'title',
        'content',
        'status',
        'is_draft',
        'image_path',
    ];

    public function user() 
    {
        return $this->belongsTo(User::class);
    }

    public function subtasks()
    {
        return $this->hasMany(Task::class, 'parent_task_id');
    }

    public function parentTask()
    {
        return $this->belongsTo(Task::class, 'parent_task_id');
    }

    public function updateProgress()
    {
        $totalSubtasks = $this->subtasks()->count();
        $completedSubtasks = $this->subtasks()->where('status', 'done')->count();

        if ($totalSubtasks > 0 && $totalSubtasks == $completedSubtasks) {
            $this->status = 'done';
            $this->save();
        }else {
            $this->status = 'in-progress';
            $this->save();
        }
    }

    public function scopeSearchByTitle($query, $title)
    {
        return $query->where('title', 'like', '%' . $title . '%');
    }

    public function scopeFilterByStatus($query, $status)
    {
        if ($status === 'draft') {
            return $query->where('is_draft', true);
        } elseif ($status === 'trash') {
            return $query->onlyTrashed();
        }

        return $query->where('status', $status)
                     ->where('is_draft', false);
    }
}
