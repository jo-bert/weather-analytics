<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class Alert extends Model
{
  use HasFactory;

  protected $fillable = [
    'location_id',
    'parameter',
    'condition',
    'value',
    'minValue',
    'maxValue',
    'expiry',
    'paused',
    'triggered',
    'triggered_at',
  ];

  protected static function boot()
  {
    parent::boot();

    static::creating(function ($model) {
      self::validateAttributes($model);
    });

    static::updating(function ($model) {
      self::validateAttributes($model);
    });
  }

  private static function validateAttributes($model)
  {
    $validParameters = [
      'temp_c',
      'temp_f',
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

    $validConditions = ['below', 'above', 'between'];

    if (!in_array($model->parameter, $validParameters)) {
      throw ValidationException::withMessages(['parameter' => 'Invalid parameter value.']);
    }

    if (!in_array($model->condition, $validConditions)) {
      throw ValidationException::withMessages(['condition' => 'Invalid condition value.']);
    }
  }

  public function location()
  {
    return $this->belongsTo(Location::class);
  }
}
