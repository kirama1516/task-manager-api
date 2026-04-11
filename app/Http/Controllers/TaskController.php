<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->user()->tasks();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        return response()->json($query->paginate(10));
    }

    public function store(TaskRequest $request)
    {
        $data = $request->validated();

        $task = new Task($data);
        $task->user_id = $request->user()->id;

        if ($request->hasFile('file')) {
            $task->file = $request->file('file')->store('tasks', 'public');
        }

        $task->save();

        return response()->json($task, 201);
    }

    public function show(Request $request, int $id)
    {
        $task = $request->user()->tasks()->findOrFail($id);

        return response()->json($task);
    }

    public function update(TaskRequest $request, int $id)
    {
        $task = $request->user()->tasks()->findOrFail($id);

        $data = $request->validated();

        if ($request->hasFile('file')) {
            $data['file'] = $request->file('file')->store('tasks', 'public');
        }

        $task->update($data);

        return response()->json($task);
    }

    public function destroy(Request $request, int $id)
    {
        $task = $request->user()->tasks()->findOrFail($id);
        $task->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
