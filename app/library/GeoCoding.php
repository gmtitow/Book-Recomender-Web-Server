<?php

namespace App\Libs;


class GeoCoding
{
    /*
    * Given longitude and latitude in North America, return the address using The Google Geocoding API V3
    *
    */

    public static function Get_Address_From_Google_Maps($lat, $lon)
    {

        $url = "http://maps.googleapis.com/maps/api/geocode/json?latlng=$lat,$lon&sensor=false";

        $data = @file_get_contents($url);
        $jsondata = json_decode($data, true);

        if (!self::check_status($jsondata)) return array();

        $address = array(
            'country' => self::google_getCountry($jsondata),
            'province' => self::google_getProvince($jsondata),
            'city' => self::google_getCity($jsondata),
            'street' => self::google_getStreet($jsondata),
            'postal_code' => self::google_getPostalCode($jsondata),
            'country_code' => self::google_getCountryCode($jsondata),
            'formatted_address' => self::google_getAddress($jsondata),
        );

        return $address;
    }

    public static function Get_City_From_Google_Maps($lat, $lon)
    {
        $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=$lat,$lon&language=ru&key=".GOOGLE_API_KEY;

        $data = @file_get_contents($url);

        $jsondata = json_decode($data, true);

        if (!self::check_status($jsondata)) return null;

        return self::google_getCity($jsondata);
    }

    /*
    * Check if the json data from Google Geo is valid
    */

    public static function check_status($jsondata)
    {
        if ($jsondata["status"] == "OK") return true;
        return false;
    }

    /*
    * Given Google Geocode json, return the value in the specified element of the array
    */

    public static function google_getCountry($jsondata)
    {
        return self::Find_Long_Name_Given_Type("country", $jsondata["results"][0]["address_components"]);
    }

    public static function google_getProvince($jsondata)
    {
        return self::Find_Long_Name_Given_Type("administrative_area_level_1", $jsondata["results"][0]["address_components"], true);
    }

    public static function google_getCity($jsondata)
    {
        return self::Find_Long_Name_Given_Type("locality", $jsondata["results"][0]["address_components"]);
    }

    public static function google_getStreet($jsondata)
    {
        return self::Find_Long_Name_Given_Type("street_number", $jsondata["results"][0]["address_components"]) . ' ' . Find_Long_Name_Given_Type("route", $jsondata["results"][0]["address_components"]);
    }

    public static function google_getPostalCode($jsondata)
    {
        return self::Find_Long_Name_Given_Type("postal_code", $jsondata["results"][0]["address_components"]);
    }

    public static function google_getCountryCode($jsondata)
    {
        return self::Find_Long_Name_Given_Type("country", $jsondata["results"][0]["address_components"], true);
    }

    public static function google_getAddress($jsondata)
    {
        return $jsondata["results"][0]["formatted_address"];
    }

    /*
    * Searching in Google Geo json, return the long name given the type.
    * (If short_name is true, return short name)
    */

    public static function Find_Long_Name_Given_Type($type, $array, $short_name = false)
    {
        foreach ($array as $value) {
            if (in_array($type, $value["types"])) {
                if ($short_name)
                    return $value["short_name"];
                return $value["long_name"];
            }
        }
    }

}