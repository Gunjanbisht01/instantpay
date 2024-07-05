<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Board;
use App\Models\Task;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    //get all tasks
    public function index()
    {
        $tasks = Task::whereHas('board', function ($query) {
            $query->where('user_id', auth()->id());
        })->get();

        if ($tasks->isEmpty()) {
            return response()->json(['message' => 'No tasks found'], 200);
        }

        return response()->json(['tasks' => $tasks], 200);
    }

    //get specific board with taks
    public function boardTask(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'board_id' => 'required|exists:boards,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error_message' => $validator->errors()], 400);
        }

        $board = Board::findOrFail($request->board_id);
        //validate user can see current board related tasks
        if ($board->user_id !== auth()->id()) {
            return response()->json(['error_message' => 'Unauthorized to view this board related tasks.'], 403);
        }

        $tasks = $board->tasks;

        return response()->json(['tasks' => $tasks->isEmpty() ? 'No tasks found' : $tasks], 200);
    }

    //show specific task
    public function show(Task $task)
    {
        $board = Board::findOrFail($task->board_id);
        //validate user can see current task
        if ($board->user_id !== auth()->id()) {
            return response()->json(['error_message' => 'Unauthorized to view this task.'], 403);
        }
        return response()->json(['task' => $task], 200);
    }

    //store task
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'board_id' => 'required|exists:boards,id',
            'status' => 'required|integer|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['error_message' => $validator->errors()], 400);
        }

        $board = Board::findOrFail($request->board_id);
        //validate user can add tasks for current board
        if ($board->user_id !== auth()->id()) {
            return response()->json(['error_message' => 'Unauthorized to create this task for related board.'], 403);
        }

        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status,
            'board_id' => $request->board_id,
            'comment' => $request->comment,
        ]);

        return response()->json(['message' => 'Task created successfully', 'task' => $task], 201);
    }

    //update task
    public function update(Request $request, Task $task)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'board_id' => 'required|exists:boards,id',
            'status' => 'required|integer|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['error_message' => $validator->errors()], 400);
        }

        //validate user can update tasks for current board
        $board = Board::findOrFail($request->board_id);

        if ($board->user_id !== auth()->id()) {
            return response()->json(['error_message' => 'Unauthorized to update this task for related board.'], 403);
        }

        $task->update([
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status,
            'board_id' => $request->board_id,
            'comment' => $request->comment,
        ]);

        return response()->json(['message' => 'Task updated successfully', 'task' => $task], 200);
    }

    //delete task
    public function destroy(Task $task)
    {
        try {

            $task->delete();
            return response()->json(['message' => 'Task deleted successfully'], 200);
        } catch (\Exception $e) {

            \Log::error($e->getMessage());
            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }
}
