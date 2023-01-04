<?php

namespace App\Http\Controllers;

use App\Http\Response\BaseResponse;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    function index(){
        $admin = User::query()->where('role_id', 2)->get();
        BaseResponse::success($admin);
    }

    function show($id){
        $admin = User::query()->where('id',$id)->where('role_id', 2)->first();
        if (!$admin) BaseResponse::error('Data was not found',404);

        BaseResponse::success($admin);
    }

    function store(Request $request){
        try{
            $validated = $request->validate([

                 'name'=> ['required'],
                 'email' => ['required', 'unique:users,email'],
                 'password' => ['required', 'min:8']
            ]);
        }
        catch (\Illuminate\Validation\ValidationException $validate)
        {
            BaseResponse::error('wrong data format');
        }

        $user = User::create($validated);
        BaseResponse::success($user, 'Data was successfully created');
    }

    function update(Request $request, $id){
        $admin = User::query()->where('id', $id)->first();
        if (!$admin) BaseResponse::error('Data was not found', 404);

        try{
            $validated = $request->validate([

                 'name'=> ['required'],
                 'email' => ['required', 'unique:users,email'],
                 'password' => ['required', 'min:8']
            ]);
        }
        catch (\Illuminate\Validation\ValidationException $validate)
        {
            BaseResponse::error('wrong data format');
        }

        $admin->fill($validated);
        $admin->save();
        BaseResponse::success($admin, 'Data was successfully updated');
    }


}
