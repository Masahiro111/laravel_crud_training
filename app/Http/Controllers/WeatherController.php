<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class WeatherController extends Controller
{
    public function __invoke($city)
    {
        $coordinates = config('app.cities.' . $city);

        return Cache::remember('city' . $city, 60 * 5, function () use ($coordinates) {
            $response = Http::get('https://api.open-meteo.com/v1/forecast?latitude=' . $coordinates['lat'] . '&longitude=' . $coordinates['lng'] . '&daily=temperature_2m_max,temperature_2m_min&timezone=UTC');

            if ($response->successful()) {
                return $response->json('daily');
            }

            return response()->json([]);
        });
    }
}
