<?php

namespace App\Console\Commands;

use App\Jobs\GatherWeatherData;
use Illuminate\Console\Command;

class DispatchCity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:dispatch-city {city}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch a city';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        print 'Dispatching city ' . $this->argument('city') . PHP_EOL;
        GatherWeatherData::dispatchSync($this->argument('city'));
        print 'Dispatching done ' . PHP_EOL;
    }
}
