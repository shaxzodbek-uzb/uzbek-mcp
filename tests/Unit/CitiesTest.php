<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\Uzbek\Cities;
use PHPUnit\Framework\TestCase;

class CitiesTest extends TestCase
{
    public function test_finds_city_by_canonical_name(): void
    {
        $city = Cities::find('Toshkent');
        $this->assertNotNull($city);
        $this->assertSame('Toshkent', $city['name']);
        $this->assertEqualsWithDelta(41.2995, $city['lat'], 0.01);
    }

    public function test_finds_city_by_english_alias(): void
    {
        $this->assertSame('Samarqand', Cities::find('Samarkand')['name']);
        $this->assertSame('Buxoro', Cities::find('bukhara')['name']);
        $this->assertSame('Xiva', Cities::find('Khiva')['name']);
    }

    public function test_finds_city_by_cyrillic_name(): void
    {
        $this->assertSame('Toshkent', Cities::find('Тошкент')['name']);
        $this->assertSame('Andijon', Cities::find('Андижон')['name']);
    }

    public function test_finds_city_with_apostrophe_variants(): void
    {
        $this->assertSame("Farg\u{02BB}ona", Cities::find("Farg'ona")['name']);
        $this->assertSame("Farg\u{02BB}ona", Cities::find('Fergana')['name']);
    }

    public function test_unknown_city_returns_null(): void
    {
        $this->assertNull(Cities::find('Atlantis'));
    }
}
