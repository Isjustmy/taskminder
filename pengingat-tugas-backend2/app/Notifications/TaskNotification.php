<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\FCM\FCMChannel;
use Kreait\Firebase\Messaging\CloudMessage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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
            'database',
            'mail',
        ];
    }

    public function toMail($notifiable)
    {
        $formattedDeadline = Carbon::parse($this->task->deadline)->translatedFormat('d F Y H:i:s');

        return (new MailMessage)
            ->subject('Tugas Baru')
            ->line('Judul Tugas: ' . $this->task->title)
            ->line('Ditugaskan oleh guru ' . $this->teacherName)
            ->line('Deskripsi Tugas: ' . $this->task->description)
            ->line('Batas waktu mengerjakan: ' . $formattedDeadline)
            ->line('Harap untuk mengerjakan tugas sebelum batas waktu tiba.')
            ->action('Lihat Tugas', 'https://taskminder.pplgsmkn1ciomas.my.id/dashboard/task/student/' . $this->task->id);
    }

    // kirim push notification ke siswa melalui FCM
    public function toFCM($notifiable): CloudMessage
    {
        $formattedDeadline = Carbon::parse($this->task->deadline)->translatedFormat('d F Y H:i:s');

        return CloudMessage::new()
            ->withDefaultSounds()
            ->withNotification([
                'title' => 'Tugas Baru: "' . $this->task->title . '"',
                'body' => 'Tugas baru telah ditugaskan oleh guru ' . $this->teacherName . ' , dengan batas waktu ' . $formattedDeadline,
                'icon' => url('assets/taskminder_logo 1 (mini 150x150).png')
            ])
            ->withData([
                'icon' => url('assets/taskminder_logo 1 (mini 150x150).png'),
                'click_action' => 'https://taskminder.pplgsmkn1ciomas.my.id/dashboard/task/student/' . $this->task->id,
            ])
            ->withHighestPossiblePriority();
    }

    public function toArray($notifiable)
    {
        $formattedDeadline = Carbon::parse($this->task->deadline)->translatedFormat('d F Y H:i:s');

        return [
            'title' => 'Tugas Baru: "' . $this->task->title . '"',
            'description' => 'Tugas baru oleh guru ' . $this->teacherName . ' telah ditugaskan dengan judul: ' . $this->task->title . '. Batas waktu mengerjakan tugas: ' . $formattedDeadline . ', Harap untuk mengerjakan tugas sebelum batas waktu tiba.',
            'deadline' => $formattedDeadline,
            'link' => 'https://taskminder.pplgsmkn1ciomas.my.id/dashboard/task/student/' . $this->task->id
        ];
    }
}