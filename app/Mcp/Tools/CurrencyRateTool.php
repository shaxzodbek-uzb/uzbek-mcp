<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Http;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Throwable;

#[Name('currency-rate')]
#[Title('Uzbekistan currency rate (CBU)')]
#[Description(
    'Get the official exchange rate of a currency against the Uzbek soʻm from the Central Bank of Uzbekistan '
    .'(cbu.uz), optionally for a past date, and optionally convert an amount of that currency into soʻm. '
    .'Returns the rate, daily change and the date the rate applies to.'
)]
#[IsReadOnly]
class CurrencyRateTool extends Tool
{
    private const BASE_URL = 'https://cbu.uz/uz/arkhiv-kursov-valyut/json';

    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'currency' => ['sometimes', 'string', 'regex:/^[A-Za-z]{3}$/'],
            'date' => ['sometimes', 'nullable', 'date_format:Y-m-d'],
            'amount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
        ], [
            'currency.regex' => 'currency must be a 3-letter ISO code such as USD, EUR or RUB.',
            'date.date_format' => 'date must be in YYYY-MM-DD format.',
        ]);

        $currency = strtoupper($validated['currency'] ?? 'USD');
        $date = $validated['date'] ?? null;
        $amount = isset($validated['amount']) ? (float) $validated['amount'] : null;

        $url = self::BASE_URL.'/'.$currency.'/'.($date !== null ? $date.'/' : '');

        try {
            $http = Http::acceptJson()->timeout(15)->retry(2, 200)->get($url);
        } catch (Throwable $e) {
            return Response::error("Could not reach the CBU rate service: {$e->getMessage()}");
        }

        if (! $http->successful()) {
            return Response::error("CBU rate service returned HTTP {$http->status()}.");
        }

        $row = $http->json(0);

        if (! is_array($row) || ! isset($row['Rate'])) {
            return Response::error("No rate found for currency [{$currency}]".($date !== null ? " on {$date}." : '.'));
        }

        $rate = (float) $row['Rate'];
        $nominal = (float) ($row['Nominal'] ?? 1) ?: 1.0;

        $payload = [
            'currency' => $row['Ccy'] ?? $currency,
            'name_uz' => $row['CcyNm_UZ'] ?? null,
            'name_en' => $row['CcyNm_EN'] ?? null,
            'nominal' => (int) $nominal,
            'rate' => $rate,
            'diff' => isset($row['Diff']) ? (float) $row['Diff'] : null,
            'date' => $row['Date'] ?? null,
            'unit' => "so\u{02BB}m",
        ];

        if ($amount !== null) {
            $payload['amount'] = $amount;
            $payload['converted_sum'] = round($amount * $rate / $nominal, 2);
        }

        return Response::json($payload);
    }

    /**
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'currency' => $schema->string()
                ->description('3-letter ISO currency code (e.g. USD, EUR, RUB). Defaults to USD.')
                ->default('USD'),

            'date' => $schema->string()
                ->description('Optional date in YYYY-MM-DD format for a historical rate. Defaults to the latest rate.'),

            'amount' => $schema->number()
                ->description('Optional amount of the currency to convert into soʻm.'),
        ];
    }
}
