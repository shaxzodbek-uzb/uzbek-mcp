<?php

declare(strict_types=1);

namespace App\Support\Uzbek;

/**
 * Human-readable descriptions for WMO weather interpretation codes
 * (as returned by Open-Meteo) in Uzbek and English.
 */
final class WeatherCodes
{
    /** @var array<int, array{uz:string, en:string}> */
    private const CODES = [
        0 => ['uz' => 'Ochiq osmon', 'en' => 'Clear sky'],
        1 => ['uz' => 'Asosan ochiq', 'en' => 'Mainly clear'],
        2 => ['uz' => 'Qisman bulutli', 'en' => 'Partly cloudy'],
        3 => ['uz' => 'Bulutli', 'en' => 'Overcast'],
        45 => ['uz' => 'Tuman', 'en' => 'Fog'],
        48 => ['uz' => 'Qirovli tuman', 'en' => 'Depositing rime fog'],
        51 => ['uz' => 'Yengil shivalama', 'en' => 'Light drizzle'],
        53 => ['uz' => "O\u{02BB}rtacha shivalama", 'en' => 'Moderate drizzle'],
        55 => ['uz' => 'Kuchli shivalama', 'en' => 'Dense drizzle'],
        56 => ['uz' => 'Muzli shivalama', 'en' => 'Light freezing drizzle'],
        57 => ['uz' => 'Kuchli muzli shivalama', 'en' => 'Dense freezing drizzle'],
        61 => ['uz' => 'Yengil yomgʻir', 'en' => 'Slight rain'],
        63 => ['uz' => "O\u{02BB}rtacha yomg\u{02BB}ir", 'en' => 'Moderate rain'],
        65 => ['uz' => "Kuchli yomg\u{02BB}ir", 'en' => 'Heavy rain'],
        66 => ['uz' => "Yengil muzli yomg\u{02BB}ir", 'en' => 'Light freezing rain'],
        67 => ['uz' => "Kuchli muzli yomg\u{02BB}ir", 'en' => 'Heavy freezing rain'],
        71 => ['uz' => 'Yengil qor', 'en' => 'Slight snow fall'],
        73 => ['uz' => "O\u{02BB}rtacha qor", 'en' => 'Moderate snow fall'],
        75 => ['uz' => 'Kuchli qor', 'en' => 'Heavy snow fall'],
        77 => ['uz' => 'Qor donalari', 'en' => 'Snow grains'],
        80 => ['uz' => 'Yengil jala', 'en' => 'Slight rain showers'],
        81 => ['uz' => "O\u{02BB}rtacha jala", 'en' => 'Moderate rain showers'],
        82 => ['uz' => 'Kuchli jala', 'en' => 'Violent rain showers'],
        85 => ['uz' => 'Yengil qor jalasi', 'en' => 'Slight snow showers'],
        86 => ['uz' => 'Kuchli qor jalasi', 'en' => 'Heavy snow showers'],
        95 => ['uz' => 'Momaqaldiroq', 'en' => 'Thunderstorm'],
        96 => ['uz' => "Momaqaldiroq, yengil do\u{02BB}l", 'en' => 'Thunderstorm with slight hail'],
        99 => ['uz' => "Momaqaldiroq, kuchli do\u{02BB}l", 'en' => 'Thunderstorm with heavy hail'],
    ];

    /** @return array{uz:string, en:string} */
    public static function describe(int $code): array
    {
        return self::CODES[$code] ?? ['uz' => "Noma\u{02BC}lum", 'en' => 'Unknown'];
    }
}
