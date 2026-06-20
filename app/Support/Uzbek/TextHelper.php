<?php

declare(strict_types=1);

namespace App\Support\Uzbek;

use Normalizer;

/**
 * Normalisation and slug helpers for Uzbek text.
 */
final class TextHelper
{
    private const APOSTROPHES = [
        "'", '`', "\u{00B4}", "\u{02B9}", "\u{02BB}", "\u{02BC}",
        "\u{2018}", "\u{2019}", "\u{2032}",
    ];

    /**
     * Canonicalise Uzbek text:
     *  - apostrophes after o/g become U+02BB (Oʻ, Gʻ);
     *  - any other apostrophe becomes U+02BC (the tutuq belgisi);
     *  - Unicode is normalised to NFC;
     *  - runs of whitespace are collapsed when requested.
     */
    public static function normalize(string $text, bool $collapseWhitespace = true): string
    {
        $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $out = '';

        foreach ($chars as $i => $ch) {
            if (in_array($ch, self::APOSTROPHES, true)) {
                $prev = mb_strtolower($chars[$i - 1] ?? '');
                $out .= ($prev === 'o' || $prev === 'g')
                    ? Transliterator::TURNED_COMMA
                    : Transliterator::MODIFIER_APOSTROPHE;

                continue;
            }

            $out .= $ch;
        }

        if (class_exists(Normalizer::class)) {
            $normalized = Normalizer::normalize($out, Normalizer::FORM_C);

            if ($normalized !== false) {
                $out = $normalized;
            }
        }

        if ($collapseWhitespace) {
            $out = trim((string) preg_replace('/\s+/u', ' ', $out));
        }

        return $out;
    }

    /**
     * Build an ASCII URL slug from Uzbek text (Latin or Cyrillic). Oʻ/Gʻ lose
     * their mark (oʻ → o), the tutuq belgisi is dropped, and the digraphs sh/ch
     * are kept.
     */
    public static function slugify(string $text, string $separator = '-'): string
    {
        $latin = Transliterator::toLatin($text);

        $latin = str_replace(self::APOSTROPHES, '', $latin);
        $latin = mb_strtolower($latin);

        $latin = (string) preg_replace('/[^a-z0-9]+/u', $separator, $latin);

        return trim($latin, $separator);
    }
}
