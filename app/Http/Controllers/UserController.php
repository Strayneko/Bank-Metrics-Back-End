<?php

namespace App\Http\Controllers;

use App\Helpers\Countries;
use App\Http\Response\BaseResponse;
use App\Models\Country;
use App\Models\User;
use App\Models\Role;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UserController extends Controller
{

    //untuk mengambil list user
    function index()
    {
        $user = User::with(['user_profile', 'user_profile.country'])->where('role_id', 1)->get();
        return BaseResponse::success($user);
    }

    function index_profile()
    {
        $profile = UserProfile::query()->get();
        return BaseResponse::success($profile);
    }

    function show()
    {
        // get current authenticated user
        $user = Auth::user();
        // get current authenticated user profile
        $user_profile = UserProfile::with(['country'])->where('user_id', $user->id)->first();
        $role = Role::where('id', $user->role_id)->first();
        $user['role'] = $role;
        $user['profile'] = null;
        if ($user_profile) $user['profile'] = $user_profile;
        if (!$user) return BaseResponse::error('Data was not found', 404);
        return BaseResponse::success($user);
    }

    //menambahkan profile user
    // jangan lupa kasih input type hidden di formnya ya
    function store_profile(Request $request)
    {
        // get authenticated user
        $user = Auth::user();

        // check if user is inserted theri profile
        $profile = UserProfile::where('user_id', $user->id)->first();
        $rules = $profile ? [
            'address' => 'min:1',
            'country_id' => 'numeric|min:1',
            'marital_status' => 'min:0',
            'dob' => 'date',
            'employement' => 'min:0|numeric',
            'gender' => 'digits_between:0,1|numeric',
            'photo' => 'file|image|mimetypes:image/jpg,image/png,image/jpeg'
        ] : [
            'address' => 'required',
            'country_id' => 'required|numeric|min:1',
            'marital_status' => 'required',
            'dob' => 'required',
            'employement' => 'required',
            'gender' => 'required|digits_between:0,1|numeric',
            'photo' => 'required|file|image|mimetypes:image/jpg,image/png,image/jpeg'
        ];
        try {

            $validated = $request->validate($rules);
        } catch (\Illuminate\Validation\ValidationException $validate) {
            return BaseResponse::error($validate->validator->errors()->all());
        }



        if ($request->file('photo')) {
            $photo = $request->getSchemeAndHttpHost() . '/storage/' . $request->file('photo')->store('profile', 'public');
            $validated['photo'] = $photo;
        }
        // check if country id is available in database
        $country_id = $request->input('country_id') ?? 0;
        $country = Country::find($country_id);
        if (!$country) {
            // get country data by specific id
            $countries = Countries::getCountries();
            $countries = $countries->where('id', $request->input('country_id'))->first();
            if ($countries) {
                // insert country data to countries table
                Country::create([
                    'country_name' => $countries['name'],
                    'id' => $countries['id'],
                ]);
                $validated['country_id'] = $request->input('country_id');
            }
        }

        $validated['user_id'] = $user->id;
        // if user already submitted profile
        if ($profile) {

            // delete profile image
            $imgName = Str::of($profile->photo)->remove($request->getSchemeAndHttpHost() . '/storage/');
            Storage::disk('public')->delete($imgName);
            // update user profile
            $profile->update($validated);
            return BaseResponse::success($profile, 'Data was successfully Updated');
        }

        $profile = UserProfile::create($validated);


        return BaseResponse::success($profile, 'Data was successfully created');
    }
}
