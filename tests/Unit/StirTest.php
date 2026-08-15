<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\Uzbek\Stir;
use PHPUnit\Framework\TestCase;

class StirTest extends TestCase
{
    public function test_accepts_nine_digits_in_any_common_spacing(): void
    {
        foreach (['123456789', '123 456 789', '123-456-789', '  123456789  '] as $input) {
            $result = Stir::check($input);
            $this->assertTrue($result['valid'], "should accept: {$input}");
            $this->assertSame('123456789', $result['normalized']);
            $this->assertSame('123 456 789', $result['formatted']);
            $this->assertNull($result['reason']);
        }
    }

    public function test_rejects_the_wrong_length_with_a_useful_reason(): void
    {
        $short = Stir::check('12345678');
        $this->assertFalse($short['valid']);
        $this->assertStringContainsString('9 digits', $short['reason']);
        $this->assertStringContainsString('8', $short['reason']);

        $this->assertFalse(Stir::check('1234567890')['valid']);
    }

    public function test_rejects_letters_rather_than_stripping_them(): void
    {
        // Stripping would turn "12345678O" into an 8-digit number and report the
        // wrong problem; the real problem is the letter.
        $result = Stir::check('12345678O');
        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('digits only', $result['reason']);
    }

    public function test_rejects_a_placeholder(): void
    {
        $result = Stir::check('000000000');
        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('placeholder', $result['reason']);

        $this->assertFalse(Stir::check('111111111')['valid']);
    }

    public function test_rejects_empty_input(): void
    {
        $this->assertFalse(Stir::check('')['valid']);
        $this->assertFalse(Stir::check('   ')['valid']);
    }

    /**
     * The contract is deliberately narrow: any 9 digits pass. This test exists so
     * that adding a checksum later is a conscious, breaking decision rather than a
     * quiet one — a wrong checksum rejects real taxpayers.
     */
    public function test_does_not_apply_a_checksum(): void
    {
        foreach (['123456789', '987654321', '100000001'] as $arbitrary) {
            $this->assertTrue(Stir::check($arbitrary)['valid'], "should accept: {$arbitrary}");
        }
    }
}
