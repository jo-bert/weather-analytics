<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HourlyForecast extends Model
{
    protected $fillable = [
        'location_id',
        'time_epoch',
        'time',
        'temp_c',
        'temp_f',
        'is_day',
        'condition_text',
        'condition_icon',
        'condition_code',
        'wind_mph',
        'wind_kph',
        'wind_degree',
        'wind_dir',
        'pressure_mb',
        'pressure_in',
        'precip_mm',
        'precip_in',
        'snow_cm',
        'humidity',
        'cloud',
        'feelslike_c',
        'feelslike_f',
        'windchill_c',
        'windchill_f',
        'heatindex_c',
        'heatindex_f',
        'dewpoint_c',
        'dewpoint_f',
        'will_it_rain',
        'chance_of_rain',
        'will_it_snow',
        'chance_of_snow',
        'vis_km',
        'vis_miles',
        'gust_mph',
        'gust_kph',
        'uv'
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
