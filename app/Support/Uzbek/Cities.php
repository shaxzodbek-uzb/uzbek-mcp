<?php

declare(strict_types=1);

namespace App\Support\Uzbek;

/**
 * Coordinates of major Uzbek cities, so the weather tool can resolve common
 * names offline without a geocoding round-trip. Names are matched on their
 * canonical Latin spelling, common aliases, and Cyrillic form.
 */
final class Cities
{
    /** @var array<string, array{name:string, lat:float, lon:float}> */
    private const CITIES = [
        'toshkent' => ['name' => 'Toshkent', 'lat' => 41.2995, 'lon' => 69.2401],
        'samarqand' => ['name' => 'Samarqand', 'lat' => 39.6542, 'lon' => 66.9597],
        'buxoro' => ['name' => 'Buxoro', 'lat' => 39.7747, 'lon' => 64.4286],
        'andijon' => ['name' => 'Andijon', 'lat' => 40.7821, 'lon' => 72.3442],
        'namangan' => ['name' => 'Namangan', 'lat' => 40.9983, 'lon' => 71.6726],
        "farg\u{02BB}ona" => ['name' => "Farg\u{02BB}ona", 'lat' => 40.3864, 'lon' => 71.7864],
        'nukus' => ['name' => 'Nukus', 'lat' => 42.4731, 'lon' => 59.6103],
        'qarshi' => ['name' => 'Qarshi', 'lat' => 38.8606, 'lon' => 65.7975],
        "qo\u{02BB}qon" => ['name' => "Qo\u{02BB}qon", 'lat' => 40.5286, 'lon' => 70.9425],
        "marg\u{02BB}ilon" => ['name' => "Marg\u{02BB}ilon", 'lat' => 40.4711, 'lon' => 71.7247],
        'jizzax' => ['name' => 'Jizzax', 'lat' => 40.1158, 'lon' => 67.8422],
        'termiz' => ['name' => 'Termiz', 'lat' => 37.2242, 'lon' => 67.2783],
        'urganch' => ['name' => 'Urganch', 'lat' => 41.5500, 'lon' => 60.6333],
        'navoiy' => ['name' => 'Navoiy', 'lat' => 40.0844, 'lon' => 65.3792],
        'guliston' => ['name' => 'Guliston', 'lat' => 40.4897, 'lon' => 68.7842],
        'xiva' => ['name' => 'Xiva', 'lat' => 41.3783, 'lon' => 60.3639],
    ];

    /** Common aliases (English / Russian / ASCII) → canonical key. */
    private const ALIASES = [
        'tashkent' => 'toshkent',
        'samarkand' => 'samarqand',
        'bukhara' => 'buxoro',
        'andijan' => 'andijon',
        'fergana' => "farg\u{02BB}ona",
        "farg'ona" => "farg\u{02BB}ona",
        'fargona' => "farg\u{02BB}ona",
        'karshi' => 'qarshi',
        'kokand' => "qo\u{02BB}qon",
        "qo'qon" => "qo\u{02BB}qon",
        'qoqon' => "qo\u{02BB}qon",
        'margilan' => "marg\u{02BB}ilon",
        "marg'ilon" => "marg\u{02BB}ilon",
        'margilon' => "marg\u{02BB}ilon",
        'jizzakh' => 'jizzax',
        'termez' => 'termiz',
        'urgench' => 'urganch',
        'navoi' => 'navoiy',
        'gulistan' => 'guliston',
        'khiva' => 'xiva',
    ];

    /**
     * Resolve a city name to coordinates, or null if unknown. Cyrillic input is
     * transliterated to Latin first; the apostrophe flavour is normalised.
     *
     * @return array{name:string, lat:float, lon:float}|null
     */
    public static function find(string $name): ?array
    {
        $key = self::key($name);

        if (isset(self::ALIASES[$key])) {
            $key = self::ALIASES[$key];
        }

        return self::CITIES[$key] ?? null;
    }

    private static function key(string $name): string
    {
        $name = trim($name);

        if (preg_match('/\p{Cyrillic}/u', $name) === 1) {
            $name = Transliterator::toLatin($name);
        }

        $name = mb_strtolower($name);

        // Unify apostrophe variants to U+02BB so "o'zbek" matches "oʻzbek".
        return str_replace(
            ["'", '`', "\u{02BC}", "\u{2018}", "\u{2019}"],
            Transliterator::TURNED_COMMA,
            $name
        );
    }
}
