<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorReading extends Model
{
    protected $fillable = [
        'distance_cm',
        'temperature_c',
        'humidity_percent',
        'mq135_raw',
        'mq135_signal_percent',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'distance_cm' => 'float',
            'temperature_c' => 'float',
            'humidity_percent' => 'float',
            'mq135_raw' => 'integer',
            'mq135_signal_percent' => 'float',
            'recorded_at' => 'datetime',
        ];
    }
}