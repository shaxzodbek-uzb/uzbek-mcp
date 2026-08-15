<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\Uzbek\PhoneNumber;
use PHPUnit\Framework\TestCase;

class PhoneNumberTest extends TestCase
{
    /**
     * Every common way an Uzbek number is written must reduce to the same
     * 9 national digits — that equivalence is the whole point of the tool.
     */
    public function test_all_common_input_forms_reduce_to_the_same_national_number(): void
    {
        foreach ([
            '+998901234567',
            '+998 90 123 45 67',
            '998901234567',
            '8 90 123-45-67',
            '890 123 45 67',
            '901234567',
            '(90) 123-45-67',
            "  +998\u{00A0}90 123 45 67  ",
        ] as $input) {
            $this->assertSame('901234567', PhoneNumber::national($input), "failed for: {$input}");
        }
    }

    public function test_formats(): void
    {
        $this->assertSame('+998901234567', PhoneNumber::e164('901234567'));
        $this->assertSame('+998 90 123 45 67', PhoneNumber::international('901234567'));
        $this->assertSame('(90) 123-45-67', PhoneNumber::local('901234567'));
    }

    public function test_lookup_reports_operator_and_kind(): void
    {
        $mobile = PhoneNumber::lookup('901234567');
        $this->assertSame('90', $mobile['prefix']);
        $this->assertSame('Beeline', $mobile['operator']);
        $this->assertSame('mobile', $mobile['kind']);

        $landline = PhoneNumber::lookup('712001122');
        $this->assertSame('landline', $landline['kind']);
        $this->assertStringContainsString('Toshkent', (string) $landline['operator']);
    }

    public function test_an_unallocated_prefix_parses_but_is_flagged(): void
    {
        // Shape is right, range is not one we know: parse it, but say so rather
        // than inventing an operator.
        $this->assertSame('121234567', PhoneNumber::national('121234567'));
        $this->assertFalse(PhoneNumber::hasKnownPrefix('121234567'));
        $this->assertNull(PhoneNumber::lookup('121234567')['operator']);
        $this->assertSame('unknown', PhoneNumber::lookup('121234567')['kind']);
    }

    public function test_rejects_input_that_is_not_an_uzbek_number(): void
    {
        foreach ([
            '',
            'not a phone',
            '12345',                 // too short
            '9012345678901234',      // too long
            '+1 415 555 0132',       // wrong country
            '1234567890',            // 10 digits, no trunk prefix
        ] as $input) {
            $this->assertNull(PhoneNumber::national($input), "should reject: {$input}");
        }
    }

    public function test_a_ten_digit_number_starting_with_eight_needs_a_known_prefix(): void
    {
        // 8 + known prefix is the domestic trunk form.
        $this->assertSame('901234567', PhoneNumber::national('8901234567'));
        // 8 + unknown prefix is more likely a foreign number than a trunk call,
        // so it is refused rather than silently reinterpreted.
        $this->assertNull(PhoneNumber::national('8121234567'));
    }
}
