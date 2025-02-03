<?php

namespace App\Http\Controllers;

use App\Jobs\GatherWeatherData;
use App\Models\HourlyForecast;
use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB as FacadesDB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class LocationController extends Controller
{

    private ?Location $nearestLocation;
    private string $weatherApiKey;

    public function __construct()
    {
        $this->weatherApiKey = env('WEATHER_API_KEY', '');
    }

    public function index()
    {
        return Inertia::render('Locations/Index', [
            'nearestLocation' => null,
            'locations' => Cache::remember('users', now()->addMinute(), function () {
                return Location::all();
            }),
            'countries' => Cache::remember('countries', now()->addMinute(), function () {
                return Location::all()->pluck('country')->unique()->values()->toArray();
            }),
        ]);
    }

    public function submit(Request $request)
    {

        $lat = $request['lat'];
        $long = $request['long'];
        $location =  $request['location'];
        if ($lat !== 0 || $long !== 0) {
            $nearestLocationArray = FacadesDB::select('SELECT * FROM find_nearest_location(?, ?)', [$lat, $long]);
            $this->nearestLocation = !empty($nearestLocationArray) ? Location::find($nearestLocationArray[0]->id) : null;
        } else if ($location !== '') {
            $this->nearestLocation = Location::where('name', $location)->first();
            Log::info($location);
        } else {
            return redirect()->route('locations.index');
        }

        if (($lat && $long && $this->nearestLocation->distance <= 50) || ($location && $this->nearestLocation)) {
            $latestForecast = HourlyForecast::where('location_id', $this->nearestLocation->id)->orderBy('time_epoch', 'desc')->first();
            $latestUpdatedTime = Carbon::parse($latestForecast->updated_at);
            $currentTime = Carbon::now();
            if ($latestUpdatedTime->diffInHours($currentTime) >= 1) {
                Log::info("Hourly forecast for {$this->nearestLocation->name} is outdated. Updating one now and getting from the API instead");
                GatherWeatherData::dispatch($this->nearestLocation->name);
                return Inertia::render('Locations/Index', [
                    'nearestLocation' => $this->nearestLocation->name,
                    'locations' => Cache::remember('users', now()->addMinute(), function () {
                        return Location::all();
                    }),
                    'countries' => Cache::remember('countries', now()->addMinute(), function () {
                        return Location::all()->pluck('country')->unique()->values()->toArray();
                    }),
                    'country' => $this->nearestLocation->country,
                    'location' => $this->nearestLocation->name,
                    'weather' => $this->getDailyForecast($this->nearestLocation->name)
                ]);
            } else {
                // HourlyForecast::where('location_id', $this->nearestLocation->id)
                // ->whereBetween('time_epoch', [Carbon::now()->subDays(7)->timestamp, Carbon::now()->addDays(7)->timestamp])->get()
                $weatherData = collect([]);
                for ($j = -6; $j <= 7; $j++) {
                    $weatherData = $weatherData->merge(FacadesDB::select('SELECT * FROM get_daily_stat(?, ?, ?)', [Carbon::now()->addDays($j - 1)->getTimestamp(), Carbon::now()->addDays($j)->getTimestamp(), $this->nearestLocation->id]));
                }
                return Inertia::render('Locations/Index', [
                    'nearestLocation' => $this->nearestLocation->name,
                    'locations' => Cache::remember('users', now()->addMinute(), function () {
                        return Location::all();
                    }),
                    'countries' => Cache::remember('countries', now()->addMinute(), function () {
                        return Location::all()->pluck(value: 'country')->unique()->values()->toArray();
                    }),
                    'country' => $this->nearestLocation->country,
                    'location' => $this->nearestLocation->name,
                    'weather' => $weatherData
                ]);
            }
        } else {
            $response = Http::get("http://api.weatherapi.com/v1/search.json?key={$this->weatherApiKey}&q={$lat}, {$long}");
            GatherWeatherData::dispatchSync($response->json()[0]['name']);
            return Inertia::render('Locations/Index', [
                'nearestLocation' => 'Getting the weather of past 7 days and next 2 days for few minutes. Please wait',
                'locations' => Cache::remember('users', now()->addMinute(), function () {
                    return Location::all()->filter(function ($location) {
                        return $location->name !== 'Singapore';
                    })->values()->toArray();
                }),
                'countries' => Cache::remember('countries', now()->addMinute(), function () {
                    return Location::all()->pluck('country')->unique()->values()->toArray();
                }),
                'country' => $response->json()[0]['country'],
                'location' => $response->json()[0]['name']
            ]);
        }
    }

    private function getDailyForecast($city)
    {
        $aggregratedData = collect([]);
        $keys = ['condition_text', 'condition_icon', 'condition_code'];
        for ($j = -7; $j <= 2; $j++) {
            $date = now()->addDays($j)->format('Y-m-d');
            $response = Http::get("http://api.weatherapi.com/v1/history.json?key={$this->weatherApiKey}&q={$city}&dt={$date}");
            if (!$response->ok()) {
                break;
            }
            $todayWeather = $response->json()['forecast']['forecastday'][0]['day'];
            $values = [$todayWeather['condition']['text'], $todayWeather['condition']['icon'], $todayWeather['condition']['code']];
            for ($i = 0; $i < count($keys); $i++) {
                $todayWeather[$keys[$i]] = $values[$i];
            }
            $todayWeather['condition'] = null;
            $aggregratedData = $aggregratedData->add($todayWeather);
        }
        $response = Http::get("http://api.weatherapi.com/v1/forecast.json?key={$this->weatherApiKey}&q={$city}&days=7");
        foreach ($response['forecast']['forecastday'] as $forecastDay) {
            $values = [$forecastDay['day']['condition']['text'], $forecastDay['day']['condition']['icon'], $forecastDay['day']['condition']['code']];
            for ($i = 0; $i < count($keys); $i++) {
                $forecastDay[$keys[$i]] = $values[$i];
            }
            $forecastDay['condition'] = null;
            $aggregratedData = $aggregratedData->add($todayWeather);
        }
        return $aggregratedData;
    }
}
