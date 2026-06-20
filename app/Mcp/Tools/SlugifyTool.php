<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Support\Uzbek\TextHelper;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('slugify')]
#[Title('Uzbek slug generator')]
#[Description(
    'Generate an ASCII URL slug from Uzbek text in either script (e.g. "Oʻzbekiston Respublikasi" → '
    .'"ozbekiston-respublikasi"). Cyrillic is transliterated to Latin, the Oʻ/Gʻ mark and tutuq belgisi are '
    .'stripped, and the result is lower-cased and hyphenated.'
)]
#[IsReadOnly]
class SlugifyTool extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'text' => ['required', 'string', 'max:20000'],
            'separator' => ['sometimes', 'string', 'max:1'],
        ], [
            'text.required' => 'Provide the Uzbek text to slugify in the "text" argument.',
        ]);

        $slug = TextHelper::slugify(
            $validated['text'],
            $validated['separator'] ?? '-',
        );

        return Response::text($slug);
    }

    /**
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'text' => $schema->string()
                ->description('The Uzbek text to slugify.')
                ->required(),

            'separator' => $schema->string()
                ->description('Word separator character. Defaults to "-".')
                ->default('-'),
        ];
    }
}
