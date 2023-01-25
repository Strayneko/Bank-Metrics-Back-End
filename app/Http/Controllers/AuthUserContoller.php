<?php

namespace App\Http\Controllers;

use App\Http\Response\BaseResponse;
use App\Jobs\SendEmail;
use App\Models\User;
use App\Models\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Faker\Provider\Base;

class AuthUserContoller extends Controller
{
    function register(Request $rq)
    {
        //validate user input register
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

        //payload request all register
        $payload = $rq->all();

        $confirmation_code = Str::random(30);
        // set default role id for user
        $payload['role_id'] = 1;
        $payload['confirmation_code'] = $confirmation_code;
        //create all input register user
        $register = User::create($payload);

        // send email using queue
        SendEmail::dispatch($rq->email, $confirmation_code, 'verification');

        return response()->json([
            'status' => true,
            'message' => 'Register success',
            'data' => $register
        ], 201);
    }

    function login(Request $request)
    {
        // prepare login credential
        $credentials = [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
        ];

        $email = $request->email;
        $confirmed = User::where('email', $email)->first();

        // attemp auth
        if (!Auth::attempt($credentials)) {
            return BaseResponse::error("Email or password wrong!", 401);
        }

        if ($confirmed['confirmed'] != true) {
            return BaseResponse::error("Please Verify Your Email First");
        }

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


    function logout(Request $request)
    {
        //to delete token after user logout
        $request->user()->currentAccessToken()->delete();
        return BaseResponse::success(null, 'Logout Success');
    }

    function verification($confirmation_code)
    {
        if (!$confirmation_code) {
            return BaseResponse::error('Not Confirmation Code');
        }

        $user = User::where('confirmation_code', $confirmation_code)->where('role_id', 1)->first();

        if (!$user) {
            return BaseResponse::error('Not Confirmation Code');
        }

        $dateTime = Carbon::now()->format('Y-m-d H:i:s');
        $user->confirmed = true;
        $user->confirmation_code = null;
        $user->email_verified_at = $dateTime;
        $user->save();

        return BaseResponse::success('Verification Success', 200);
    }
}
