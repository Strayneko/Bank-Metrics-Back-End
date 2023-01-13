<?php

namespace App\Http\Controllers;

use App\Http\Response\BaseResponse;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    function index()
    {
        $admin = User::query()->where('role_id', 2)->get();
        return BaseResponse::success($admin);
    }

    function show($id)
    {
        $admin = User::query()->where('id', $id)->where('role_id', 2)->first();
        if (!$admin) return BaseResponse::error('Data was not found', 404);

        return BaseResponse::success($admin);
    }

    function store(Request $request)
    {
        try {
            $validated = $request->validate([

                'name' => ['required', 'min:5', 'max:50'],
                'email' => ['required', 'unique:users,email', 'email', 'min:3', 'max:50'],
                'password' => ['required', 'min:8']
            ]);
        } catch (\Illuminate\Validation\ValidationException $validate) {
            return BaseResponse::error($validate->validator->errors()->all());
        }

        $validated['role_id'] = 2;
        $user = User::create($validated);
        return BaseResponse::success($user, 'Data was successfully created');
    }

    function update(Request $request, $id)
    {
        $admin = User::query()->where('id', $id)->first();
        if (!$admin) BaseResponse::error('Data was not found', 404);

        try {
            $validated = $request->validate([

                'name' => ['required', 'min:5', 'max:50'],
                'email' => ['required', 'unique:users,email', 'email', 'min:3', 'max:50'],
                'password' => ['required', 'min:8']
            ]);
        } catch (\Illuminate\Validation\ValidationException $validate) {
            return BaseResponse::error($validate->validator->errors()->all());
        }

        $admin->fill($validated);
        $admin->save();
        return BaseResponse::success($admin, 'Data was successfully updated');
    }
}
