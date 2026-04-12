<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskDueSoon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
     use RefreshDatabase;

    public function test_user_can_register()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'token'
                ]);
    }

    public function test_user_can_login()
    {
        $user = \App\Models\User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'usermail' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'status' => true
                ])
                ->assertJsonStructure([
                    'token',
                    'user'
                ]);
    }
    
    /** @test */
public function authenticated_user_can_create_task()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/tasks', [
                'title'       => 'Test Task',
                'description' => 'Testing task creation',
                'due_date'    => now()->addDay()->toDateTimeString(),
                'status'      => 'pending',  // Add this
                'priority'    => 'medium',   // Add this
            ]);

        $response->assertStatus(201)
                ->assertJson([
                    'title' => 'Test Task'
                ]);
    }

    public function test_guest_cannot_create_task()
    {
        $response = $this->postJson('/api/tasks', [
            'title' => 'Unauthorized Task',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function it_dispatches_notification_when_task_is_due_soon()
    {
        // 1. Mock the Notification facade so nothing actually goes to Firebase
        Notification::fake();

        $user = User::factory()->create(['fcm_token' => 'mock-token']);
        
        // 2. Create a task due in 10 hours (within the 24h window)
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'due_date' => now()->addHours(10),
            'reminder_sent' => false
        ]);

        // 3. Run your logic (manually call the command or the logic inside it)
        // For this example, we'll assume you put the logic in a Command
        $this->artisan('tasks:send-reminders');

        // 4. Assert that the notification was "sent" to the user
        Notification::assertSentTo(
            $user, 
            TaskDueSoon::class,
            function ($notification, $channels) {
                return in_array('firebase', $channels);
            }
        );

        // 5. Assert the database was updated so we don't double-notify
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'reminder_sent' => true
        ]);
    }
    
    /** @test */
    public function it_does_not_notify_for_tasks_due_in_more_than_24_hours()
    {
        Notification::fake();

        $user = User::factory()->create(['fcm_token' => 'mock-token']);
        
        // Create a task due in 3 days
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'due_date' => now()->addDays(3),
            'reminder_sent' => false
        ]);

        $this->artisan('tasks:send-reminders');

        // Assert that NO notification was sent
        Notification::assertNothingSent();
    }

    /** @test */
    public function a_user_can_update_their_fcm_token()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
                        ->postJson('/api/fcm-token', [
                            'fcm_token' => 'new-firebase-token-123'
                        ]);

        $response->assertStatus(200);
        $this->assertEquals('new-firebase-token-123', $user->fresh()->fcm_token);
    }
}
