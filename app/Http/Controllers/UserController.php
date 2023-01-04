<?php

namespace App\Http\Controllers;

use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    function insert_user_profile(Request $request){

        try{

            $validated = $request->validate([
                // 'country_id' => 'required',
                'user_id' =>'required',
                'marital_status' =>'required',
                'dob' => 'required',
                'employment' => 'required',
                'photo' => 'required|file|image|mimetypes:image/jpg,image/png,image/jpeg'
            ]);
        }
        catch (\Illuminate\Validation\ValidationException $validate){
            return response()->json([
                'status' => false,
                'message' => $validate->validator->errors()
            ], 403);
           }



        if ($request->file('photo'))$request->file('photo')->store('profile', 'public');
        $validated['country_id'] = 1;

        $profile = UserProfile::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'data masuk',
            'data' => $profile
        ]);
    }

    function edit_user_profile(Request $req, $id)
    {
        $profile = UserProfile::query()->where('id', $id)->first();
        try{
            $validated = $req->validate([
                // 'id_country' => '',
                'marital_status' =>'',
                'dob' => '',
                'employment' => '',
                'photo' => 'file|image|mimetypes:image/jpg,image/png,image/jpeg'
            ]);
        }
        catch (\Illuminate\Validation\ValidationException $validate){
            return response()->json([
                'status' => false,
                'message' => $validate->validator->errors()
            ], 403);
        }

        $file = $req->file('photo');

        if(!$file){
            $profile->fill($validated);
            $profile->save();
            return response()->json([
                'status' => true,
                'message' => 'data diubah',
                'data' => $profile
            ]);
        }

        Storage::disk('public')->delete($$profile->photo);
        $validated['photo']->store('profile', 'public');
        $profile->fill($validated);

        return response()->json([
            'status' => true,
            'message' => 'data diubah',
            'data' => $profile
        ]);
    }
}
