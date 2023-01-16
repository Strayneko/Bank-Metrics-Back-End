<?php

namespace App\Http\Controllers;

use App\Http\Response\BaseResponse;
use App\Models\User;
use App\Models\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AuthUserContoller extends Controller
{
    public function register(Request $rq)
    {
        // validating form data (request)
        try {
            $rq->validate([
                'name' => ['required', 'min:5', 'max:50'],
                'email' => ['required', 'unique:users,email', 'email', 'max:50', 'min:3'],
                'password' => ['required', 'min:8']
            ]);
            // return error based on validation error
        } catch (\Illuminate\Validation\ValidationException $validate) {
            return response()->json([
                'status' => false,
                'message' => $validate->validator->errors()->all()
            ], 403);
        }

        // create new user
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

        // prepare login credential
        $credentials = [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
        ];
        // attemp auth
        if (!Auth::attempt($credentials)) return BaseResponse::error("Email or password wrong!", 401);

        // get user data
        $user = User::find(Auth::user()->id)->makeHidden(['created_at', 'updated_at']);
        // add auth token to user data
        $token = $user->createToken('auth_token');
        $user['auth'] = ['token' => 'Bearer ' . $token->plainTextToken];
        return BaseResponse::success(
            $user,
            'Login Success',
            200
        );
    }


    public function logout(Request $request)
    {
        // delete logouted user's token on personal access token table
        $request->user()->currentAccessToken()->delete();
        return BaseResponse::success(null, 'Logout Success');
    }
}
