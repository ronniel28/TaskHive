<?php

namespace App\Jobs;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


class DeleteExpiredTaskJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $taskId;

    public function __construct($taskId)
    {
        $this->taskId = $taskId;

    }

    public function handle()
    {
        $task = Task::onlyTrashed()->find($this->taskId);

        if ($task && $task->deleted_at && $task->deleted_at->diffInMinutes(now()) >= 1) {
            $task->forceDelete(); // Permanently delete
        }
    }

}
