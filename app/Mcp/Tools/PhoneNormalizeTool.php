<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Support\Uzbek\PhoneNumber;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('phone-normalize')]
#[Title('Normalise an Uzbek phone number')]
#[Description(
    'Parse an Uzbek phone number written any of the usual ways (+998 90 123 45 67, 998901234567, '
    .'8 90 123-45-67, 90 123 45 67) and return it in E.164, international and local formats, plus the '
    .'operator or region the 2-digit prefix belongs to. Offline and deterministic. Number portability '
    .'means the operator names the allocated range, not necessarily the current carrier.'
)]
#[IsReadOnly]
class PhoneNormalizeTool extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:40'],
        ], [
            'phone.required' => 'Provide the phone number to normalise in the "phone" argument.',
        ]);

        $national = PhoneNumber::national($validated['phone']);

        if ($national === null) {
            return Response::error(
                'Could not read that as an Uzbek phone number. Expected 9 national digits, '
                .'optionally prefixed with +998, 998, or 8.'
            );
        }

        $lookup = PhoneNumber::lookup($national);

        return Response::json([
            'input' => $validated['phone'],
            'e164' => PhoneNumber::e164($national),
            'international' => PhoneNumber::international($national),
            'local' => PhoneNumber::local($national),
            'national' => $national,
            'prefix' => $lookup['prefix'],
            'operator' => $lookup['operator'],
            'kind' => $lookup['kind'],
            'known_prefix' => PhoneNumber::hasKnownPrefix($national),
        ]);
    }

    /**
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'phone' => $schema->string()
                ->description('The phone number, in any common Uzbek format.')
                ->required(),
        ];
    }
}
