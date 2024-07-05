<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    //get all users
    public function getUsers()
    {
        $users = User::get();
        return response()->json(['message' => 'All users data', 'users' => $users], 200);
    }

    //show authenticated user data
    public function show()
    {
        $user = Auth::user();
        return response()->json(['message' => 'User data', 'user' => $user], 200);
    }

    //create user
    public function addUser(Request $request)
    {
        //validation check
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['error_message' => $validator->errors()->first()], 400);
        }

        try {

            $input = $request->all();
            $input['password'] = Hash::make($input['password']);
            $user = User::create($input);
            return response()->json(['message' => 'User created successfully', 'user' => $user], 201);
        } catch (\Exception $e) {

            \Log::error($e->getMessage());
            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }
}
