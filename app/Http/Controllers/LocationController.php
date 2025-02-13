<?php

namespace App\Http\Controllers;

use App\Jobs\GatherWeatherData;
use App\Models\Alert;
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
    private int $maxAttempts;


    public function __construct()
    {
        $this->weatherApiKey = env('WEATHER_API_KEY', '');
        $this->maxAttempts = 3;
    }

    public function index()
    {
        return Inertia::render('Locations/Index', [
            'nearestLocation' => null,
            'locations' => Cache::remember('locations', now()->addHour(), function () {
                return Location::select(['id', 'name', 'country'])->get();
            }),
            'countries' => Cache::remember('countries', now()->addHour(), function () {
                return Location::all()->pluck('country')->unique()->values()->toArray();
            }),
            'message' => '',
            'messageType' => ''
        ]);
    }

    public function submit(Request $request)
    {
        $lat = $request['lat'];
        $long = $request['long'];
        $location = $request['location'];
        if ($lat !== 0 || $long !== 0) {
            $nearestLocationArray = FacadesDB::select('SELECT * FROM find_nearest_location(?, ?)', [$lat, $long]);
            $this->nearestLocation = !empty($nearestLocationArray) ? Location::find($nearestLocationArray[0]->id) : null;
        } else if ($location !== null) {
            $this->nearestLocation = Location::where('name', $location)->first();
        } else {
            return Inertia::render('Locations/Index', [
                'nearestLocation' => null,
                'locations' => Cache::remember('locations', now()->addHour(), function () {
                    return Location::select(['id', 'name', 'country'])->get();
                }),
                'countries' => Cache::remember('countries', now()->addHour(), function () {
                    return Location::all()->pluck('country')->unique()->values()->toArray();
                }),
                'country' => '',
                'location' => '',
                'messageType' => 'error',
                'message' => 'Please select a location'
            ]);
        }

        $currentWeatherResponse = null;
        $attempts = 0;
        $city = $this->nearestLocation ? $this->nearestLocation->name : $location;
        do {
            $currentWeatherResponse = Http::get("http://api.weatherapi.com/v1/current.json?key={$this->weatherApiKey}&q={$city}&aqi=no");
            $attempts++;
        } while (!$currentWeatherResponse->ok() && $attempts < $this->maxAttempts);

        $filteredAlerts = Alert::where('location_id', $this->nearestLocation->id)
            ->where('triggered', false)
            ->where('paused', false)
            ->where('expiry', '>', now())
            ->select([
                'id',
                'parameter',
                'condition',
                'value',
                'minValue',
                'maxValue',
                'triggered',
                'triggered_at'
            ])
            ->get();

        $triggeredAlert = $filteredAlerts->filter(function ($alert) use ($currentWeatherResponse) {
            if ($alert->condition === 'less') {
                return $currentWeatherResponse['current'][$alert->parameter] < $alert->value;
            } else if ($alert->condition === 'more') {
                return $currentWeatherResponse['current'][$alert->parameter] > $alert->value;
            } else if ($alert->condition === 'equal') {
                return $currentWeatherResponse['current'][$alert->parameter] == $alert->value;
            } else if ($alert->condition === 'between') {
                return $currentWeatherResponse['current'][$alert->parameter] >= $alert->minValue &&  $currentWeatherResponse['current'][$alert->parameter] <= $alert->maxValue;
            }
        });
        if ($triggeredAlert->count() > 0) {
            $triggeredAlert->each(function ($alert) {
                $dbAlert = Alert::find($alert->id);
                $dbAlert->triggered = true;
                $dbAlert->triggered_at = now();
                $dbAlert->save();
            });
        }

        if (($lat && $long && $this->nearestLocation->distance <= 50) || ($location && $this->nearestLocation)) {
            $latestForecast = HourlyForecast::where('location_id', $this->nearestLocation->id)->orderBy('time_epoch', 'desc')->first();
            $latestUpdatedTime = Carbon::parse($latestForecast->updated_at);
            $currentTime = Carbon::now();
            if ($latestUpdatedTime->diffInHours($currentTime) >= 1) {
                // Log::info("Hourly forecast for {$this->nearestLocation->name} is outdated. Updating one now and getting from the API instead");
                GatherWeatherData::dispatch($this->nearestLocation->name);
                $weather = $this->getDailyForecast($this->nearestLocation->name);
                return Inertia::render('Locations/Index', [
                    'messageType' => 'warning',
                    'message' => 'Data is from a third-party source and may vary slightly',
                    'triggeredAlerts' => $triggeredAlert->toArray(),
                    'nearestLocation' => $this->nearestLocation->name,
                    'locations' => Cache::remember('locations', now()->addHour(), function () {
                        return Location::select(['id', 'name', 'country'])->get();
                    }),
                    'countries' => Cache::remember('countries', now()->addHour(), function () {
                        return Location::all()->pluck('country')->unique()->values()->toArray();
                    }),
                    'country' => $this->nearestLocation->country,
                    'location' => $this->nearestLocation->name,
                    'location_id' => $this->nearestLocation->id,
                    'weather' => $weather,
                    'currentWeather' => $currentWeatherResponse->ok() ? $currentWeatherResponse->json() : null,
                    'todayHourlyForecast' => HourlyForecast::where('location_id', $this->nearestLocation->id)
                        ->whereBetween('time_epoch', [Carbon::now()->timestamp, Carbon::now()->addHours(24)->timestamp])->select(['time', 'temp_c', 'temp_f', 'precip_mm', 'precip_in'])->orderBy('time_epoch')->get()
                ]);
            } else {
                // HourlyForecast::where('location_id', $this->nearestLocation->id)
                // ->whereBetween('time_epoch', [Carbon::now()->subDays(7)->timestamp, Carbon::now()->addDays(7)->timestamp])->get()
                $weatherData = collect([]);
                for ($j = -6; $j <= 7; $j++) {
                    $weather = FacadesDB::select('SELECT * FROM get_daily_stat(?, ?, ?)', [Carbon::now()->addDays($j - 1)->getTimestamp(), Carbon::now()->addDays($j)->getTimestamp(), $this->nearestLocation->id]);
                    foreach ($weather as &$record) {
                        $record->date = Carbon::now()->addDays($j - 1)->format('Y-m-d');
                    }
                    $weatherData = $weatherData->merge(json_decode(json_encode($weather)));
                }
                return Inertia::render('Locations/Index', [
                    'triggeredAlerts' => $triggeredAlert->toArray(),
                    'nearestLocation' => $this->nearestLocation->name,
                    'locations' => Cache::remember('locations', now()->addHour(), function () {
                        return Location::select(['id', 'name', 'country'])->get();
                    }),
                    'countries' => Cache::remember('countries', now()->addHour(), function () {
                        return Location::all()->pluck(value: 'country')->unique()->values()->toArray();
                    }),
                    'country' => $this->nearestLocation->country,
                    'location' => $this->nearestLocation->name,
                    'location_id' => $this->nearestLocation->id,
                    'weather' => $weatherData->toArray(),
                    'currentWeather' => $currentWeatherResponse->ok() ? $currentWeatherResponse->json() : null,
                    'todayHourlyForecast' => HourlyForecast::where('location_id', $this->nearestLocation->id)
                        ->whereBetween('time_epoch', [Carbon::now()->timestamp, Carbon::now()->addHours(24)->timestamp])->select(['time', 'temp_c', 'temp_f', 'precip_mm', 'precip_in'])->orderBy('time_epoch')->get()
                ]);
            }
        } else {
            $query = ($lat && $long) ? "{$lat}, {$long}" : $city;
            $response = null;
            $attempts = 0;
            do {
                $response = Http::get("http://api.weatherapi.com/v1/search.json?key={$this->weatherApiKey}&q={$query}");
                $attempts++;
            } while (!$response->ok() && $attempts < $this->maxAttempts);

            GatherWeatherData::dispatchSync($response->json()[0]['name']);
            $this->nearestLocation = Location::where('name', $response->json()[0]['name'])->without('hourly_forecasts')->get()->first();
            return Inertia::render('Locations/Index', [
                'messageType' => 'warning',
                'message' => 'Data is from a third-party source and may vary slightly',
                'locations' => Cache::remember('locations', now()->addHour(), function () {
                    return Location::select(['id', 'name', 'country'])->get();
                }),
                'countries' => Cache::remember('countries', now()->addHour(), function () {
                    return Location::all()->pluck('country')->unique()->values()->toArray();
                }),
                'country' => $response->json()[0]['country'],
                'location' => $response->json()[0]['name'],
                'location_id' => $this->nearestLocation->id,
                'weather' => $this->getDailyForecast($this->nearestLocation->name),
                'currentWeather' => $currentWeatherResponse->ok() ? $currentWeatherResponse->json() : null,
                'todayHourlyForecast' => HourlyForecast::where('location_id', $this->nearestLocation->id)
                    ->whereBetween('time_epoch', [Carbon::now()->timestamp, Carbon::now()->addHours(24)->timestamp])->select(['time', 'temp_c', 'temp_f', 'precip_mm', 'precip_in'])->orderBy('time_epoch')->get()
            ]);
        }
    }

    private function getDailyForecast($city)
    {
        $aggregratedData = collect([]);
        $keys = ['condition_text', 'condition_icon', 'condition_code'];
        for ($j = -7; $j <= 0; $j++) {
            $date = now()->addDays($j)->format('Y-m-d');
            $attempts = 0;
            do {
                $response = Http::get("http://api.weatherapi.com/v1/history.json?key={$this->weatherApiKey}&q={$city}&dt={$date}");
                $attempts++;
            } while (!$response->ok() && $attempts < $this->maxAttempts);
            if (!$response->ok()) {
                Log::error("Failed to fetch weather data for {$date} after {$this->maxAttempts} attempts.");
                continue;
            }
            $todayWeather = $response->json()['forecast']['forecastday'][0]['day'];
            $values = [$todayWeather['condition']['text'], $todayWeather['condition']['icon'], $todayWeather['condition']['code']];
            for ($i = 0; $i < count($keys); $i++) {
                $todayWeather[$keys[$i]] = $values[$i];
            }
            $todayWeather['condition'] = null;
            $todayWeather['date'] = $date;
            $aggregratedData = $aggregratedData->add($todayWeather);
        }
        $attempts = 0;
        do {
            $response = Http::get("http://api.weatherapi.com/v1/forecast.json?key={$this->weatherApiKey}&q={$city}&days=7");
            $attempts++;
        } while (!$response->ok() && $attempts < $this->maxAttempts);
        if (!$response->ok()) {
            Log::error("Failed to fetch forecast data after {$this->maxAttempts} attempts.");
            return $aggregratedData;
        }
        foreach ($response['forecast']['forecastday'] as $forecastDay) {
            if ($forecastDay['date'] === Carbon::now()->format('Y-m-d')) {
                continue;
            }
            $dayInfo = $forecastDay['day'];
            $values = [$dayInfo['condition']['text'], $dayInfo['condition']['icon'], $dayInfo['condition']['code']];
            for ($i = 0; $i < count($keys); $i++) {
                $dayInfo[$keys[$i]] = $values[$i];
            }
            $dayInfo['condition'] = null;
            $dayInfo['date'] = Carbon::createFromTimestamp($forecastDay['date_epoch'])->format('Y-m-d');
            $aggregratedData = $aggregratedData->add($dayInfo);
        }
        return $aggregratedData;
    }
}
