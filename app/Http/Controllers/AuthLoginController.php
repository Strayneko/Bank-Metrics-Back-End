<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Response\BaseResponse;

class AuthLoginController extends Controller
{
    public function login(Request $request){
        $name = $request->name;
        $email = $request->email;
        $password = $request->password;

        $user = User::query()->where('name', $name)->first();
            if($user == null){
            return BaseResponse::error('Name Not Found', 400);
            }

        $user = User::query()->where('email', $email)->first();
            if($user == null){
                return BaseResponse::error('Email Not Found', 400);
            }

        if(!Hash::check($password, $user->password)){
            return BaseResponse::error('Password Failed', 400);
        }

        $token = $user->createToken('auth_token');
        return BaseResponse::success([$user,
                                       'auth' => ['token' => $token->plainTextToken,
                                                  'token_type' => "Bearer" ]],
                                      'Login Success', 200);
     }
}
