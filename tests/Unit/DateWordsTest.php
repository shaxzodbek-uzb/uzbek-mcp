<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\Uzbek\DateWords;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class DateWordsTest extends TestCase
{
    public function test_spells_a_full_date(): void
    {
        $this->assertSame(
            "ikki ming yigirma oltinchi yil o\u{02BB}n beshinchi avgust",
            DateWords::spell(new DateTimeImmutable('2026-08-15')),
        );
    }

    public function test_prepends_the_weekday_when_asked(): void
    {
        // 2026-08-15 is a Saturday.
        $this->assertStringStartsWith('shanba, ', DateWords::spell(new DateTimeImmutable('2026-08-15'), true));
    }

    public function test_irregular_ordinals(): void
    {
        $this->assertSame('birinchi', DateWords::ordinal(1));
        $this->assertSame('ikkinchi', DateWords::ordinal(2));
        $this->assertSame('uchinchi', DateWords::ordinal(3));
        $this->assertSame("to\u{02BB}rtinchi", DateWords::ordinal(4));
    }

    public function test_suffix_follows_the_last_word_not_the_digit(): void
    {
        // -nchi after a vowel, -inchi after a consonant.
        $this->assertSame('beshinchi', DateWords::ordinal(5));       // besh -> consonant
        $this->assertSame('oltinchi', DateWords::ordinal(6));        // olti -> vowel
        $this->assertSame('yettinchi', DateWords::ordinal(7));       // yetti -> vowel
        $this->assertSame('sakkizinchi', DateWords::ordinal(8));     // sakkiz -> consonant
        $this->assertSame("to\u{02BB}qqizinchi", DateWords::ordinal(9));
        $this->assertSame("o\u{02BB}ninchi", DateWords::ordinal(10)); // oʻn -> consonant n
    }

    public function test_only_the_last_word_takes_the_suffix(): void
    {
        // "yigirma birinchi", never "yigirmanchi birinchi".
        $this->assertSame('yigirma birinchi', DateWords::ordinal(21));
        $this->assertSame("o\u{02BB}n beshinchi", DateWords::ordinal(15));
        $this->assertSame('bir ming ikki yuz ellikinchi', DateWords::ordinal(1250));
    }

    public function test_all_twelve_months_are_named(): void
    {
        foreach (range(1, 12) as $month) {
            $this->assertNotNull(DateWords::month($month), "missing month {$month}");
        }
        $this->assertSame('yanvar', DateWords::month(1));
        $this->assertSame('avgust', DateWords::month(8));
        $this->assertSame('dekabr', DateWords::month(12));
        $this->assertNull(DateWords::month(13));
    }

    public function test_all_seven_weekdays_are_named(): void
    {
        foreach (range(1, 7) as $day) {
            $this->assertNotNull(DateWords::weekday($day), "missing weekday {$day}");
        }
        $this->assertSame('dushanba', DateWords::weekday(1));
        $this->assertSame('yakshanba', DateWords::weekday(7));
    }

    public function test_cyrillic_script(): void
    {
        $latin = DateWords::spell(new DateTimeImmutable('2026-08-15'));
        $cyrillic = DateWords::spell(new DateTimeImmutable('2026-08-15'), false, 'cyrillic');

        $this->assertNotSame($latin, $cyrillic);
        $this->assertMatchesRegularExpression('/\p{Cyrillic}/u', $cyrillic);
    }

    public function test_handles_the_first_and_last_day_of_a_year(): void
    {
        $this->assertStringEndsWith('birinchi yanvar', DateWords::spell(new DateTimeImmutable('2026-01-01')));
        $this->assertStringEndsWith('dekabr', DateWords::spell(new DateTimeImmutable('2026-12-31')));
    }
}
