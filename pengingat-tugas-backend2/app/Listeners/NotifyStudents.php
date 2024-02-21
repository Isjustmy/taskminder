<?php

namespace App\Listeners;

use App\Events\TaskCreated;
use App\Notifications\TaskNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyStudents implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  TaskCreated  $event
     * @return void
     */
    public function handle(TaskCreated $event)
    {
        $task = $event->task;
        $students = $task->studentClass->students;

        foreach ($students as $student) {
            $student->notify(new TaskNotification($task, $event->teacherName));
        }
    }
}
