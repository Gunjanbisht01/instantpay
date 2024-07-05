<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Board;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BoardController extends Controller
{
    /**
     * All boards with tasks
     */
    public function index()
    {
        $user = Auth::user();
        $boards = $user->boards()->with('tasks')->get();

        if ($boards->isEmpty()) {
            return response()->json(['message' => 'No boards found'], 200);
        }

        return response()->json(['boards' => $boards], 200);
    }

    //get specific board with taks
    public function show(Board $board)
    {
        //validate user can see current board related tasks
        if ($board->user_id !== auth()->id()) {
            return response()->json(['error_message' => 'Unauthorized to view this board related tasks.'], 403);
        }

        $board->tasks;
        return response()->json(['board' => $board], 200);
    }

    /**
     * Store board
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255|unique:boards,title',
            'status' => 'required|integer|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['error_message' => $validator->errors()], 400);
        }

        try {
            $board = Auth::user()->boards()->create(['title' => $request->title, 'status' => $request->status,]);

            return response()->json(['message' => 'Board created successfully', 'board' => $board], 201);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }

    /**
     * Update board
     */
    public function update(Request $request, Board $board)
    {
        //validate user can update current board
        if ($board->user_id !== auth()->id()) {
            return response()->json(['error_message' => 'Unauthorized to update this board.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255|unique:boards,title,' . $board->id,
            'status' => 'required|integer|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['error_message' => $validator->errors()], 400);
        }

        try {
            $board->update(['title' => $request->title, 'status' => $request->status,]);

            return response()->json(['message' => 'Board updated successfully', 'board' => $board], 200);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }

    /**
     * delete board
     */
    public function destroy(Board $board)
    {
        if ($board->user_id !== auth()->id()) {
            return response()->json(['error_message' => 'Unauthorized to delete this board.'], 403);
        }

        try {
            $board->delete();

            return response()->json(['message' => 'Board deleted successfully'], 200);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }
}
