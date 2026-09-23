<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class OpenWeatherService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.openweather.base_url');
        $this->apiKey = config('services.openweather.key');
    }

    /**
     * Get coordinates for a given place name.
     */
    public function geocode(string $place): ?array
    {
        return Cache::remember(
            "geocode:{$place}",
            now()->addDay(),
            function () use ($place) {
                $response = Http::get("{$this->baseUrl}/geo/1.0/direct", [
                    'q' => $place,
                    'limit' => 1,
                    'appid' => $this->apiKey,
                ]);

                $response->throw();

                $results = $response->json();

                return $results[0] ?? null;
            }
        );
    }

    /**
     * Get current weather for given coordinates.
     */
    public function currentWeather(float $lat, float $lon): array
    {
        return Cache::remember(
            "weather:{$lat}:{$lon}",
            now()->addMinutes(10),
            function () use ($lat, $lon) {
                $response = Http::get("{$this->baseUrl}/data/2.5/weather", [
                    'lat' => $lat,
                    'lon' => $lon,
                    'appid' => $this->apiKey,
                    'units' => 'metric',
                ]);

                $response->throw();

                return $response->json();
            }
        );
    }

    /**
     * Get forecast for given coordinates.
     */
    public function forecast(float $lat, float $lon): array
    {
        return Cache::remember(
            "forecast:{$lat}:{$lon}",
            now()->addMinutes(10),
            function () use ($lat, $lon) {
                $response = Http::get("{$this->baseUrl}/data/2.5/forecast", [
                    'lat' => $lat,
                    'lon' => $lon,
                    'appid' => $this->apiKey,
                    'units' => 'metric',
                ]);

                $response->throw();

                return $response->json();
            }
        );
    }
}