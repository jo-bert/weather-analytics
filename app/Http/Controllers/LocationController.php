<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        // Retrieve a specific query parameter
        $latParam = $request->query('lat');
        $longParam = $request->query('long');
        $cityKeywordParam = $request->query('city');

        $allParams = $request->query();

        if ($cityKeywordParam) {
            $locations = Location::where('name', 'like', $cityKeywordParam)->get();
        } else {
            $locations = Location::all();
        }
        return $locations->toJson();
    }
}
