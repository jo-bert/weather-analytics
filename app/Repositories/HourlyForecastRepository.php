<?php

namespace App\Repositories;

use App\Models\HourlyForecast;
use DateTime;
use Illuminate\Support\Facades\Log;

class HourlyForecastRepository
{

  public function save($jsonResponse, $location)
  {
    foreach ($jsonResponse['forecast']['forecastday'] as $forecastDay) {
      foreach ($forecastDay['hour'] as $hourData) {
        HourlyForecast::updateOrCreate(
          [
            'location_id' => $location->id,
            'time_epoch' => $hourData['time_epoch']
          ],
          [
            'time' => $hourData['time'],
            'temp_c' => $hourData['temp_c'],
            'temp_f' => $hourData['temp_f'],
            'is_day' => $hourData['is_day'],
            'condition_text' => $hourData['condition']['text'],
            'condition_icon' => $hourData['condition']['icon'],
            'condition_code' => $hourData['condition']['code'],
            'wind_mph' => $hourData['wind_mph'],
            'wind_kph' => $hourData['wind_kph'],
            'wind_degree' => $hourData['wind_degree'],
            'wind_dir' => $hourData['wind_dir'],
            'pressure_mb' => $hourData['pressure_mb'],
            'pressure_in' => $hourData['pressure_in'],
            'precip_mm' => $hourData['precip_mm'],
            'precip_in' => $hourData['precip_in'],
            'snow_cm' => $hourData['snow_cm'],
            'humidity' => $hourData['humidity'],
            'cloud' => $hourData['cloud'],
            'feelslike_c' => $hourData['feelslike_c'],
            'feelslike_f' => $hourData['feelslike_f'],
            'windchill_c' => $hourData['windchill_c'],
            'windchill_f' => $hourData['windchill_f'],
            'heatindex_c' => $hourData['heatindex_c'],
            'heatindex_f' => $hourData['heatindex_f'],
            'dewpoint_c' => $hourData['dewpoint_c'],
            'dewpoint_f' => $hourData['dewpoint_f'],
            'will_it_rain' => $hourData['will_it_rain'],
            'chance_of_rain' => $hourData['chance_of_rain'],
            'will_it_snow' => $hourData['will_it_snow'],
            'chance_of_snow' => $hourData['chance_of_snow'],
            'vis_km' => $hourData['vis_km'],
            'vis_miles' => $hourData['vis_miles'],
            'gust_mph' => $hourData['gust_mph'],
            'gust_kph' => $hourData['gust_kph'],
            'uv' => $hourData['uv'],
          ]
        );
        $time = (new DateTime())->setTimestamp($hourData['time_epoch']);
        if (app()->runningInConsole()) {
          print("Hourly forecast created/updated for location: {$location->name} in {$time->format('Y-m-d H:i:s')}" . PHP_EOL);
        }
        Log::info("Hourly forecast created/updated for location: {$location->name} in {$time->format('Y-m-d H:i:s')}");
      }
    }
  }
}
