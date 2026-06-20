<?php

declare(strict_types=1);

namespace App\Support\Uzbek;

/**
 * Deterministic Uzbek transliteration between the Latin and Cyrillic scripts.
 *
 * Implements the official Uzbek national alphabet (1995 law, still in force in
 * 2026 — the 2018/2021 "Ó/Ǵ" diacritic reforms were never adopted). Key points:
 *
 *  - The letters Oʻ and Gʻ use U+02BB MODIFIER LETTER TURNED COMMA (ʻ).
 *  - The tutuq belgisi (glottal stop, Cyrillic ъ) uses U+02BC MODIFIER LETTER
 *    APOSTROPHE (ʼ) — a *different* character from the Oʻ/Gʻ mark.
 *  - Cyrillic е → "ye" word-initially / after a vowel / after ъ,ь, else "e".
 *  - Cyrillic ц → "ts" after a vowel, otherwise "s".
 *
 * Because a few mappings are inherently lossy (Latin e ← е/э, ц → ts/s, ь is
 * dropped), a Cyrillic→Latin→Cyrillic round-trip is not guaranteed for every
 * input; the common, well-formed cases do round-trip.
 */
final class Transliterator
{
    /** Modifier letter turned comma — the mark in Oʻ and Gʻ. */
    public const TURNED_COMMA = "\u{02BB}"; // ʻ

    /** Modifier letter apostrophe — the tutuq belgisi (Cyrillic ъ). */
    public const MODIFIER_APOSTROPHE = "\u{02BC}"; // ʼ

    /** Characters commonly used in place of an Uzbek apostrophe on input. */
    private const APOSTROPHES = [
        "'", '`', "\u{00B4}", "\u{02B9}", "\u{02BB}", "\u{02BC}",
        "\u{2018}", "\u{2019}", "\u{2032}",
    ];

    /** Single Latin → Cyrillic letters (lowercase). "e" is handled positionally. */
    private const LATIN_SINGLE = [
        'a' => 'а', 'b' => 'б', 'c' => 'с', 'd' => 'д', 'f' => 'ф', 'g' => 'г',
        'h' => 'ҳ', 'i' => 'и', 'j' => 'ж', 'k' => 'к', 'l' => 'л', 'm' => 'м',
        'n' => 'н', 'o' => 'о', 'p' => 'п', 'q' => 'қ', 'r' => 'р', 's' => 'с',
        't' => 'т', 'u' => 'у', 'v' => 'в', 'w' => 'в', 'x' => 'х', 'y' => 'й',
        'z' => 'з',
    ];

    /** Single Cyrillic → Latin letters (lowercase). "е" and "ц" are positional. */
    private const CYRILLIC_SINGLE = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'ж' => 'j',
        'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'қ' => 'q', 'л' => 'l',
        'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's',
        'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'x', 'ҳ' => 'h', 'ч' => 'ch',
        'ш' => 'sh', 'щ' => 'shch', 'э' => 'e', 'ё' => 'yo', 'ю' => 'yu',
        'я' => 'ya', 'ы' => 'i',
        'ғ' => 'g'.self::TURNED_COMMA,
        'ў' => 'o'.self::TURNED_COMMA,
        'ъ' => self::MODIFIER_APOSTROPHE,
        'ь' => '',
    ];

    private const CYRILLIC_VOWELS = ['а', 'о', 'у', 'и', 'э', 'е', 'ё', 'ю', 'я', 'ў', 'ы'];

    /**
     * Transliterate text, auto-detecting the direction unless one is given.
     *
     * @param  'auto'|'to_cyrillic'|'to_latin'  $direction
     * @return array{direction: 'to_cyrillic'|'to_latin', result: string}
     */
    public static function transliterate(string $text, string $direction = 'auto'): array
    {
        if ($direction === 'auto') {
            $direction = self::detect($text);
        }

        $result = $direction === 'to_cyrillic'
            ? self::toCyrillic($text)
            : self::toLatin($text);

        return ['direction' => $direction, 'result' => $result];
    }

    /**
     * Detect the conversion to apply: Cyrillic-heavy text converts to Latin and
     * vice-versa. Ties (or scriptless text) default to Latin → Cyrillic.
     *
     * @return 'to_cyrillic'|'to_latin'
     */
    public static function detect(string $text): string
    {
        $cyrillic = preg_match_all('/\p{Cyrillic}/u', $text);
        $latin = preg_match_all('/\p{Latin}/u', $text);

        return $cyrillic > $latin ? 'to_latin' : 'to_cyrillic';
    }

    /** Convert Uzbek Latin text to Cyrillic. */
    public static function toCyrillic(string $text): string
    {
        $chars = self::split($text);
        $count = count($chars);
        $out = '';

        for ($i = 0; $i < $count; $i++) {
            $ch = $chars[$i];
            $lower = mb_strtolower($ch);
            $next = $chars[$i + 1] ?? '';
            $lowerNext = mb_strtolower($next);
            $nextIsApostrophe = $next !== '' && in_array($next, self::APOSTROPHES, true);

            // Oʻ / Gʻ (letter + apostrophe of any flavour).
            if (($lower === 'o' || $lower === 'g') && $nextIsApostrophe) {
                $out .= self::matchCase($ch, $lower === 'o' ? 'ў' : 'ғ');
                $i++;

                continue;
            }

            // yo / yu / ya / ye digraphs.
            if ($lower === 'y' && in_array($lowerNext, ['o', 'u', 'a', 'e'], true)) {
                $map = ['yo' => 'ё', 'yu' => 'ю', 'ya' => 'я', 'ye' => 'е'];
                $out .= self::matchCase($ch, $map[$lower.$lowerNext]);
                $i++;

                continue;
            }

            // sh / ch digraphs.
            if (($lower === 's' || $lower === 'c') && $lowerNext === 'h') {
                $out .= self::matchCase($ch, $lower === 's' ? 'ш' : 'ч');
                $i++;

                continue;
            }

            // A standalone apostrophe is the tutuq belgisi → ъ.
            if (in_array($ch, self::APOSTROPHES, true)) {
                $out .= 'ъ';

                continue;
            }

            // "e": word-initial → э, otherwise → е.
            if ($lower === 'e') {
                $prev = $chars[$i - 1] ?? '';
                $out .= self::matchCase($ch, self::isLetter($prev) ? 'е' : 'э');

                continue;
            }

            if (isset(self::LATIN_SINGLE[$lower])) {
                $out .= self::matchCase($ch, self::LATIN_SINGLE[$lower]);

                continue;
            }

            $out .= $ch;
        }

        return $out;
    }

    /** Convert Uzbek Cyrillic text to Latin. */
    public static function toLatin(string $text): string
    {
        $chars = self::split($text);
        $count = count($chars);
        $out = '';

        for ($i = 0; $i < $count; $i++) {
            $ch = $chars[$i];
            $lower = mb_strtolower($ch);
            $next = $chars[$i + 1] ?? '';
            $prev = $chars[$i - 1] ?? '';

            // "е": ye word-initially / after a vowel / after ъ,ь, else e.
            if ($lower === 'е') {
                $prevLower = mb_strtolower($prev);
                $useYe = ! self::isLetter($prev)
                    || self::isCyrillicVowel($prev)
                    || $prevLower === 'ъ'
                    || $prevLower === 'ь';
                $out .= self::expandCase($ch, $next, $useYe ? 'ye' : 'e');

                continue;
            }

            // "ц": ts after a vowel, otherwise s.
            if ($lower === 'ц') {
                $out .= self::expandCase($ch, $next, self::isCyrillicVowel($prev) ? 'ts' : 's');

                continue;
            }

            if (array_key_exists($lower, self::CYRILLIC_SINGLE)) {
                $target = self::CYRILLIC_SINGLE[$lower];

                if ($target === '') {
                    continue; // ь is dropped
                }

                $out .= mb_strlen($target) === 1
                    ? self::matchCase($ch, $target)
                    : self::expandCase($ch, $next, $target);

                continue;
            }

            $out .= $ch;
        }

        return $out;
    }

    /** @return list<string> */
    private static function split(string $text): array
    {
        return preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }

    /** Apply the source character's case to a single-character target. */
    private static function matchCase(string $source, string $targetLower): string
    {
        return self::isUpper($source) ? mb_strtoupper($targetLower) : $targetLower;
    }

    /**
     * Apply case when one source character expands to several target characters.
     * An uppercase source followed by another uppercase letter yields an all-caps
     * target ("ШАҲАР" → "SHAHAR"); otherwise it is title-cased ("Шаҳар" → "Shahar").
     */
    private static function expandCase(string $source, string $next, string $targetLower): string
    {
        if (! self::isUpper($source)) {
            return $targetLower;
        }

        if ($next !== '' && self::isUpper($next)) {
            return mb_strtoupper($targetLower);
        }

        return mb_strtoupper(mb_substr($targetLower, 0, 1)).mb_substr($targetLower, 1);
    }

    private static function isUpper(string $ch): bool
    {
        return $ch !== '' && mb_strtolower($ch) !== $ch;
    }

    private static function isLetter(string $ch): bool
    {
        return $ch !== '' && preg_match('/\p{L}/u', $ch) === 1;
    }

    private static function isCyrillicVowel(string $ch): bool
    {
        return in_array(mb_strtolower($ch), self::CYRILLIC_VOWELS, true);
    }
}
