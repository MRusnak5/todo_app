<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTodoListRequest;
use App\Http\Requests\UpdateTodoListRequest;
use App\Http\Resources\TodoListResource;
use App\Jobs\SendTaskFinishedNotification;
use App\Jobs\SendTaskSharedNotification;
use App\Jobs\SendTaskUnsharedNotification;
use App\Models\TodoList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;


class TodoListController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */


    public function index(Request $request): ResourceCollection
    {
        $category = $request->query('category');
        $status = $request->query('finished');

        $tasks = TodoList::when($category, function ($query, $category) {
            return $query->whereIn('task_categories_id', (array)$category);
        })->when($status, function ($query, $status) {
            return $query->whereIn('finished', (array)$status);
        })->where('created_by', auth()->user()->id)->withTrashed()->paginate($request->input('limit', 10));

        return TodoListResource::collection($tasks);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(StoreTodoListRequest $request): TodoListResource
    {
        return new TodoListResource(TodoList::create($request->validated()));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return TodoListResource
     */
    public function show(TodoList $todoList): TodoListResource
    {
        $todoList->load('users');
        return new TodoListResource($todoList);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(UpdateTodoListRequest $request, TodoList $todoList): TodoListResource
    {
        $todoList->update($request->validated());
        return new TodoListResource($todoList);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy(TodoList $todoList): JsonResponse
    {
        $todoList->delete();

        return response()->json(['message' => 'Task successfully deleted.']);
    }

    public function restore($todoList): TodoListResource
    {
        $todoList = TodoList::withTrashed()->findOrFail($todoList);
        $todoList->restore();

        return new TodoListResource($todoList);
    }

    public function finished($todoList): TodoListResource
    {
        $todoList = TodoList::findOrFail($todoList);

        if ($todoList->finished === false) {
            $todoList->update(['finished' => 1]);

            $emailJobs = new SendTaskFinishedNotification($todoList->id);
            $this->dispatch($emailJobs);
        }
        return new TodoListResource($todoList);
    }

    public function shareTask(Request $request, $todoList): JsonResponse
    {

        $request->validate([
            'userIds' => 'required|array',
            'userIds.*' => 'integer|exists:users,id',
        ]);

        $todoList = TodoList::findOrFail($todoList);


        $oldUserIds = $todoList->users()->pluck('id')->toArray();
        $todoList->users()->sync($request->userIds);
        $newUserIds = $todoList->users()->pluck('id')->toArray();

        $addedIds = array_values(array_diff($newUserIds, $oldUserIds));
        $removedIds = array_values(array_diff($oldUserIds, $newUserIds));

        if ($addedIds) {
            $emailJobs = new SendTaskSharedNotification($todoList->id, $addedIds);
            $this->dispatch($emailJobs);
        }
        if ($removedIds) {
            $emailJobs = new SendTaskUnsharedNotification($todoList->id, $addedIds);
            $this->dispatch($emailJobs);
        }
        return response()->json(['added' => $addedIds,
            'removed' => $removedIds]);

    }
}
