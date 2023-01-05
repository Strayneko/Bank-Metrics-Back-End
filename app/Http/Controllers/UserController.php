<?php

namespace App\Http\Controllers;

use App\Helpers\Countries;
use App\Http\Response\BaseResponse;
use App\Models\Country;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{

    //untuk mengambil list user
    function index()
    {
        $user = User::with(['user_profile'])->where('role_id', 1)->get();
        return BaseResponse::success($user);
    }

    function index_profile()
    {
        $profile = UserProfile::query()->get();
        return BaseResponse::success($profile);
    }

    function show($id)
    {
        $user = User::with(['user_profile', 'country'])->where('role_id', 1)->where('id', $id)->first();
        if (!$user) return BaseResponse::error('Data was not found', 404);
        return BaseResponse::success($user);
    }

    //menambahkan profile user
    // jangan lupa kasih input type hidden di formnya ya
    function store_profile(Request $request)
    {

        // check if user is inserted theri profile
        $profile = UserProfile::where('user_id', $request->input('user_id'))->first();
        if ($profile) return BaseResponse::error("You've already submited user profile!");
        try {

            $validated = $request->validate([
                'country_id' => 'required|numeric|min:1',
                'user_id' => 'required',
                'marital_status' => 'required',
                'dob' => 'required',
                'employement' => 'required',
                'photo' => 'required|file|image|mimetypes:image/jpg,image/png,image/jpeg'
            ]);
        } catch (\Illuminate\Validation\ValidationException $validate) {
            return BaseResponse::error('Wrong data format');
        }



        if ($request->file('photo')) $photo = $request->file('photo')->store('profile', 'public');
        // check if country id is available in database
        $country = Country::find($request->input('country_id'));
        if (!$country) {
            // get country data by specific id
            $countries = Countries::getCountries();
            $countries = $countries->where('id', $request->input('country_id'))->first();
            // insert country data to countries table
            $new_country = Country::create([
                'country_name' => $countries['name'],
                'country_id' => $countries['id'],
            ]);
        }
        $validated['country_id'] = $request->input('country_id');
        $validated['photo'] = $photo;
        $profile = UserProfile::create($validated);

        return BaseResponse::success($profile, 'Data was successfully created');
    }

    //mengubah profile user
    function edit_profile(Request $req, $id)
    {
        $profile = UserProfile::query()->where('id', $id)->first();

        if (!$profile) BaseResponse::error('Data was not found', 404);

        try {
            $validated = $req->validate([
                'user_id' =>'required',
                'photo' => 'file|image|mimetypes:image/jpg,image/png,image/jpeg'
            ]);
        } catch (\Illuminate\Validation\ValidationException $validate) {
            return BaseResponse::error('Wrong data format');
        }

        $file = $req->file('photo');

        if (!$file) {
            $profile->fill($validated);
            $profile->save();
            return BaseResponse::success($profile, 'Data was successfully updated');
        }

        Storage::disk('public')->delete($profile->photo);
        $validated['photo'] = $req->file('photo')->store('profile', 'public');
        $profile->fill($validated);

        return BaseResponse::success($profile, 'Data was successfully updated');
    }
}
