<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use App\Models\StudentTasks;
use Carbon\Carbon;

class GradeTasks extends Command
{
    protected $signature = 'tasks:grade';
    protected $description = 'Grade tasks that have passed the deadline';

    public function handle()
    {
        $tasks = Task::where('deadline', '<', Carbon::now())->get();

        foreach ($tasks as $task) {
            $submission = StudentTasks::where('task_id', $task->id)
                ->where('is_submitted', true)
                ->first();

            if (!$submission) {
                StudentTasks::create([
                    'task_id' => $task->id,
                    'student_id' => $task->student_id,
                    'teacher_id' => $task->creator_id,
                    'score' => null,
                    'submitted_at' => null,
                    'updated_at' => Carbon::now(),
                ]);

                // Kirim notifikasi
                // Tambahkan notifikasi sesuai kebutuhan
            }
        }

        $this->info('Tasks graded successfully.');
    }
}
