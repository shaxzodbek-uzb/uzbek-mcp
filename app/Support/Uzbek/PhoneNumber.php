<?php

declare(strict_types=1);

namespace App\Support\Uzbek;

/**
 * Parse and normalise Uzbek phone numbers.
 *
 * Uzbekistan is country code +998 with a fixed 9-digit national number: a 2-digit
 * area/operator code followed by 7 subscriber digits. That fixed shape is what makes
 * the common input forms unambiguous — `+998 90 123 45 67`, `998901234567`,
 * `8 90 123-45-67`, `90 123 45 67` all denote the same subscriber.
 *
 * The operator/region table maps the 2-digit prefix to a human label. Mobile prefixes
 * are attributed to the operator that was originally allocated the range; number
 * portability means the label describes the range, not necessarily today's carrier,
 * and {@see lookup()} says so rather than implying a live lookup.
 */
final class PhoneNumber
{
    public const COUNTRY_CODE = '998';

    /** National significant number length (area/operator code + subscriber). */
    private const NSN_LENGTH = 9;

    /**
     * 2-digit prefix => [label, kind].
     *
     * @var array<string, array{0: string, 1: string}>
     */
    private const PREFIXES = [
        // Mobile ranges, by originally allocated operator.
        '90' => ['Beeline', 'mobile'],
        '91' => ['Beeline', 'mobile'],
        '93' => ['Ucell', 'mobile'],
        '94' => ['Ucell', 'mobile'],
        '95' => ['Uzmobile', 'mobile'],
        '99' => ['Uzmobile', 'mobile'],
        '97' => ['Mobiuz', 'mobile'],
        '88' => ['Humans', 'mobile'],
        '33' => ['Humans', 'mobile'],
        '77' => ['Perfectum', 'mobile'],
        '20' => ['Uzmobile', 'mobile'],

        // Landline area codes.
        '71' => ['Toshkent (shahar)', 'landline'],
        '70' => ['Toshkent (viloyat)', 'landline'],
        '66' => ['Samarqand', 'landline'],
        '65' => ['Buxoro', 'landline'],
        '74' => ['Jizzax', 'landline'],
        '75' => ['Qashqadaryo', 'landline'],
        '76' => ['Surxondaryo', 'landline'],
        '67' => ['Navoiy', 'landline'],
        '69' => ['Xorazm', 'landline'],
        '61' => ["Qoraqalpog\u{02BB}iston", 'landline'],
        '73' => ["Farg\u{02BB}ona", 'landline'],
        '72' => ['Namangan', 'landline'],
        '78' => ['Andijon / xizmat raqamlari', 'landline'],
        '79' => ['Sirdaryo', 'landline'],
    ];

    /**
     * Reduce arbitrary user input to the 9-digit national number.
     *
     * Returns null when the input cannot be read as an Uzbek number. Accepts a leading
     * `+998`, a bare `998`, the domestic trunk prefix `8`, or the national number on its
     * own; separators, spaces and parentheses are ignored.
     */
    public static function national(string $input): ?string
    {
        $digits = preg_replace('/\D+/', '', $input) ?? '';

        if ($digits === '') {
            return null;
        }

        // 998901234567 (12) — with country code.
        if (str_starts_with($digits, self::COUNTRY_CODE)
            && strlen($digits) === strlen(self::COUNTRY_CODE) + self::NSN_LENGTH) {
            return substr($digits, strlen(self::COUNTRY_CODE));
        }

        // 890 123 45 67 (10) — domestic trunk prefix. Only when what follows is a
        // known prefix, so a 10-digit number that merely starts with 8 is rejected
        // rather than silently reinterpreted.
        if (strlen($digits) === self::NSN_LENGTH + 1 && str_starts_with($digits, '8')) {
            $rest = substr($digits, 1);

            return isset(self::PREFIXES[substr($rest, 0, 2)]) ? $rest : null;
        }

        // 901234567 (9) — national number as dialled locally.
        if (strlen($digits) === self::NSN_LENGTH) {
            return $digits;
        }

        return null;
    }

    /** E.164: `+998901234567`. The form to store in a database. */
    public static function e164(string $national): string
    {
        return '+'.self::COUNTRY_CODE.$national;
    }

    /** Human form: `+998 90 123 45 67`. */
    public static function international(string $national): string
    {
        return sprintf(
            '+%s %s %s %s %s',
            self::COUNTRY_CODE,
            substr($national, 0, 2),
            substr($national, 2, 3),
            substr($national, 5, 2),
            substr($national, 7, 2),
        );
    }

    /** Local form: `(90) 123-45-67`. */
    public static function local(string $national): string
    {
        return sprintf(
            '(%s) %s-%s-%s',
            substr($national, 0, 2),
            substr($national, 2, 3),
            substr($national, 5, 2),
            substr($national, 7, 2),
        );
    }

    /**
     * Operator/region for a national number.
     *
     * @return array{prefix: string, operator: string|null, kind: string}
     */
    public static function lookup(string $national): array
    {
        $prefix = substr($national, 0, 2);
        $entry = self::PREFIXES[$prefix] ?? null;

        return [
            'prefix' => $prefix,
            'operator' => $entry[0] ?? null,
            'kind' => $entry[1] ?? 'unknown',
        ];
    }

    /** True when the 2-digit prefix is an allocated Uzbek range. */
    public static function hasKnownPrefix(string $national): bool
    {
        return isset(self::PREFIXES[substr($national, 0, 2)]);
    }
}
