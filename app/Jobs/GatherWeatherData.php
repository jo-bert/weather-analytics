<?php

namespace App\Jobs;

use App\Models\HourlyForecast;
use App\Models\Location;
use DateTime;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GatherWeatherData implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $city;

    public function __construct($city)
    {
        $this->city = $city;
    }

    public function handle(): void
    {
        Log::channel('job')->debug(message: "Gather Data for city {$this->city}");
        $weatherApiKey = env('WEATHER_API_KEY', '');
        for ($j = -7; $j <= 2; $j++) {
            $date = now()->addDays($j)->format('Y-m-d');
            $response = Http::get("http://api.weatherapi.com/v1/history.json?key={$weatherApiKey}&q={$this->city}&dt={$date}");
            if ($response->ok()) {
                $jsonResponse = $response->json();
                $locationData = $jsonResponse['location'];
                $storedLocation = Location::where('name', $locationData['name']);
                if ($storedLocation->exists()) {
                    $this->generateHourlyForecast($jsonResponse, $storedLocation->first());
                } else {
                    $location = Location::create([
                        'name' => $locationData['name'],
                        'region' => $locationData['region'],
                        'country' => $locationData['country'],
                        'lat' => $locationData['lat'],
                        'lon' => $locationData['lon'],
                        'tz_id' => $locationData['tz_id'],
                    ]);
                    Log::info("Location created: {$locationData['name']}");
                    $this->generateHourlyForecast($jsonResponse, $location);
                }
            } else {
                Log::info("Error code found when trying to query {$date} for history {$this->city}");
                Log::error($response->body());
            }
        }

        for ($k = 1; $k <= 10; $k++) {
            $date = now()->addDays($k)->format('Y-m-d');
            $response = Http::get("http://api.weatherapi.com/v1/forecast.json?key={$weatherApiKey}&q={$this->city}&days={$k}&aqi=no&alerts=no");
            if ($response->ok()) {
                $jsonResponse = $response->json();
                $locationData = $jsonResponse['location'];
                $storedLocation = Location::where('name', $locationData['name']);
                if ($storedLocation->exists()) {
                    $this->generateHourlyForecast($jsonResponse, $storedLocation->first());
                } else {
                    $location = Location::create([
                        'name' => $locationData['name'],
                        'region' => $locationData['region'],
                        'country' => $locationData['country'],
                        'lat' => $locationData['lat'],
                        'lon' => $locationData['lon'],
                        'tz_id' => $locationData['tz_id'],
                    ]);
                    Log::info("Location created: {$locationData['name']}");
                    $this->generateHourlyForecast($jsonResponse, $location);
                }
            } else {
                Log::info("Error code found when trying to query {$date} for forecast {$this->city}");
                Log::error($response->body());
            }
        }
    }

    public function generateHourlyForecast($jsonResponse, $location)
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
                $time =  new DateTime($hourData['time_epoch']);
                Log::info("Hourly forecast created/updated for location: {$location->name} in {$time->format('Y-m-d H:i:s')}");
            }
        }
    }
}
