<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\Uzbek\TextHelper;
use PHPUnit\Framework\TestCase;

class TextHelperTest extends TestCase
{
    public function test_normalize_fixes_apostrophes(): void
    {
        // o'/g' apostrophes become U+02BB; tutuq belgisi becomes U+02BC.
        $this->assertSame("o\u{02BB}zbek", TextHelper::normalize("o'zbek"));
        $this->assertSame("g\u{02BB}alaba", TextHelper::normalize('g`alaba'));
        $this->assertSame("san\u{02BC}at", TextHelper::normalize("san'at"));
    }

    public function test_normalize_collapses_whitespace(): void
    {
        $this->assertSame('salom dunyo', TextHelper::normalize("  salom   dunyo \n"));
        $this->assertSame('salom   dunyo', TextHelper::normalize('salom   dunyo', false));
    }

    public function test_slugify_latin(): void
    {
        $this->assertSame(
            'ozbekiston-respublikasi',
            TextHelper::slugify("O\u{02BB}zbekiston Respublikasi")
        );
        $this->assertSame('sanat', TextHelper::slugify("san\u{02BC}at"));
    }

    public function test_slugify_cyrillic(): void
    {
        $this->assertSame('toshkent-shahri', TextHelper::slugify('Тошкент шаҳри'));
    }

    public function test_slugify_custom_separator(): void
    {
        $this->assertSame('salom_dunyo', TextHelper::slugify('Salom dunyo', '_'));
    }
}
