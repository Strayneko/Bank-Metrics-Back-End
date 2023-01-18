<?php

namespace App\Http\Controllers;

use App\Http\Response\BaseResponse;
use App\Models\Bank;
use Illuminate\Http\Request;

class BankController extends Controller
{
    function index()
    {
        // get all bank data
        $bank = Bank::query()->get();
        return BaseResponse::success($bank);
    }

    function show($id)
    {
        // get bank data with certain id
        $bank = Bank::query()->where('id', $id)->first();
        if (!$bank) BaseResponse::error('Data was not found', 404);
        return BaseResponse::success($bank);
    }

    function store(Request $request)
    {
        // validating request (form data)
        try {
            $validated = $request->validate([

                'name' => ['required', 'max:50', 'min:3'],
                'loaning_percentage' => ['required', 'numeric', 'min:1', 'max:100'],
                'max_age' => ['required', 'min:1', 'max:150',  'numeric'],
                'min_age' => ['required', 'min:1', 'max:150'],
                'marital_status' => ['required', 'numeric', 'digits_between:0,2'],
                'nationality' => ['required', 'numeric', 'digits_between:0,2'],
                'employment' => ['required', 'numeric', 'digits_between:0,2'],
            ]);
            // return error based on validation error
        } catch (\Illuminate\Validation\ValidationException $validate) {
            return BaseResponse::error($validate->validator->errors()->all());
        }

        $bank = Bank::create($validated);
        return BaseResponse::success($bank, 'Data was successfully created');
    }

    function update(Request $request, $id)
    {
        $bank = Bank::query()->where('id', $id)->first();
        if (!$bank) BaseResponse::error('Data was not found', 404);
        // validating request (form data)
        try {
            $validated = $request->validate([
                'name' => [ 'max:50', 'min:3'],
                'loaning_percentage' => [ 'numeric', 'min:1', 'max:100'],
                'max_age' => [ 'min:1', 'max:150',  'numeric'],
                'min_age' => [ 'min:1', 'max:150', 'numeric'],
                'marital_status' => [ 'numeric', 'digits_between:0,2'],
                'nationality' => [ 'numeric', 'digits_between:0,2'],
                'employment' => [ 'numeric', 'digits_between:0,2'],
            ]);
            // return error based on validation error
        } catch (\Illuminate\Validation\ValidationException $validate) {
            return BaseResponse::error($validate->validator->errors()->all());
        }

        $bank->fill($validated);
        $bank->save();
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
