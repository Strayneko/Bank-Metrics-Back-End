<?php

namespace App\Http\Controllers;

use App\Helpers\Countries;
use App\Http\Response\BaseResponse;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    // show country names
    public function index()
    {
        // get countries data
        $countries = Countries::getCountries();
        return BaseResponse::success($countries);
    }
}
