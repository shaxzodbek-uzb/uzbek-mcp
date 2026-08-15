<?php

declare(strict_types=1);

namespace App\Support\Uzbek;

/**
 * Shape validation for a STIR — the Uzbek taxpayer identification number
 * (soliq to‘lovchining identifikatsiya raqami), 9 digits.
 *
 * **This checks the shape, not the taxpayer.** It catches the errors that actually
 * happen when a number is copied off an invoice — a missing digit, a transposed pair
 * that changes the length, stray separators, letters — and nothing else.
 *
 * It deliberately does **not** implement a checksum. Published descriptions of the
 * STIR check digit disagree with one another, and a wrong checksum in an invoicing
 * path is worse than no checksum: it rejects a real business's tax number with a
 * confident-sounding error, and the person hitting it has no way to argue. If you need
 * to know whether a STIR is real and belongs to whom it claims, verify it against the
 * State Tax Committee's register — that is the only authority, and no offline routine
 * can substitute for it.
 */
final class Stir
{
    /** A STIR is exactly 9 digits. */
    public const LENGTH = 9;

    /**
     * Reduce user input to bare digits.
     *
     * Returns null when the input contains anything other than digits and the usual
     * separators (spaces, hyphens), so an accidental letter is a failure rather than a
     * silently stripped character.
     */
    public static function digits(string $input): ?string
    {
        $trimmed = trim($input);

        if ($trimmed === '' || preg_match('/[^\d\s\-]/u', $trimmed) === 1) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $trimmed) ?? '';

        return $digits === '' ? null : $digits;
    }

    /**
     * Validate the shape of a STIR.
     *
     * @return array{valid: bool, normalized: string|null, formatted: string|null, reason: string|null}
     */
    public static function check(string $input): array
    {
        $digits = self::digits($input);

        if ($digits === null) {
            return self::fail('A STIR contains digits only (spaces and hyphens are ignored).');
        }

        $length = strlen($digits);

        if ($length !== self::LENGTH) {
            return self::fail(sprintf(
                'A STIR is %d digits; this has %d. Check for a missing or extra digit.',
                self::LENGTH,
                $length,
            ));
        }

        // An all-zero or all-same number is never issued and is the usual shape of a
        // placeholder left in a form.
        if (preg_match('/^(\d)\1{8}$/', $digits) === 1) {
            return self::fail('All nine digits are identical — this looks like a placeholder.');
        }

        return [
            'valid' => true,
            'normalized' => $digits,
            'formatted' => self::format($digits),
            'reason' => null,
        ];
    }

    /** `123 456 789` — the grouping used on Uzbek invoices. */
    public static function format(string $digits): string
    {
        return implode(' ', str_split($digits, 3));
    }

    /**
     * @return array{valid: false, normalized: null, formatted: null, reason: string}
     */
    private static function fail(string $reason): array
    {
        return ['valid' => false, 'normalized' => null, 'formatted' => null, 'reason' => $reason];
    }
}
