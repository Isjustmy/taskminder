<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\FCM\FCMChannel;
use Kreait\Firebase\Messaging\CloudMessage;

class TaskUpdatedNotification extends Notification
{
    public $oldTask;
    public $newTask;


    /**
     * Create a new notification instance.
     *
     * @param  mixed  $oldTask
     * @param  mixed  $newTask
     * @return void
     */
    public function __construct($oldTask, $newTask)
    {
        $this->oldTask = $oldTask;
        $this->newTask = $newTask;

        // Convert deadline to Carbon instance if necessary
        if (!($this->oldTask->deadline instanceof Carbon)) {
            $this->oldTask->deadline = Carbon::parse($this->oldTask->deadline);
        }

        if (!($this->newTask->deadline instanceof Carbon)) {
            $this->newTask->deadline = Carbon::parse($this->newTask->deadline);
        }
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
            FCMChannel::class,
            'database',
            'mail',
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
        $deadlineChanged = $this->oldTask->deadline->ne($this->newTask->deadline);
        $deadlineMessage = $deadlineChanged
            ? 'Deadline baru: ' . $this->newTask->deadline->format('d-m-Y H:i:s')
            : 'Deadline tugas tidak berubah.';

        return (new MailMessage)
            ->subject('Pembertahuan: Pembaruan Tugas "' . $this->oldTask->title . '"')
            ->line('Tugas telah diperbarui dengan ' . $deadlineMessage . ' Silahkan periksa tugas untuk mengetahui perubahannya lebih lanjut.')
            ->action('Lihat Tugas', 'https://taskminder.pplgsmkn1ciomas.my.id/dashboard/task/student/' . $this->oldTask->id);
    }

    public function toFCM($notifiable): CloudMessage
    {
        $deadlineChanged = $this->oldTask->deadline->ne($this->newTask->deadline);
        $deadlineMessage = $deadlineChanged
            ? 'Deadline baru: ' . $this->newTask->deadline->format('d-m-Y H:i:s')
            : 'Deadline tugas tidak berubah.';

        return CloudMessage::new()
            ->withDefaultSounds()
            ->withNotification([
                'title' => 'Tugas Diperbarui: "' . $this->oldTask->title . '"',
                'body' => 'Tugas telah diperbarui dengan ' . $deadlineMessage . '. Silahkan periksa tugas untuk mengetahui perubahannya lebih lanjut.',
                'icon' => url('assets/taskminder_logo 1 (mini 150x150).png')
            ])
            ->withData([
                'title' => 'Pembertahuan: Pembaruan Tugas "' . $this->oldTask->title . '"',
                'description' => 'Tugas telah diperbarui. Silahkan periksa tugas untuk mengetahui perubahannya.',
                'icon' => url('assets/taskminder_logo 1 (mini 150x150).png'),
                'click_action' =>'https://taskminder.pplgsmkn1ciomas.my.id/dashboard/task/student/' . $this->oldTask->id,
            ])
            ->withHighestPossiblePriority();
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        $deadlineChanged = $this->oldTask->deadline->ne($this->newTask->deadline);
        $deadlineMessage = $deadlineChanged
            ? 'Deadline baru: ' . $this->newTask->deadline->format('d-m-Y H:i:s')
            : 'Deadline tugas tidak berubah.';

        return [
            'title' => 'Tugas Diperbarui: "' . $this->oldTask->title . '"',
            'message' => 'Tugas telah diperbarui dengan ' . $deadlineMessage . '. Silahkan periksa tugas untuk mengetahui perubahannya lebih lanjut.',
            'task_id' => $this->oldTask->id,
            'class_id' => $this->oldTask->class_id,
        ];
    }
}
