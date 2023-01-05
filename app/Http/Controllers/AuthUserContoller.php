<?php

namespace App\Http\Controllers;

use App\Http\Response\BaseResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthUserContoller extends Controller
{
    public function register(Request $rq)
    {
        try {
            $rq->validate([
                'name' => ['required'],
                'email' => ['required', 'unique:users,email'],
                'password' => ['required', 'min:8']
            ]);
        } catch (\Illuminate\Validation\ValidationException $validate) {
            return response()->json([
                'status' => false,
                'message' => $validate->validator->errors()
            ], 403);
        }

        $payload = $rq->all();
        $payload['role_id'] = 1;
        $register = User::create($payload);
        return response()->json([
            'status' => true,
            'message' => 'Register Success',
            'data' => $register
        ], 201);
    }

    public function login(Request $request)
    {
        $name = $request->name;
        $email = $request->email;
        $password = $request->password;

        $user = User::query()->where('name', $name)->first();
        if ($user == null) {
            return BaseResponse::error('Name Not Found', 400);
        }

        $user = User::query()->where('email', $email)->first();
        if ($user == null) {
            return BaseResponse::error('Email Not Found', 400);
        }

        if (!Hash::check($password, $user->password)) {
            return BaseResponse::error('Password Failed', 400);
        }

        // add auth token to user data
        $token = $user->createToken('auth_token');
        $user['auth'] = ['tokeen' => 'Bearer ' . $token->plainTextToken];
        return BaseResponse::success(
            $user,
            'Login Success',
            200
        );
    }
}
