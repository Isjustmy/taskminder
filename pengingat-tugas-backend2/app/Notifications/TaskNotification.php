<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\FCM\FCMChannel;
use Kreait\Firebase\Messaging\CloudMessage;
use App\Models\User;

class TaskNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $task;
    protected $teacherName;

    public function __construct($task, $teacherName)
    {
        $this->task = $task;
        $this->teacherName = $teacherName;
    }

    public function via($notifiable): array
    {

        return ['database', FCMChannel::class];
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

    // kirim push notification ke siswa melalui FCM
    public function toFCM($notifiable): CloudMessage
    {
        return CloudMessage::new()
            ->withDefaultSounds()
            ->withNotification([
                'title' => 'Tugas Baru',
                'body' => 'Tugas baru telah ditugaskan oleh ' . $this->teacherName,
                'icon'=> '../../public/assets/taskminder_logo 1 (mini 150x150).png'
            ])         
            ->withData([
                'title' => 'Tugas ' . $this->task->mata_pelajaran . ' baru',
                'description' => 'Tugas baru oleh guru ' . $this->teacherName . ' telah ditugaskan dengan judul: ' . $this->task->title . '. Harap segera kerjakan tugas sebelum deadline tiba',
                'deadline' => $this->task->deadline,
                'priority' => 'high'
            ]);
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
