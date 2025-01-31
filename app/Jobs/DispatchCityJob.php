<?php

namespace App\Jobs;


use App\Jobs\GatherWeatherData;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class DispatchCityJob implements ShouldQueue
{
    use Queueable;

    public function __construct() {}

    public function handle(): void
    {
        $cities = ['Singapore', 'Jakarta', 'Kuala Lumpur', 'Bangkok', 'Manila', 'Hanoi', 'Phnom Penh', 'Vientiane'];

        foreach ($cities as $city) {
            GatherWeatherData::dispatch($city);
            Log::info("Dispatched job for city: {$city}");
        }

        Log::info('All city jobs have been dispatched.');
    }
}
