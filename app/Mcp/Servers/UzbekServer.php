<?php

declare(strict_types=1);

namespace App\Mcp\Servers;

use App\Mcp\Tools\CurrencyRateTool;
use App\Mcp\Tools\DateToWordsTool;
use App\Mcp\Tools\NormalizeTextTool;
use App\Mcp\Tools\NumberToWordsTool;
use App\Mcp\Tools\PhoneNormalizeTool;
use App\Mcp\Tools\PublicHolidaysTool;
use App\Mcp\Tools\SlugifyTool;
use App\Mcp\Tools\StirValidateTool;
use App\Mcp\Tools\TransliterateTool;
use App\Mcp\Tools\WeatherTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;
use Laravel\Mcp\Server\Tool;

#[Name('Uzbek MCP')]
#[Version('0.1.0')]
#[Instructions(<<<'MARKDOWN'
    The Uzbek MCP server gives AI agents native Uzbek-language abilities plus a few handy Uzbekistan data feeds.

    Language tools (offline, deterministic, official 1995 alphabet — Oʻ/Gʻ use U+02BB, tutuq belgisi U+02BC):
    - transliterate: convert between the Latin and Cyrillic scripts (auto-detects direction).
    - normalize-text: fix apostrophes/quotes and Unicode form in Uzbek text.
    - number-to-words: spell an integer in written Uzbek (great for sum-in-words on invoices).
    - slugify: build an ASCII URL slug from Uzbek text in either script.
    - date-to-words: spell an ISO date in written Uzbek, for contracts and official documents.

    Uzbekistan format tools (offline, deterministic):
    - phone-normalize: parse any common Uzbek phone format into E.164 plus the operator/region.
    - stir-validate: check the SHAPE of a taxpayer number (9 digits). It cannot confirm a STIR is
      issued or whose it is — only the State Tax Committee register can. Never refuse a customer
      on this check alone.

    Uzbekistan data tools (live, no API key):
    - currency-rate: official CBU exchange rate vs the soʻm, with optional date and amount conversion.
    - public-holidays: official Uzbek public holidays for a year.
    - weather: current weather + today's forecast for an Uzbek city (Open-Meteo).

    Prefer the language tools for any Uzbek text handling rather than guessing transliteration yourself.
    MARKDOWN)]
class UzbekServer extends Server
{
    /**
     * @var array<int, class-string<Tool>>
     */
    protected array $tools = [
        TransliterateTool::class,
        NormalizeTextTool::class,
        NumberToWordsTool::class,
        SlugifyTool::class,
        DateToWordsTool::class,
        PhoneNormalizeTool::class,
        StirValidateTool::class,
        CurrencyRateTool::class,
        PublicHolidaysTool::class,
        WeatherTool::class,
    ];

    /**
     * @var array<int, class-string>
     */
    protected array $resources = [];

    /**
     * @var array<int, class-string>
     */
    protected array $prompts = [];
}
