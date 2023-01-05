<?php

namespace App\Http\Controllers;

use App\Http\Response\BaseResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthAdminController extends Controller
{
    public function login(Request $request){
        $name = $request->name;
        $email = $request->email;
        $password = $request->password;

        $admin = User::query()->where('name', $name)->first();
        if($admin == null){
            return BaseResponse::error('Name Admin Not Found', 400);
        }

        $admin = User::query()->where('email', $email)->first();
        if($admin == null){
            return BaseResponse::error('Email Admin Not Found', 400);
        }

        if(!Hash::check($password, $admin->password)){
            return BaseResponse::error('Password Admin Failed', 400);
        }

        $token = $admin->createToken('auth_token');
        return BaseResponse::success([$admin, 'auth' => [
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer'
        ]]);
    }
}
