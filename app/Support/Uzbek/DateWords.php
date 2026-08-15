<?php

declare(strict_types=1);

namespace App\Support\Uzbek;

use DateTimeInterface;

/**
 * Spell a date in written Uzbek.
 *
 * Uzbek writes dates largest-to-smallest with the year first and both the year and the
 * day taking the ordinal suffix: 2026-08-15 is
 * "ikki ming yigirma oltinchi yil o‘n beshinchi avgust".
 *
 * The ordinal suffix is chosen by the *final word* of the spelled number, not by the
 * digit — Uzbek attaches -nchi after a vowel and -inchi after a consonant, so 2 (ikki)
 * becomes ikkinchi while 3 (uch) becomes uchinchi.
 */
final class DateWords
{
    /** Month names in the official Latin orthography. */
    private const MONTHS = [
        1 => 'yanvar', 2 => 'fevral', 3 => 'mart', 4 => 'aprel',
        5 => 'may', 6 => 'iyun', 7 => 'iyul', 8 => 'avgust',
        9 => 'sentabr', 10 => 'oktabr', 11 => 'noyabr', 12 => 'dekabr',
    ];

    /** Weekday names, keyed by ISO-8601 day number (1 = Monday). */
    private const WEEKDAYS = [
        1 => 'dushanba', 2 => 'seshanba', 3 => 'chorshanba', 4 => 'payshanba',
        5 => 'juma', 6 => 'shanba', 7 => 'yakshanba',
    ];

    /**
     * A handful of ordinals are irregular enough that suffixing the cardinal would be
     * wrong; everything else is formed by rule.
     */
    private const IRREGULAR = [
        1 => 'birinchi',
        2 => 'ikkinchi',
        3 => 'uchinchi',
        4 => "to\u{02BB}rtinchi",
    ];

    private const VOWELS = ['a', 'e', 'i', 'o', 'u', "o\u{02BB}"];

    /**
     * Spell a date.
     *
     * @param  bool  $withWeekday  prepend the weekday, e.g. "shanba, …"
     * @param  string  $script  'latin' (default) or 'cyrillic'
     */
    public static function spell(
        DateTimeInterface $date,
        bool $withWeekday = false,
        string $script = 'latin',
    ): string {
        $year = (int) $date->format('Y');
        $month = (int) $date->format('n');
        $day = (int) $date->format('j');

        $words = sprintf(
            '%s yil %s %s',
            self::ordinal($year),
            self::ordinal($day),
            self::MONTHS[$month],
        );

        if ($withWeekday) {
            $words = self::WEEKDAYS[(int) $date->format('N')].', '.$words;
        }

        return $script === 'cyrillic' ? Transliterator::toCyrillic($words) : $words;
    }

    /** The month name alone, e.g. 8 → "avgust". */
    public static function month(int $month): ?string
    {
        return self::MONTHS[$month] ?? null;
    }

    /** The weekday name for an ISO day number (1 = Monday). */
    public static function weekday(int $isoDay): ?string
    {
        return self::WEEKDAYS[$isoDay] ?? null;
    }

    /**
     * Uzbek ordinal for a positive integer: 15 → "o‘n beshinchi".
     *
     * Only the last word takes the suffix — "yigirma birinchi", not
     * "yigirmanchi birinchi".
     */
    public static function ordinal(int $number): string
    {
        $words = NumberConverter::toWords($number);
        $parts = explode(' ', $words);
        $last = array_pop($parts);

        $parts[] = self::suffix($last);

        return implode(' ', $parts);
    }

    /** Attach -nchi after a vowel, -inchi after a consonant, honouring the irregulars. */
    private static function suffix(string $word): string
    {
        foreach (self::IRREGULAR as $value => $ordinal) {
            if ($word === NumberConverter::toWords($value)) {
                return $ordinal;
            }
        }

        // oʻ is a single vowel written with two code points; check it before the
        // single-character vowels so "oʻn" is not read as ending in the modifier mark.
        foreach (self::VOWELS as $vowel) {
            if (mb_substr($word, -mb_strlen($vowel)) === $vowel) {
                return $word.'nchi';
            }
        }

        return $word.'inchi';
    }
}
