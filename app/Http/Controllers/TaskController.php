<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status'      => ['required', 'string'],
            'priority'    => ['required', 'in:low,medium,high'],
            'due_date'    => ['required', 'date_format:Y-m-d'],
            'file'        => ['nullable', 'file', 'mimes:jpg,png,pdf,docx', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        // Create the task instance
        $task = new Task();
        $task->user_id     = $request->user()->id; // Get ID from JWT token
        $task->title       = $validated['title'];
        $task->description = $validated['description'];
        $task->status      = $validated['status'];
        $task->priority    = $validated['priority'];
        $task->due_date    = $validated['due_date'];

        // Handle File Upload
        if ($request->hasFile('file')) {
            $task->file = $request->file('file')->store('tasks', 'public');
        }

        $task->save();

        return response()->json([
            'status' => true,
            'message' => 'Task created successfully',
            'task' => $task
        ], 201);
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
