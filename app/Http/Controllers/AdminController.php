<?php

namespace App\Http\Controllers;

use App\Http\Response\BaseResponse;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    function index()
    {
        // get all user data with role_id = 2 (admin)
        $admin = User::query()->where('role_id', 2)->get();
        return BaseResponse::success($admin);
    }

    function show($id)
    {
        // find user data with role_id = 2 (admin) and id = $id
        $admin = User::query()->where('id', $id)->where('role_id', 2)->first();
        if (!$admin) return BaseResponse::error('Data was not found', 404);

        return BaseResponse::success($admin);
    }

    function store(Request $request)
    {
        // validating form data
        try {
            $validated = $request->validate([

                'name' => ['required', 'min:5', 'max:50'],
                'email' => ['required', 'unique:users,email', 'email', 'min:3', 'max:50'],
                'password' => ['required', 'min:8']
            ]);
            // return error based on validation process
        } catch (\Illuminate\Validation\ValidationException $validate) {
            return BaseResponse::error($validate->validator->errors()->all());
        }
        // creating new admin
        $validated['role_id'] = 2;
        $validated['confirmed']= true;
        $user = User::create($validated);
        return BaseResponse::success($user, 'Data was successfully created');
    }

    function update(Request $request, $id)
    {
        // find user data with role = 2 (admin) and certain id
        $admin = User::query()->where('id', $id)->first();
        if (!$admin) BaseResponse::error('Data was not found', 404);
        
        // validating form data
        try {
            $validated = $request->validate([

                'name' => ['required', 'min:5', 'max:50'],
                'email' => ['required', 'unique:users,email', 'email', 'min:3', 'max:50'],
                'password' => ['required', 'min:8']
            ]);

            // return error based on validation process
        } catch (\Illuminate\Validation\ValidationException $validate) {
            return BaseResponse::error($validate->validator->errors()->all());
        }
        // updating admin's data
        $admin->fill($validated);
        $admin->save();
        return BaseResponse::success($admin, 'Data was successfully updated');
    }
}
