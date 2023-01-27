<?php

namespace App\Http\Controllers;

use App\Http\Response\BaseResponse;
use App\Jobs\SendEmail;
use App\Mail\SendMail;
use App\Models\PasswordReset;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class PasswordResets extends Controller
{
    public function password_reset(Request $request)
    {
        //validate user input
        $request->validate([
            'email' => 'required|email',
        ]);

        //to save req email in variable email
        $email = $request->email;

        //to get data email from table User
        $user = User::where('email', $email)->first(); // null

        //to check whether the email exists or not
        if (!$user) {
            return BaseResponse::error('Email Does Not Exist', 404);
        }

        // check whether the user has verified email or not
        if ($user['confirmed'] != true) {
            return BaseResponse::error('Please Verify Email First');
        }

        //to create token/code
        $token = Str::random(40);

        //to add data to table PasswordReset
        PasswordReset::create([
            'email' => $request->email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);

        // send email using queue
        SendEmail::dispatch($request->email, $token, 'reset_password');

        return BaseResponse::success('Password Reset Email Sent.. check your email', 200);
    }

    public function reset(Request $request, $token)
    {
        //validate input password user
        try {
            $request->validate([
                'password' => ['required', 'min:8', 'confirmed'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $validate) {
            return response()->json([
                'status' => false,
                'message' => $validate->validator->errors()->all()
            ], 403);
        }

        //to retrieve the token from the password reset table
        $passwordReset = PasswordReset::where('token', $token)->first();

        //to check whether the token exists in the database or not
        if (!$passwordReset) {
            return BaseResponse::error('Token is invalid or expired', 404);
        }

        //to retrieve data from the user table based on the email in the passwordreset table
        $user = User::where('email', $passwordReset->email)->first();
        $user['confirmed'] = true;
        //to req passwrod from table user
        $user->password = $request->password;
        //to update password in table user
        $user->update();

        //to delete data email in password reset table after password update
        PasswordReset::where('email', $user->email)->delete();

        return BaseResponse::success('Reset Password Success', 200);
    }
}
