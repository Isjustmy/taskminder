<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TaskNotification extends Notification
{
    public $task;
    public $teacherName;

    public function __construct($task, $teacherName)
    {
        $this->task = $task;
        $this->teacherName = $teacherName;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Tugas Baru: "' . $this->task->title . '" dari Guru ' . $this->teacherName)
            ->greeting('Halo!')
            ->line('Anda memiliki tugas baru: "' . $this->task->title . '" dengan deadline ' . $this->task->deadline . '.')
            ->line('Harap kerjakan sebelum deadline tiba.');
    }
    
    public function toArray($notifiable)
    {
        return [
            'task_title' => $this->task->title,
            'teacher_name' => $this->teacherName,
            'deadline' => $this->task->deadline,
        ];
    }
}
