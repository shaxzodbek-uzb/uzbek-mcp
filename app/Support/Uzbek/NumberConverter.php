<?php

declare(strict_types=1);

namespace App\Support\Uzbek;

use RuntimeException;

/**
 * Convert integers to written Uzbek words (Latin script, official spelling with
 * the U+02BB apostrophe in toʻrt, oʻn, etc.).
 *
 * Uses the formal convention required by sum-in-words / financial usage, where
 * the multiplier "bir" is always kept: 100 → "bir yuz", 1000 → "bir ming",
 * 1 000 000 → "bir million". Words are space-separated, largest scale first,
 * with all-zero groups skipped (1 000 000 → "bir million").
 */
final class NumberConverter
{
    private const ONES = [
        'nol', 'bir', 'ikki', 'uch', "to\u{02BB}rt", 'besh',
        'olti', 'yetti', 'sakkiz', "to\u{02BB}qqiz",
    ];

    private const TEN = "o\u{02BB}n";

    private const TENS = [
        2 => 'yigirma', 3 => "o\u{02BB}ttiz", 4 => 'qirq', 5 => 'ellik',
        6 => 'oltmish', 7 => 'yetmish', 8 => 'sakson', 9 => "to\u{02BB}qson",
    ];

    /** Scale words by 3-digit group index (0 = units, 1 = thousands, …). */
    private const SCALES = [
        1 => 'ming', 2 => 'million', 3 => 'milliard',
        4 => 'trillion', 5 => 'kvadrillion', 6 => 'kvintillion',
    ];

    /**
     * Convert an integer to written Uzbek words.
     *
     * @param  string  $script  'latin' (default) or 'cyrillic'
     * @param  string|null  $currency  optional unit appended verbatim, e.g. "soʻm"
     */
    public static function toWords(int $number, ?string $currency = null, string $script = 'latin'): string
    {
        $words = self::integerToWords($number);

        if ($currency !== null && $currency !== '') {
            $words .= ' '.$currency;
        }

        if ($script === 'cyrillic') {
            $words = Transliterator::toCyrillic($words);
        }

        return $words;
    }

    private static function integerToWords(int $number): string
    {
        if ($number === 0) {
            return self::ONES[0];
        }

        $negative = $number < 0;
        $n = abs($number);

        $groups = [];
        while ($n > 0) {
            $groups[] = $n % 1000;
            $n = intdiv($n, 1000);
        }

        $parts = [];
        for ($k = count($groups) - 1; $k >= 0; $k--) {
            $group = $groups[$k];

            if ($group === 0) {
                continue;
            }

            $rendered = self::groupToWords($group);

            if ($k > 0) {
                if (! isset(self::SCALES[$k])) {
                    throw new RuntimeException('Number is too large to express in words.');
                }

                $rendered .= ' '.self::SCALES[$k];
            }

            $parts[] = $rendered;
        }

        $words = implode(' ', $parts);

        return $negative ? 'minus '.$words : $words;
    }

    /** Render a value in 0–999 as Uzbek words. */
    private static function groupToWords(int $n): string
    {
        $words = [];

        $hundreds = intdiv($n, 100);
        $remainder = $n % 100;

        if ($hundreds > 0) {
            $words[] = self::ONES[$hundreds];
            $words[] = 'yuz';
        }

        $tens = intdiv($remainder, 10);
        $ones = $remainder % 10;

        if ($tens === 1) {
            $words[] = self::TEN;
        } elseif ($tens >= 2) {
            $words[] = self::TENS[$tens];
        }

        if ($ones > 0) {
            $words[] = self::ONES[$ones];
        }

        return implode(' ', $words);
    }
}
