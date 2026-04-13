<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Notifications\TaskDueSoon;
use Illuminate\Console\Command;

class SendTaskReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-task-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends notifications for tasks due within 24 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Your logic is perfectly fine!
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
        
        $this->info('Reminders sent successfully.');
    }
}