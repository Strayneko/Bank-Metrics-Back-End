<?php

namespace App\Http\Controllers;

use App\Http\Response\BaseResponse;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{

    //untuk mengambil list user
    function index(){
        $user = User::with(['user_profile'])->where('role_id', 1)->get();
        return BaseResponse::success($user);
    }

    function index_profile(){
        $profile = UserProfile::query()->get();
        return BaseResponse::success($profile);
    }

    function show($id){
        $user = User::with(['user_profile', 'country'])->where('role_id', 1)->where('id',$id)->first();
        if (!$user) return BaseResponse::error('Data was not found',404);
        return BaseResponse::success($user);
    }

    //menambahkan profile user
    // jangan lupa kasih input type hidden di formnya ya
    function store_profile(Request $request){

        try{

            $validated = $request->validate([
                'country_id' => 'required',
                'user_id' =>'required',
                'marital_status' =>'required',
                'dob' => 'required',
                'employement' => 'required',
                'photo' => 'required|file|image|mimetypes:image/jpg,image/png,image/jpeg'
            ]);
        }
        catch (\Illuminate\Validation\ValidationException $validate){
            return BaseResponse::error('Wrong data format');
           }



        if ($request->file('photo'))$path = $request->file('photo')->store('profile', 'public');
        // $validated['country_id'] = 1;
        $validated['photo'] = $path;
        $profile = UserProfile::create($validated);

        return BaseResponse::success($profile, 'Data was successfully created');

    }

    //mengubah profile user
    function edit_profile(Request $req, $id)
    {
        $profile = UserProfile::query()->where('id', $id)->first();

        if (!$profile) BaseResponse::error('Data was not found', 404);

        try{
            $validated = $req->validate([
                'user_id' =>'required',
                'photo' => 'file|image|mimetypes:image/jpg,image/png,image/jpeg'
            ]);
        }
        catch (\Illuminate\Validation\ValidationException $validate){
            return BaseResponse::error('Wrong data format');
        }

        $file = $req->file('photo');

        if(!$file){
            $profile->fill($validated);
            $profile->save();
            return BaseResponse::success($profile, 'Data was successfully updated');
        }

        Storage::disk('public')->delete($profile->photo);
        $validated['photo']->store('profile', 'public');
        $profile->fill($validated);

        return BaseResponse::success($profile, 'Data was successfully updated');
    }

}
