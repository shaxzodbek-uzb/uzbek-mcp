<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\Uzbek\Transliterator;
use PHPUnit\Framework\TestCase;

class TransliteratorTest extends TestCase
{
    public function test_latin_to_cyrillic_special_letters(): void
    {
        $this->assertSame("\u{040E}збекистон", Transliterator::toCyrillic("O\u{02BB}zbekiston"));
        $this->assertSame("\u{0492}алаба", Transliterator::toCyrillic("G\u{02BB}alaba"));
        $this->assertSame('Шаҳар', Transliterator::toCyrillic('Shahar'));
        $this->assertSame('чиройли', Transliterator::toCyrillic('chiroyli'));
    }

    public function test_latin_to_cyrillic_handles_case(): void
    {
        $this->assertSame('САМАРҚАНД', Transliterator::toCyrillic('SAMARQAND'));
        $this->assertSame('Тошкент', Transliterator::toCyrillic('Toshkent'));
    }

    public function test_latin_e_is_positional(): void
    {
        // Word-initial e -> э, otherwise -> е.
        $this->assertSame('Эшик', Transliterator::toCyrillic('Eshik'));
        $this->assertSame('кел', Transliterator::toCyrillic('kel'));
        // ye digraph -> е.
        $this->assertSame('ер', Transliterator::toCyrillic('yer'));
        $this->assertSame('Европа', Transliterator::toCyrillic('Yevropa'));
    }

    public function test_tutuq_belgisi_maps_to_hard_sign(): void
    {
        $this->assertSame('санъат', Transliterator::toCyrillic("san\u{02BC}at"));
        // Straight ASCII apostrophe is accepted too.
        $this->assertSame('санъат', Transliterator::toCyrillic("san'at"));
    }

    public function test_cyrillic_to_latin_special_letters(): void
    {
        $this->assertSame("O\u{02BB}zbekiston", Transliterator::toLatin("\u{040E}збекистон"));
        $this->assertSame("G\u{02BB}alaba", Transliterator::toLatin("\u{0492}алаба"));
        $this->assertSame('SHAHAR', Transliterator::toLatin('ШАҲАР'));
        $this->assertSame('Shahar', Transliterator::toLatin('Шаҳар'));
    }

    public function test_cyrillic_e_is_positional(): void
    {
        $this->assertSame('yer', Transliterator::toLatin('ер'));
        $this->assertSame('Yevropa', Transliterator::toLatin('Европа'));
        $this->assertSame('kel', Transliterator::toLatin('кел'));
    }

    public function test_cyrillic_ts_is_positional(): void
    {
        // ц after a vowel -> ts, otherwise -> s.
        $this->assertSame('litsey', Transliterator::toLatin('лицей'));
        $this->assertSame('sirk', Transliterator::toLatin('цирк'));
    }

    public function test_round_trip_for_well_formed_text(): void
    {
        foreach (["O\u{02BB}zbekiston", 'Toshkent', 'Shahar', "Farg\u{02BB}ona"] as $word) {
            $this->assertSame($word, Transliterator::toLatin(Transliterator::toCyrillic($word)));
        }
    }

    public function test_auto_detection(): void
    {
        $latin = Transliterator::transliterate('Salom dunyo');
        $this->assertSame('to_cyrillic', $latin['direction']);
        $this->assertSame('Салом дунё', $latin['result']);

        $cyrillic = Transliterator::transliterate('Салом дунё');
        $this->assertSame('to_latin', $cyrillic['direction']);
        $this->assertSame('Salom dunyo', $cyrillic['result']);
    }

    public function test_non_letters_pass_through(): void
    {
        $this->assertSame('Salom, dunyo! 123', Transliterator::toLatin('Салом, дунё! 123'));
    }
}
