<?php

namespace App\Helpers;

class Countries
{
    public static function getCountries()
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
        return $json;
    }
}
