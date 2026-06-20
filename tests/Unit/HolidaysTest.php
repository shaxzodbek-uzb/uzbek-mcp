<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\Uzbek\Holidays;
use PHPUnit\Framework\TestCase;

class HolidaysTest extends TestCase
{
    public function test_fixed_holidays_present_for_any_year(): void
    {
        $holidays = Holidays::forYear(2099);
        $dates = array_column($holidays, 'date');

        $this->assertContains('2099-01-01', $dates);
        $this->assertContains('2099-09-01', $dates);
        $this->assertContains('2099-12-08', $dates);
        $this->assertCount(8, $holidays); // no lunar data for 2099
    }

    public function test_lunar_holidays_present_for_known_year(): void
    {
        $holidays = Holidays::forYear(2026);
        $dates = array_column($holidays, 'date');

        $this->assertTrue(Holidays::hasLunar(2026));
        $this->assertContains('2026-03-20', $dates); // Ramazon hayit
        $this->assertContains('2026-05-27', $dates); // Qurbon hayit
        $this->assertCount(10, $holidays);
    }

    public function test_holidays_are_sorted_by_date(): void
    {
        $dates = array_column(Holidays::forYear(2026), 'date');
        $sorted = $dates;
        sort($sorted);

        $this->assertSame($sorted, $dates);
    }

    public function test_names_include_independence_day(): void
    {
        $names = array_column(Holidays::forYear(2026), 'name_uz');
        $this->assertContains('Mustaqillik kuni', $names);
    }
}
