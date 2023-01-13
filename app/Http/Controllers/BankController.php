<?php

namespace App\Http\Controllers;

use App\Http\Response\BaseResponse;
use App\Models\Bank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BankController extends Controller
{
    function index()
    {
        $bank = Bank::query()->get();
        return BaseResponse::success($bank);
    }

    function show($id)
    {
        $bank = Bank::query()->where('id', $id)->first();
        if (!$bank) BaseResponse::error('Data was not found', 404);
        return BaseResponse::success($bank);
    }

    function store(Request $request)
    {
        try {
            $validated = $request->validate([

                'name' => ['required', 'max:50', 'min:3'],
                'loaning_percentage' => ['required', 'numeric', 'min:1', '100'],
                'max_age' => ['required', 'min:1', 'max:150',  'numeric'],
                'min_age' => ['required', 'min:1', 'max:150'],
                'marital_status' => ['required', 'numeric', 'digits_between:0,1'],
                'nationality' => ['required', 'numeric', 'min:1'],
                'employment' => ['required', 'numeric', 'digits_between:0,1'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $validate) {
            return BaseResponse::error($validate->validator->errors()->all());
        }

        $bank = Bank::create($validated);
        return BaseResponse::success($bank, 'Data was successfully created');
    }

    function update(Request $request)
    {
        // get authenticated user
        $user = Auth::user();
        $bank = Bank::query()->where('id', $user->id)->first();
        if (!$bank) BaseResponse::error('Data was not found', 404);
        try {
            $validated = $request->validate([
                'name' => ['required', 'max:50', 'min:3'],
                'loaning_percentage' => ['required', 'numeric', 'min:1', '100'],
                'max_age' => ['required', 'min:1', 'max:150',  'numeric'],
                'min_age' => ['required', 'min:1', 'max:150'],
                'marital_status' => ['required', 'numeric', 'digits_between:0,1'],
                'nationality' => ['required', 'numeric', 'min:1'],
                'employment' => ['required', 'numeric', 'digits_between:0,1'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $validate) {
            return BaseResponse::error($validate->validator->errors()->all());
        }

        $bank->fill($validated);
        return BaseResponse::success($bank, 'Data was successfully updated');
    }

    function destroy($id)
    {
        $bank = Bank::query()->where('id', $id)->first();
        if (!$bank) return BaseResponse::error('Data was not found', 404);
        $bank->delete();
        return BaseResponse::success($bank, 'Data was successfully deleted');
    }
}
