<?php

namespace App\Jobs;

use App\Models\StudentTasks;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Foundation\Bus\Dispatchable;

class MarkLateSubmissionsJob
{
    use Dispatchable;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $now = Carbon::now();

        $tasks = Task::with('studentTasks')
            ->where('deadline', '<', $now)
            ->get();

        foreach ($tasks as $task) {
            foreach ($task->studentTasks as $studentTask) {
                if (!$studentTask->is_submitted && !$studentTask->is_late) {
                    $studentTask->is_late = true;
                    $studentTask->save();
                }
            }
        }
    }
}
