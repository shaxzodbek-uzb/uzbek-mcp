<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mcp\Servers\UzbekServer;
use App\Mcp\Tools\CurrencyRateTool;
use App\Mcp\Tools\NormalizeTextTool;
use App\Mcp\Tools\NumberToWordsTool;
use App\Mcp\Tools\PublicHolidaysTool;
use App\Mcp\Tools\SlugifyTool;
use App\Mcp\Tools\TransliterateTool;
use App\Mcp\Tools\WeatherTool;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UzbekMcpServerTest extends TestCase
{
    public function test_transliterate_tool(): void
    {
        UzbekServer::tool(TransliterateTool::class, ['text' => "O\u{02BB}zbekiston"])
            ->assertOk()
            ->assertName('transliterate')
            ->assertSee("\u{040E}збекистон");
    }

    public function test_transliterate_requires_text(): void
    {
        UzbekServer::tool(TransliterateTool::class, [])
            ->assertHasErrors();
    }

    public function test_number_to_words_tool(): void
    {
        UzbekServer::tool(NumberToWordsTool::class, ['number' => 1250, 'currency' => "so\u{02BB}m"])
            ->assertOk()
            ->assertSee("bir ming ikki yuz ellik so\u{02BB}m");
    }

    public function test_normalize_text_tool(): void
    {
        UzbekServer::tool(NormalizeTextTool::class, ['text' => "o'zbek"])
            ->assertOk()
            ->assertSee("o\u{02BB}zbek");
    }

    public function test_slugify_tool(): void
    {
        UzbekServer::tool(SlugifyTool::class, ['text' => "O\u{02BB}zbekiston Respublikasi"])
            ->assertOk()
            ->assertSee('ozbekiston-respublikasi');
    }

    public function test_public_holidays_tool(): void
    {
        UzbekServer::tool(PublicHolidaysTool::class, ['year' => 2026])
            ->assertOk()
            ->assertSee('Mustaqillik kuni')
            ->assertSee('2026-09-01');
    }

    public function test_currency_rate_tool(): void
    {
        Http::fake([
            'cbu.uz/*' => Http::response([[
                'id' => 68,
                'Code' => '840',
                'Ccy' => 'USD',
                'CcyNm_UZ' => 'AQSH dollari',
                'CcyNm_EN' => 'US Dollar',
                'Nominal' => '1',
                'Rate' => '12085.56',
                'Diff' => '33.51',
                'Date' => '19.06.2026',
            ]], 200),
        ]);

        UzbekServer::tool(CurrencyRateTool::class, ['currency' => 'USD', 'amount' => 10])
            ->assertOk()
            ->assertSee('12085.56')
            ->assertSee('AQSH dollari')
            ->assertSee('120855.6'); // 10 * 12085.56
    }

    public function test_currency_rate_handles_missing_currency(): void
    {
        Http::fake(['cbu.uz/*' => Http::response([], 200)]);

        UzbekServer::tool(CurrencyRateTool::class, ['currency' => 'XyZ'])
            ->assertHasErrors();
    }

    public function test_weather_tool(): void
    {
        Http::fake([
            'api.open-meteo.com/*' => Http::response([
                'latitude' => 41.3,
                'longitude' => 69.24,
                'current' => [
                    'time' => '2026-06-20T11:00',
                    'temperature_2m' => 29.8,
                    'relative_humidity_2m' => 37,
                    'weather_code' => 1,
                    'wind_speed_10m' => 2.8,
                ],
                'daily' => [
                    'temperature_2m_max' => [31.6],
                    'temperature_2m_min' => [19.5],
                    'weather_code' => [1],
                ],
            ], 200),
        ]);

        UzbekServer::tool(WeatherTool::class, ['city' => 'Toshkent'])
            ->assertOk()
            ->assertSee('29.8')
            ->assertSee('Toshkent');
    }

    public function test_weather_unknown_city_is_geocoded(): void
    {
        Http::fake([
            'geocoding-api.open-meteo.com/*' => Http::response([
                'results' => [[
                    'name' => 'Zarafshan',
                    'latitude' => 41.57,
                    'longitude' => 64.2,
                    'country_code' => 'UZ',
                ]],
            ], 200),
            'api.open-meteo.com/*' => Http::response([
                'current' => ['temperature_2m' => 33.1, 'weather_code' => 0],
                'daily' => ['temperature_2m_max' => [35.0], 'temperature_2m_min' => [20.0], 'weather_code' => [0]],
            ], 200),
        ]);

        UzbekServer::tool(WeatherTool::class, ['city' => 'Zarafshan'])
            ->assertOk()
            ->assertSee('Zarafshan')
            ->assertSee('33.1');
    }
}
