<?php

namespace App\Http\Controllers;

use App\Http\Response\BaseResponse;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    // show country names
    public function index()
    {
        // load json file from public folder
        $json = file_get_contents('json/country_names.json');
        // convert json to php array
        $json = json_decode($json);
        // convert array to laravel collection
        $json = collect($json);
        // mapping country data
        $json = $json->map(function ($country, $idx) {
            return [
                'id' => $idx + 1,
                'name' => $country
            ];
        });
        return BaseResponse::success($json);
    }
}
