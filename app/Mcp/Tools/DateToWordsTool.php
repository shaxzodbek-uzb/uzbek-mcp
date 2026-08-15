<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Support\Uzbek\DateWords;
use DateTimeImmutable;
use Exception;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('date-to-words')]
#[Title('Uzbek date in words')]
#[Description(
    'Spell an ISO date in written Uzbek, e.g. 2026-08-15 becomes "ikki ming yigirma oltinchi yil '
    .'o\u{02BB}n beshinchi avgust". Useful for contracts, invoices and official documents where the date '
    .'must appear in words. Optionally prepend the weekday or render in the Cyrillic script. '
    .'Offline and deterministic.'
)]
#[IsReadOnly]
class DateToWordsTool extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'date' => ['required', 'string', 'date_format:Y-m-d'],
            'weekday' => ['sometimes', 'boolean'],
            'script' => ['sometimes', 'in:latin,cyrillic'],
        ], [
            'date.required' => 'Provide the date in the "date" argument.',
            'date.date_format' => 'date must be an ISO date, e.g. 2026-08-15.',
            'script.in' => 'script must be "latin" or "cyrillic".',
        ]);

        try {
            $date = new DateTimeImmutable($validated['date']);
        } catch (Exception) {
            return Response::error('date must be an ISO date, e.g. 2026-08-15.');
        }

        return Response::text(DateWords::spell(
            $date,
            (bool) ($validated['weekday'] ?? false),
            $validated['script'] ?? 'latin',
        ));
    }

    /**
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'date' => $schema->string()
                ->description('The date to spell, as YYYY-MM-DD.')
                ->required(),

            'weekday' => $schema->boolean()
                ->description('Prepend the Uzbek weekday name. Defaults to false.')
                ->default(false),

            'script' => $schema->string()
                ->enum(['latin', 'cyrillic'])
                ->description('Output script. Defaults to latin.')
                ->default('latin'),
        ];
    }
}
