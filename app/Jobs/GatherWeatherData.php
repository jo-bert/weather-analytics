<?php

namespace App\Jobs;

use App\Models\HourlyForecast;
use App\Models\Location;
use App\Repositories\HourlyForecastRepository;
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


    private $maxAttempts;
    protected $city;
    private HourlyForecastRepository $hourlyForecastRepository;
    public function __construct($city)
    {
        $this->maxAttempts = 3;
        $this->city = $city;
        $this->hourlyForecastRepository = new HourlyForecastRepository();
    }

    public function handle(): void
    {
        Log::channel('job')->debug(message: "Gather Data for city {$this->city}");
        $weatherApiKey = env('WEATHER_API_KEY', '');
        for ($j = -7; $j <= 2; $j++) {
            $date = now()->addDays($j)->format('Y-m-d');
            $attempts = 0;
            do {
                $response = Http::get("http://api.weatherapi.com/v1/history.json?key={$weatherApiKey}&q={$this->city}&dt={$date}");
                $attempts++;
            } while (!$response->ok() && $attempts < $this->maxAttempts);
            if ($response->ok()) {
                $jsonResponse = $response->json();
                $locationData = $jsonResponse['location'];
                $storedLocation = Location::where('name', $locationData['name']);
                if ($storedLocation->exists()) {
                    $this->hourlyForecastRepository->save($jsonResponse, $storedLocation->first());
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
                    $this->hourlyForecastRepository->save($jsonResponse, $location);
                }
            } else {
                Log::info("Error code found when trying to query {$date} for history {$this->city}");
                Log::error($response->body());
            }
        }

        for ($k = 1; $k <= 10; $k++) {
            $date = now()->addDays($k)->format('Y-m-d');
            $attempts = 0;
            do {
                $response = Http::get("http://api.weatherapi.com/v1/forecast.json?key={$weatherApiKey}&q={$this->city}&days={$k}&aqi=no&alerts=no");
                $attempts++;
            } while (!$response->ok() && $attempts < $this->maxAttempts);
            if ($response->ok()) {
                $jsonResponse = $response->json();
                $locationData = $jsonResponse['location'];
                $storedLocation = Location::where('name', $locationData['name']);
                if ($storedLocation->exists()) {
                    $this->hourlyForecastRepository->save($jsonResponse, $storedLocation->first());
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
                    $this->hourlyForecastRepository->save($jsonResponse, $location);
                }
            } else {
                Log::info("Error code found when trying to query {$date} for forecast {$this->city}");
                Log::error($response->body());
            }
        }
    }
}
