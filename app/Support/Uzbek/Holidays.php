<?php

declare(strict_types=1);

namespace App\Support\Uzbek;

/**
 * Official public holidays of the Republic of Uzbekistan.
 *
 * Eight holidays fall on fixed Gregorian dates. The two Islamic holidays
 * (Ramazon hayit / Eid al-Fitr and Qurbon hayit / Eid al-Adha) follow the lunar
 * Hijri calendar and are fixed each year by presidential decree, so they are
 * provided from a per-year lookup table rather than computed.
 */
final class Holidays
{
    /** @var list<array{month:int, day:int, name_uz:string, name_en:string}> */
    private const FIXED = [
        ['month' => 1, 'day' => 1, 'name_uz' => 'Yangi yil', 'name_en' => "New Year's Day"],
        ['month' => 1, 'day' => 14, 'name_uz' => 'Vatan himoyachilari kuni', 'name_en' => 'Defenders of the Motherland Day'],
        ['month' => 3, 'day' => 8, 'name_uz' => 'Xotin-qizlar kuni', 'name_en' => "International Women's Day"],
        ['month' => 3, 'day' => 21, 'name_uz' => "Navro\u{02BB}z bayrami", 'name_en' => 'Navruz'],
        ['month' => 5, 'day' => 9, 'name_uz' => 'Xotira va qadrlash kuni', 'name_en' => 'Day of Memory and Honour'],
        ['month' => 9, 'day' => 1, 'name_uz' => 'Mustaqillik kuni', 'name_en' => 'Independence Day'],
        ['month' => 10, 'day' => 1, 'name_uz' => "O\u{02BB}qituvchi va murabbiylar kuni", 'name_en' => "Teachers' and Instructors' Day"],
        ['month' => 12, 'day' => 8, 'name_uz' => 'Konstitutsiya kuni', 'name_en' => 'Constitution Day'],
    ];

    /**
     * Lunar (Islamic) holiday dates by year, as set by government decree.
     *
     * @var array<int, array{ramazon:string, qurbon:string}>
     */
    private const LUNAR = [
        2024 => ['ramazon' => '2024-04-10', 'qurbon' => '2024-06-16'],
        2025 => ['ramazon' => '2025-03-30', 'qurbon' => '2025-06-06'],
        2026 => ['ramazon' => '2026-03-20', 'qurbon' => '2026-05-27'],
        2027 => ['ramazon' => '2027-03-10', 'qurbon' => '2027-05-16'],
    ];

    /**
     * Return all public holidays for a given year, sorted by date.
     *
     * @return list<array{date:string, name_uz:string, name_en:string, type:string, approximate:bool}>
     */
    public static function forYear(int $year): array
    {
        $holidays = [];

        foreach (self::FIXED as $holiday) {
            $holidays[] = [
                'date' => sprintf('%04d-%02d-%02d', $year, $holiday['month'], $holiday['day']),
                'name_uz' => $holiday['name_uz'],
                'name_en' => $holiday['name_en'],
                'type' => 'fixed',
                'approximate' => false,
            ];
        }

        if (isset(self::LUNAR[$year])) {
            $lunar = self::LUNAR[$year];
            $holidays[] = [
                'date' => $lunar['ramazon'],
                'name_uz' => 'Ramazon hayit',
                'name_en' => 'Eid al-Fitr (Ramadan Hayit)',
                'type' => 'lunar',
                'approximate' => false,
            ];
            $holidays[] = [
                'date' => $lunar['qurbon'],
                'name_uz' => 'Qurbon hayit',
                'name_en' => 'Eid al-Adha (Kurban Hayit)',
                'type' => 'lunar',
                'approximate' => false,
            ];
        }

        usort($holidays, static fn (array $a, array $b): int => strcmp($a['date'], $b['date']));

        return $holidays;
    }

    /** Whether the two lunar holidays are known for the given year. */
    public static function hasLunar(int $year): bool
    {
        return isset(self::LUNAR[$year]);
    }
}
