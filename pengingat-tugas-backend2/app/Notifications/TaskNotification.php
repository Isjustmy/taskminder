<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\FCM\FCMChannel;
use Kreait\Firebase\Messaging\CloudMessage;
use App\Models\User;

class TaskNotification extends Notification
{

    protected $task;
    protected $teacherName;

    public function __construct($task, $teacherName)
    {
        $this->task = $task;
        $this->teacherName = $teacherName;
    }

    public function via($notifiable): array
    {

        return [
            FCMChannel::class,
            'mail',
            'database',
        ];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Tugas Baru')
            ->line('Judul Tugas: ' . $this->task->title)
            ->line('Ditugaskan oleh guru ' . $this->teacherName)
            ->line('Deskripsi Tugas: ' . $this->task->description)
            ->line('Batas waktu mengerjakan: ' . $this->task->deadline)
            ->line('Harap untuk mengerjakan tugas sebelum batas waktu tiba.')
            ->action('Lihat Tugas', url('/tasks/' . $this->task->id));
    }

    // kirim push notification ke siswa melalui FCM
    public function toFCM($notifiable): CloudMessage
    {
        return CloudMessage::new()
            ->withDefaultSounds()
            ->withNotification([
                'title' => 'Tugas Baru: "' . $this->task->title . '"',
                'body' => 'Tugas baru telah ditugaskan oleh guru ' . $this->teacherName . ' , dengan batas waktu ' . $this->task->deadline,
                'icon' => '../../public/assets/taskminder_logo 1 (mini 150x150).png'
            ])
            ->withData([
                'title' => 'Tugas Baru: "' . $this->task->title . '"',
                'description' => 'Tugas baru oleh guru ' . $this->teacherName . ' telah ditugaskan dengan judul: ' . $this->task->title . '. Batas waktu mengerjakan tugas: ' . $this->task->deadline . ', Harap untuk mengerjakan tugas sebelum batas waktu tiba.',
                'deadline' => $this->task->deadline,
                'priority' => 'high'
            ]);
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Tugas Baru: "' . $this->task->title . '"',
            'description' => 'Tugas baru oleh guru ' . $this->teacherName . ' telah ditugaskan dengan judul: ' . $this->task->title . '. Batas waktu mengerjakan tugas: ' . $this->task->deadline . ', Harap untuk mengerjakan tugas sebelum batas waktu tiba.',
            'deadline' => $this->task->deadline,
        ];
    }
}
