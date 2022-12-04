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

        $response = Http::get('https://api.open-meteo.com/v1/forecast?latitude=' . $coordinates['lat'] . '&longitude=' . $coordinates['lng'] . '&hourly=temperature_2m,weathercode&daily=temperature_2m_max,temperature_2m_min,sunrise,sunset&current_weather=true&timezone=Asia%2FTokyo');

        if ($response->successful()) {
            // return $response->json([]); 全部返却
            return $response->json('hourly');
        }

        return response()->json([]);
    }
}
