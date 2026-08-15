<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Support\Uzbek\Stir;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('stir-validate')]
#[Title('Check the shape of an Uzbek STIR')]
#[Description(
    'Check that an Uzbek taxpayer number (STIR / INN) has the right shape — exactly 9 digits — and '
    .'return it normalised and grouped as it appears on an invoice. This validates the SHAPE ONLY: it '
    .'catches a missing digit, a stray character or a placeholder, and it does NOT verify that the '
    .'number is issued or belongs to a given company. Only the State Tax Committee register can tell '
    .'you that; never refuse a customer on this check alone.'
)]
#[IsReadOnly]
class StirValidateTool extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'stir' => ['required', 'string', 'max:40'],
        ], [
            'stir.required' => 'Provide the taxpayer number in the "stir" argument.',
        ]);

        $result = Stir::check($validated['stir']);

        return Response::json([
            'input' => $validated['stir'],
            'valid_shape' => $result['valid'],
            'normalized' => $result['normalized'],
            'formatted' => $result['formatted'],
            'reason' => $result['reason'],
            'note' => 'Shape check only — this does not confirm the STIR is issued or whose it is.',
        ]);
    }

    /**
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'stir' => $schema->string()
                ->description('The taxpayer number, with or without spaces/hyphens.')
                ->required(),
        ];
    }
}
