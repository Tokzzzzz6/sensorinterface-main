<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SensorReading;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SensorReadingController extends Controller
{
    public function store(Request $request)
    {
        $expectedToken = (string) config('app.sensor_api_token', '');

        $providedToken = (string) $request->header(
            'X-Sensor-Token',
            ''
        );

        if (
            $expectedToken === '' ||
            !hash_equals($expectedToken, $providedToken)
        ) {
            return response()->json([
                'message' => 'Unauthorized device.'
            ], 401);
        }

        $validated = $request->validate([
            'distance_cm' => 'nullable|numeric|min:0|max:1000',
            'temperature_c' => 'nullable|numeric|min:-40|max:125',
            'humidity_percent' => 'nullable|numeric|min:0|max:100',
            'mq135_raw' => 'nullable|integer|min:0|max:4095',
            'mq135_signal_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        if (empty($validated)) {
            return response()->json([
                'message' => 'No sensor readings provided.'
            ], 422);
        }

        $reading = SensorReading::create([
            ...$validated,
            'recorded_at' => now(),
        ]);

        return response()->json([
            'message' => 'Sensor readings saved successfully.',
            'data' => $reading,
        ], 201);
    }

    public function latest()
    {
        $reading = SensorReading::latest('recorded_at')->first();

        return response()->json([
            'data' => $reading,
        ]);
    }

    public function history()
    {
        $readings = SensorReading::orderByDesc('recorded_at')
            ->limit(50)
            ->get()
            ->reverse()
            ->values();

        return response()->json([
            'data' => $readings,
        ]);
    }
}
