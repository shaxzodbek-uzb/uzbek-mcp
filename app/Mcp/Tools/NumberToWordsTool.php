<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Support\Uzbek\NumberConverter;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('number-to-words')]
#[Title('Uzbek number to words')]
#[Description(
    'Spell out an integer in written Uzbek (e.g. 1250 → "bir ming ikki yuz ellik"). Useful for sum-in-words on '
    .'invoices and documents. Optionally append a currency unit such as "soʻm" and render the result in the '
    .'Cyrillic script.'
)]
#[IsReadOnly]
class NumberToWordsTool extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'number' => ['required', 'integer'],
            'currency' => ['sometimes', 'nullable', 'string', 'max:30'],
            'script' => ['sometimes', 'in:latin,cyrillic'],
        ], [
            'number.required' => 'Provide the integer to spell out in the "number" argument.',
            'number.integer' => 'number must be a whole number (decimals are not supported).',
            'script.in' => 'script must be "latin" or "cyrillic".',
        ]);

        $words = NumberConverter::toWords(
            (int) $validated['number'],
            $validated['currency'] ?? null,
            $validated['script'] ?? 'latin',
        );

        return Response::text($words);
    }

    /**
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'number' => $schema->integer()
                ->description('The integer to spell out (may be negative).')
                ->required(),

            'currency' => $schema->string()
                ->description('Optional currency/unit word appended to the result, e.g. "soʻm".'),

            'script' => $schema->string()
                ->enum(['latin', 'cyrillic'])
                ->description('Output script. Defaults to latin.')
                ->default('latin'),
        ];
    }
}
