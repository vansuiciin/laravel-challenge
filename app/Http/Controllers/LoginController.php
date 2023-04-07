<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\UserResource; // Import the UserResource
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        try {
            $validateUser = Validator::make($request->all(), 
            [
                'email' => 'required|email|min:6',
                'password' => 'required|min:8',
            ]);

            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Model not found.'
                ]);
            }

            if (Auth::attempt($request->only('email', 'password'))) {
                $token = $user->createToken('api-token')->plainTextToken;
                return response()->json([
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'user' => new UserResource($user), // Use a Resource to format the user data
                ]);
            } else {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
