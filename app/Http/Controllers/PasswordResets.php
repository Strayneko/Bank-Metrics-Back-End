<?php

namespace App\Http\Controllers;

use App\Http\Response\BaseResponse;
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
    public function password_reset(Request $request){
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->email;

        $user = User::where('email', $email)->first();
        if(!$user){
            return BaseResponse::error('Email Does Not Exist', 404);
        }

        $token = Str::random(40);

        PasswordReset::create([
            'email' => $request->email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);

        dump("http://127.0.0.1:8000/api/reset/user/" . $token);

        Mail::send('emails.index', ['token' => $token], function(Message $message)use($email){
            $message->to($email);
            $message->subject('Password Reset');
        });

        return BaseResponse::success('Password Reset Email Sent.. check your email', 200);

    }

    public function reset(Request $request, $token){
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        $passwordReset = PasswordReset::where('token', $token)->first();

        if(!$passwordReset){
            return BaseResponse::error('Token is invalid or expired', 404);
        }

        $user = User::where('email', $passwordReset->email)->first();
        $user->password = $request->password;
        $user->update();

        PasswordReset::where('email', $user->email)->delete();

        return BaseResponse::success('Reset Password Success', 200);
    }
}
