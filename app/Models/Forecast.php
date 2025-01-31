<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Forecast extends Model
{
    protected $fillable = [
        'location_id', 'date', 'date_epoch', 'maxtemp_c', 'maxtemp_f', 'mintemp_c', 'mintemp_f',
        'avgtemp_c', 'avgtemp_f', 'maxwind_mph', 'maxwind_kph', 'totalprecip_mm', 'totalprecip_in',
        'totalsnow_cm', 'avgvis_km', 'avgvis_miles', 'avghumidity', 'daily_will_it_rain',
        'daily_chance_of_rain', 'daily_will_it_snow', 'daily_chance_of_snow', 'condition_code', 'uv'
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function hourlyForecasts()
    {
        return $this->hasMany(HourlyForecast::class);
    }
}