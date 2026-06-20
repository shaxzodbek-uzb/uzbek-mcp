<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Support\Uzbek\Cities;
use App\Support\Uzbek\WeatherCodes;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Http;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Throwable;

#[Name('weather')]
#[Title('Uzbekistan weather')]
#[Description(
    'Get the current weather and today\'s forecast for a city in Uzbekistan via Open-Meteo (no API key). '
    .'Major Uzbek cities are recognised by name in Latin or Cyrillic (e.g. Toshkent, Samarqand, Buxoro); '
    .'other names are geocoded automatically. You may also pass explicit latitude/longitude.'
)]
#[IsReadOnly]
class WeatherTool extends Tool
{
    private const FORECAST_URL = 'https://api.open-meteo.com/v1/forecast';

    private const GEOCODE_URL = 'https://geocoding-api.open-meteo.com/v1/search';

    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'city' => ['sometimes', 'nullable', 'string', 'max:120'],
            'latitude' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
        ]);

        $latitude = isset($validated['latitude']) ? (float) $validated['latitude'] : null;
        $longitude = isset($validated['longitude']) ? (float) $validated['longitude'] : null;
        $city = $validated['city'] ?? null;

        $locationName = $city ?? 'Toshkent';

        if ($latitude === null || $longitude === null) {
            $resolved = $this->resolveLocation($city ?? 'Toshkent');

            if ($resolved instanceof Response) {
                return $resolved;
            }

            [$latitude, $longitude, $locationName] = $resolved;
        }

        try {
            $http = Http::acceptJson()->timeout(15)->retry(2, 200)->get(self::FORECAST_URL, [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'current' => 'temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m',
                'daily' => 'temperature_2m_max,temperature_2m_min,weather_code',
                'timezone' => 'Asia/Tashkent',
                'forecast_days' => 1,
            ]);
        } catch (Throwable $e) {
            return Response::error("Could not reach the weather service: {$e->getMessage()}");
        }

        if (! $http->successful()) {
            return Response::error("Weather service returned HTTP {$http->status()}.");
        }

        $current = $http->json('current', []);
        $daily = $http->json('daily', []);
        $code = (int) ($current['weather_code'] ?? 0);

        $payload = [
            'location' => $locationName,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'timezone' => 'Asia/Tashkent',
            'condition' => WeatherCodes::describe($code),
            'current' => [
                'time' => $current['time'] ?? null,
                'temperature_c' => $current['temperature_2m'] ?? null,
                'humidity_percent' => $current['relative_humidity_2m'] ?? null,
                'wind_speed_kmh' => $current['wind_speed_10m'] ?? null,
            ],
            'today' => [
                'max_c' => $daily['temperature_2m_max'][0] ?? null,
                'min_c' => $daily['temperature_2m_min'][0] ?? null,
            ],
        ];

        return Response::json($payload);
    }

    /**
     * Resolve a city name to [lat, lon, name], or return an error Response.
     *
     * @return array{0: float, 1: float, 2: string}|Response
     */
    private function resolveLocation(string $city): array|Response
    {
        if ($known = Cities::find($city)) {
            return [$known['lat'], $known['lon'], $known['name']];
        }

        try {
            $http = Http::acceptJson()->timeout(15)->get(self::GEOCODE_URL, [
                'name' => $city,
                'count' => 10,
                'language' => 'en',
                'format' => 'json',
            ]);
        } catch (Throwable $e) {
            return Response::error("Could not reach the geocoding service: {$e->getMessage()}");
        }

        $results = $http->successful() ? $http->json('results', []) : [];

        if (! is_array($results) || $results === []) {
            return Response::error("Could not find a location named [{$city}]. Try a major Uzbek city or pass latitude/longitude.");
        }

        // Prefer a match inside Uzbekistan, otherwise fall back to the first result.
        $match = null;
        foreach ($results as $result) {
            if (($result['country_code'] ?? null) === 'UZ') {
                $match = $result;
                break;
            }
        }
        $match ??= $results[0];

        return [
            (float) $match['latitude'],
            (float) $match['longitude'],
            $match['name'] ?? $city,
        ];
    }

    /**
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'city' => $schema->string()
                ->description('City name in Uzbekistan (Latin or Cyrillic). Defaults to Toshkent.')
                ->default('Toshkent'),

            'latitude' => $schema->number()
                ->description('Optional explicit latitude (overrides city).'),

            'longitude' => $schema->number()
                ->description('Optional explicit longitude (overrides city).'),
        ];
    }
}
