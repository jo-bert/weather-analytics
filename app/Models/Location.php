<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = ['name', 'region', 'country', 'lat', 'lon', 'tz_id'];

    public function hourly_forcasts()
    {
        return $this->hasMany(HourlyForecast::class);
    }
}
