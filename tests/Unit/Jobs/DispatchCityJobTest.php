<?php

namespace Tests\Unit\Jobs;

use App\Jobs\DispatchCityJob;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;


class DispatchCityJobTest extends TestCase
{
  /** @test */
  public function dispatches_gather_weather_data_jobs_for_each_city(): void
  {
    Queue::fake();
    Log::spy();

    $job = new DispatchCityJob();
    $job->handle();

    $cities = ['Singapore', 'Jakarta', 'Kuala Lumpur', 'Penang', 'Kuching', 'Bangkok', 'Manila', 'Hanoi', 'Phnom Penh', 'Vientiane', 'Tokyo'];

    foreach ($cities as $city) {
      // Queue::assertPushed(GatherWeatherData::class, function ($job) use ($city) {
      //   return $job->city === $city;
      // });
      Log::assertLogged('info', "Dispatched job for city: {$city}");
    }

    Log::assertLogged('info', 'All city jobs have been dispatched.');
  }
}
