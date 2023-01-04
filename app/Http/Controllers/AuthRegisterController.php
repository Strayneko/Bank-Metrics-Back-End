<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AuthRegisterController extends Controller
{
    public function register(Request $rq){
        try {
             $rq->validate([
                 'name'=> ['required'],
                 'email' => ['required', 'unique:users,email'],
                 'password' => ['required', 'min:8']
             ]);
        } catch (\Illuminate\Validation\ValidationException $validate){
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
}
