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

#[Name('normalize-text')]
#[Title('Normalize Uzbek text')]
#[Description(
    'Canonicalise Uzbek Latin text: fixes apostrophes so Oʻ/Gʻ use U+02BB and the tutuq belgisi uses U+02BC, '
    .'normalises Unicode to NFC, and (optionally) collapses repeated whitespace. Use this to clean text typed '
    .'with straight quotes or backticks before storing or comparing it.'
)]
#[IsReadOnly]
class NormalizeTextTool extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'text' => ['required', 'string', 'max:20000'],
            'collapse_whitespace' => ['sometimes', 'boolean'],
        ], [
            'text.required' => 'Provide the Uzbek text to normalize in the "text" argument.',
        ]);

        $normalized = TextHelper::normalize(
            $validated['text'],
            $validated['collapse_whitespace'] ?? true,
        );

        return Response::text($normalized);
    }

    /**
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'text' => $schema->string()
                ->description('The Uzbek text to normalize.')
                ->required(),

            'collapse_whitespace' => $schema->boolean()
                ->description('Collapse runs of whitespace into single spaces and trim. Defaults to true.')
                ->default(true),
        ];
    }
}
