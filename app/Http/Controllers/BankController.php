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

                'name' => ['required'],
                'loaning_percentage' => ['required'],
                'max_age' => ['required'],
                'min_age' => ['required'],
                'marital_status' => ['required'],
                'nationality' => ['required'],
                'employment' => ['required'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $validate) {
            return BaseResponse::error('Wrong data format');
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

                'name' => ['required'],
                'loaning_percentage' => ['required'],
                'max_age' => ['required'],
                'min_age' => ['required'],
                'marital_status' => ['required'],
                'nationality' => ['required'],
                'employment' => ['required'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $validate) {
            return BaseResponse::error('Wrong data format');
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
