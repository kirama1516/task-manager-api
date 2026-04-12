<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FcmNotification;

class TaskDueSoon extends Notification
{
    protected $task;

    public function __construct($task) {
        $this->task = $task;
    }

    // Define that we are using a custom 'firebase' channel
    public function via($notifiable) {
        return ['firebase'];
    }

    public function toFirebase($notifiable)
    {
        return CloudMessage::new()
            ->withNotification(FcmNotification::create(
                'Task Deadline!',
                "Your task '{$this->task->title}' is due in less than 24 hours."
            ));
    }
}