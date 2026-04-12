<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Notifications\TaskDueSoon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:send-task-reminders')]
#[Description('Command description')]
class SendTaskReminders extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Find tasks due within the next 24 hours that haven't been notified
        $tasks = Task::where('due_date', '>', now())
                    ->where('due_date', '<=', now()->addHours(24))
                    ->where('reminder_sent', false)
                    ->with('user')
                    ->get();

        foreach ($tasks as $task) {
            if ($task->user->fcm_token) {
                $task->user->notify(new TaskDueSoon($task));
                $task->update(['reminder_sent' => true]);
            }
        }
    }
}
