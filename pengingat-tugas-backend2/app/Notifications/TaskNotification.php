<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskNotification extends Notification
{
    use Queueable;

    protected $task;
    protected $teacherName;

    public function __construct($task, $teacherName)
    {
        $this->task = $task;
        $this->teacherName = $teacherName;
    }

    public function via($notifiable)
    {
        return [
            // 'mail',
            'database'
            ]; // Sesuaikan dengan metode notifikasi yang Anda gunakan (contoh: mail, database, etc.)
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Tugas Baru')
            ->line('Tugas baru telah ditugaskan oleh ' . $this->teacherName)
            ->line('Judul Tugas: ' . $this->task->title)
            ->line('Deskripsi Tugas: ' . $this->task->description)
            ->action('Lihat Tugas', url('/tasks/' . $this->task->id))
            ->line('Terima kasih telah menggunakan aplikasi kami!');
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Tugas ' . $this->task->mata_pelajaran . ' baru',
            'description' => 'Tugas baru oleh guru ' . $this->teacherName . ' telah ditugaskan dengan judul: ' . $this->task->title . '. Harap segera kerjakan tugas sebelum deadline tiba',
            'deadline' => $this->task->deadline,
        ];
    }
}
