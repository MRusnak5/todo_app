<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Http\Request;
use App\Http\Resources\TodoListResource;;
use App\Models\TodoList;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use App\Jobs\SendTaskSharedNotification;
use App\Jobs\SendTaskUnsharedNotification;

class TodoListControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function testIndexMethodReturnsResourceCollection()
    {
        // Arrange
        $user = User::factory()->create();
        $todoList = TodoList::factory()->count(5)->create([
            'created_by' => $user->id,
        ]);
        $request = Request::create('/api/v1/todoLists?limit=5', 'GET');
        $request->headers->set('Authorization', 'Bearer '.$user->token);

        // Act
        $response = $this->actingAs($user)->get('api/v1/todoLists', ['Authorization' => 'Bearer '.$user->token]);
        $collection = TodoListResource::collection($todoList->take(5));

        // Assert
        $response->assertStatus(200);

    }

    public function testStoreTodoList()
    {
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)->get('api/v1/todoLists', ['Authorization' => 'Bearer '.$user->token]);

        $taskData = [
            'name' => 'Test Task',
            'description' => 'Test Task Description',
            'created_by' => 1,
        ];

        $response = $this->postJson('/api/v1/todoLists', $taskData);

        $response->assertStatus(201)
            ->assertJson([
                'data'=>[
                    'name' => 'Test Task',
                    'description' => 'Test Task Description',
                    'created_by' => 1,
                ]
            ]);

        $this->assertDatabaseHas('todo_lists', $taskData);
    }

    public function test_share_task_with_valid_inputs()
    {
        $user = User::factory()->create();
        $todoList = TodoList::factory()->create();
        $oldUserIds = $todoList->users()->pluck('id')->toArray();
        $userIds = User::factory()->count(3)->create()->pluck('id')->toArray();
        $response = $this->actingAs($user)->get('api/v1/todoLists', ['Authorization' => 'Bearer '.$user->token]);

        Queue::fake();
        $response = $this->json('POST', "/api/v1/todoLists/{$todoList->id}/shareTask", [
            'userIds' => $userIds,
        ]);

        $response->assertStatus(200);
        $newUserIds = $todoList->fresh()->users()->pluck('id')->toArray();
        $addedIds = array_values(array_diff($newUserIds, $oldUserIds));
        $removedIds = array_values(array_diff($oldUserIds, $newUserIds));

        $response->assertJson([
            'added' => $addedIds,
            'removed' => $removedIds,
        ]);

        Queue::assertPushed(SendTaskSharedNotification::class, function ($job) use ($addedIds, $todoList) {

            return $job->users === $addedIds;
        });

    }


    public function test_example()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
