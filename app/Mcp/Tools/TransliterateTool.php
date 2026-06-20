<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Support\Uzbek\Transliterator;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('transliterate')]
#[Title('Transliterate Uzbek (Latin ↔ Cyrillic)')]
#[Description(
    'Transliterate Uzbek text between the Latin and Cyrillic scripts using the official 1995 national alphabet '
    .'(Oʻ/Gʻ written with U+02BB, the tutuq belgisi with U+02BC). Set direction to "auto" to detect the source '
    .'script automatically, or force "to_cyrillic" / "to_latin".'
)]
#[IsReadOnly]
class TransliterateTool extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'text' => ['required', 'string', 'max:20000'],
            'direction' => ['sometimes', 'in:auto,to_cyrillic,to_latin'],
        ], [
            'text.required' => 'Provide the Uzbek text to transliterate in the "text" argument.',
            'direction.in' => 'direction must be one of: auto, to_cyrillic, to_latin.',
        ]);

        $result = Transliterator::transliterate(
            $validated['text'],
            $validated['direction'] ?? 'auto',
        );

        return Response::text($result['result']);
    }

    /**
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'text' => $schema->string()
                ->description('The Uzbek text to transliterate.')
                ->required(),

            'direction' => $schema->string()
                ->enum(['auto', 'to_cyrillic', 'to_latin'])
                ->description('Conversion direction; "auto" detects the source script. Defaults to auto.')
                ->default('auto'),
        ];
    }
}
