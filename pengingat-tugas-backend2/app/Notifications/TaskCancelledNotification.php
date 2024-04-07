<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskCancelledNotification extends Notification
{
    use Queueable;

    protected $oldTaskData;

    /**
     * Create a new notification instance.
     *
     * @param Task $oldTaskData
     * @return void
     */
    public function __construct(Task $oldTaskData)
    {
        $this->oldTaskData = $oldTaskData;
    }
    
    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [
        // 'mail',
        'database'
    ];
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
            ->subject('Pemberitahuan: Pembatalan Tugas')
            ->line('Tugas berikut telah dibatalkan:')
            ->line('Judul Tugas: ' . $this->oldTaskData->getOriginal('title'))
            ->line('Deskripsi Tugas: ' . $this->oldTaskData->getOriginal('description'))
            ->line('Deadline: ' . $this->oldTaskData->getOriginal('deadline'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'judul' => 'Pembatalan Tugas "' . $this->oldTaskData->getOriginal('title') . '"',
            'Keterangan' => 'Tugas "' . $this->oldTaskData->getOriginal('title') . '"' . ' dari Guru ' . $this->oldTaskData->getOriginal('teacher_name') . ' telah dibatalkan.',
            'data_tugas' => [
                'judul_tugas' => $this->oldTaskData->getOriginal('title'),
                'deskripsi_tugas' => $this->oldTaskData->getOriginal('description'),
                'deadline' => $this->oldTaskData->getOriginal('deadline')
            ]
        ];
    }
}
