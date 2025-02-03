<?php

use App\Jobs\DispatchCityJob;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new DispatchCityJob())
  ->everyMinute()
  ->onOneServer();
