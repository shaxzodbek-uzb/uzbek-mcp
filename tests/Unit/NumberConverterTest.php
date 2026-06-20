<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\Uzbek\NumberConverter;
use PHPUnit\Framework\TestCase;

class NumberConverterTest extends TestCase
{
    public function test_basic_numbers(): void
    {
        $this->assertSame('nol', NumberConverter::toWords(0));
        $this->assertSame('bir', NumberConverter::toWords(1));
        $this->assertSame('yigirma bir', NumberConverter::toWords(21));
        $this->assertSame("o\u{02BB}n", NumberConverter::toWords(10));
        $this->assertSame("o\u{02BB}n besh", NumberConverter::toWords(15));
        $this->assertSame('bir yuz', NumberConverter::toWords(100));
        $this->assertSame('uch yuz besh', NumberConverter::toWords(305));
    }

    public function test_thousands_and_millions(): void
    {
        $this->assertSame('bir ming', NumberConverter::toWords(1000));
        $this->assertSame('bir ming ellik', NumberConverter::toWords(1050));
        $this->assertSame('bir ming ikki yuz ellik', NumberConverter::toWords(1250));
        $this->assertSame(
            "to\u{02BB}qqiz yuz to\u{02BB}qson to\u{02BB}qqiz ming to\u{02BB}qqiz yuz to\u{02BB}qson to\u{02BB}qqiz",
            NumberConverter::toWords(999999)
        );
        $this->assertSame(
            "bir million ikki yuz o\u{02BB}ttiz to\u{02BB}rt ming besh yuz oltmish yetti",
            NumberConverter::toWords(1234567)
        );
    }

    public function test_skips_empty_groups(): void
    {
        $this->assertSame('bir million', NumberConverter::toWords(1000000));
        $this->assertSame('bir milliard', NumberConverter::toWords(1000000000));
    }

    public function test_negative(): void
    {
        $this->assertSame('minus besh', NumberConverter::toWords(-5));
    }

    public function test_currency_suffix(): void
    {
        $this->assertSame(
            "bir ming ikki yuz ellik so\u{02BB}m",
            NumberConverter::toWords(1250, "so\u{02BB}m")
        );
    }

    public function test_cyrillic_script(): void
    {
        $this->assertSame('бир минг икки юз эллик', NumberConverter::toWords(1250, null, 'cyrillic'));
    }
}
