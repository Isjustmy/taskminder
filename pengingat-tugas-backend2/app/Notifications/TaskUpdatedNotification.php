<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\FCM\FCMChannel;
use Kreait\Firebase\Messaging\CloudMessage;

class TaskUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $task;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($task)
    {
        $this->task = $task;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database', FCMChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Pembertahuan: Pembaruan Tugas "' . $this->task->title . '"')
                    ->line('Sebuah tugas baru saja diperbarui. Silahkan periksa tugas untuk mengetahui perubahannya.')
                    ->action('Lihat Tugas', url('/tasks/' . $this->task->id));
    }

    public function toFCM($notifiable): CloudMessage
    {
        return CloudMessage::new()
            ->withDefaultSounds()
            ->withNotification([
                'title' => 'Pembertahuan: Pembaruan Tugas "' . $this->task->title . '"',
                'body' => 'Tugas telah diperbarui. Silahkan periksa tugas untuk mengetahui perubahannya.',
                'icon'=> '../../public/assets/taskminder_logo 1 (mini 150x150).png'
            ])         
            ->withData([
                'title' => 'Pembertahuan: Pembaruan Tugas "' . $this->task->title . '"',
                'description' => 'Tugas telah diperbarui. Silahkan periksa tugas untuk mengetahui perubahannya.',
                'priority' => 'high'
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Pembertahuan: Pembaruan Tugas "' . $this->task->title . '"',
            'message' => 'Tugas telah diperbarui. Silahkan periksa tugas untuk mengetahui perubahannya.',
            'task_id' => $this->task->id,
            'class_id' => $this->task->class_id,
        ];
    }
}
