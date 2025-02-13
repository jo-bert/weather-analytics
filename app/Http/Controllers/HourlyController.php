<?php

namespace App\Http\Controllers;

use App\Models\HourlyForecast;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HourlyController extends Controller
{
  // GET: Retrieve all hourly forecasts
  public function index(Request $request)
  {
    return Cache::remember('hourly_forecasts', now()->addHour(), function () {
      return HourlyForecast::all();
    });
  }

  // POST: Create a new hourly forecast
  public function store(Request $request)
  {
    $validatedData = $request->validate([
      'location_id' => 'required|integer',
      'time_epoch' => 'required|integer',
      'time' => 'required|date',
      'temp_c' => 'numeric',
      'temp_f' => 'numeric',
      'is_day' => 'integer',
      'condition_text' => 'string',
      'condition_icon' => 'string',
      'condition_code' => 'integer',
      'wind_mph' => 'numeric',
      'wind_kph' => 'numeric',
      'wind_degree' => 'integer',
      'wind_dir' => 'string',
      'pressure_mb' => 'numeric',
      'pressure_in' => 'numeric',
      'precip_mm' => 'numeric',
      'precip_in' => 'numeric',
      'snow_cm' => 'numeric',
      'humidity' => 'numeric',
      'cloud' => 'numeric',
      'feelslike_c' => 'numeric',
      'feelslike_f' => 'numeric',
      'windchill_c' => 'numeric',
      'windchill_f' => 'numeric',
      'heatindex_c' => 'numeric',
      'heatindex_f' => 'numeric',
      'dewpoint_c' => 'numeric',
      'dewpoint_f' => 'numeric',
      'will_it_rain' => 'integer',
      'chance_of_rain' => 'integer',
      'will_it_snow' => 'integer',
      'chance_of_snow' => 'integer',
      'vis_km' => 'numeric',
      'vis_miles' => 'numeric',
      'gust_mph' => 'numeric',
      'gust_kph' => 'numeric',
      'uv' => 'numeric',
    ]);

    $forecast = HourlyForecast::create($validatedData);
    return response()->json($forecast, 201);
  }

  // GET: Retrieve a specific hourly forecast by ID
  public function show($id)
  {
    $forecast = HourlyForecast::findOrFail($id);
    return response()->json($forecast);
  }

  // PUT: Update a specific hourly forecast by ID
  public function update(Request $request, $id)
  {
    $validatedData = $request->validate([
      'location_id' => 'integer',
      'time_epoch' => 'integer',
      'time' => 'date',
      'temp_c' => 'numeric',
      'temp_f' => 'numeric',
      'is_day' => 'integer',
      'condition_text' => 'string',
      'condition_icon' => 'string',
      'condition_code' => 'integer',
      'wind_mph' => 'numeric',
      'wind_kph' => 'numeric',
      'wind_degree' => 'integer',
      'wind_dir' => 'string',
      'pressure_mb' => 'numeric',
      'pressure_in' => 'numeric',
      'precip_mm' => 'numeric',
      'precip_in' => 'numeric',
      'snow_cm' => 'numeric',
      'humidity' => 'numeric',
      'cloud' => 'numeric',
      'feelslike_c' => 'numeric',
      'feelslike_f' => 'numeric',
      'windchill_c' => 'numeric',
      'windchill_f' => 'numeric',
      'heatindex_c' => 'numeric',
      'heatindex_f' => 'numeric',
      'dewpoint_c' => 'numeric',
      'dewpoint_f' => 'numeric',
      'will_it_rain' => 'integer',
      'chance_of_rain' => 'integer',
      'will_it_snow' => 'integer',
      'chance_of_snow' => 'integer',
      'vis_km' => 'numeric',
      'vis_miles' => 'numeric',
      'gust_mph' => 'numeric',
      'gust_kph' => 'numeric',
      'uv' => 'numeric',
    ]);

    $forecast = HourlyForecast::findOrFail($id);
    $forecast->update($validatedData);
    return response()->json($forecast);
  }

  // DELETE: Delete a specific hourly forecast by ID
  public function destroy($id)
  {
    $forecast = HourlyForecast::findOrFail($id);
    $forecast->delete();
    return response()->json(null, 204);
  }

  public function getByRange(Request $request)
  {
    try {
      $validatedData = $request->validate([
        'location_id' => 'required|integer',
        'start_time' => 'required|numeric',
        'end_time' => 'required|numeric',
      ]);
      $forecasts = HourlyForecast::where('location_id', $validatedData['location_id'])
        ->whereBetween('time_epoch', [$validatedData['start_time'], $validatedData['end_time']])
        ->select(['time', 'temp_c', 'temp_f', 'precip_mm', 'precip_in'])
        ->orderBy('time_epoch')
        ->get();
      return response()->json($forecasts);
    } catch (Exception $e) {
      return response()->json(['error' => $e->getMessage()], 400);
    }
  }
}
