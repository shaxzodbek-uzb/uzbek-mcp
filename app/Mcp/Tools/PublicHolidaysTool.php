<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Support\Uzbek\Holidays;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Carbon;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('public-holidays')]
#[Title('Uzbekistan public holidays')]
#[Description(
    'List the official public holidays of Uzbekistan for a given year (Uzbek and English names, ISO dates). '
    .'The eight fixed-date holidays are returned for any year; the two Islamic holidays (Ramazon hayit, Qurbon '
    .'hayit) follow the lunar calendar and are only included for years present in the built-in decree table.'
)]
#[IsReadOnly]
class PublicHolidaysTool extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'year' => ['sometimes', 'integer', 'min:1991', 'max:2100'],
        ], [
            'year.integer' => 'year must be a 4-digit integer.',
        ]);

        $year = (int) ($validated['year'] ?? Carbon::now('Asia/Tashkent')->year);

        $payload = [
            'year' => $year,
            'lunar_holidays_known' => Holidays::hasLunar($year),
            'holidays' => Holidays::forYear($year),
        ];

        if (! $payload['lunar_holidays_known']) {
            $payload['note'] = 'Ramazon hayit and Qurbon hayit are set by annual decree and are not available '
                .'for this year; only the eight fixed-date holidays are listed.';
        }

        return Response::json($payload);
    }

    /**
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'year' => $schema->integer()
                ->description('The 4-digit year. Defaults to the current year in Asia/Tashkent.')
                ->min(1991)
                ->max(2100),
        ];
    }
}
