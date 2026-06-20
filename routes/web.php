<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/', static fn (): array => [
    'name' => 'Uzbek MCP',
    'description' => 'The Uzbek language MCP server (transliteration, number-to-words, '
        .'normalization, slugify) plus live Uzbekistan data (CBU rates, holidays, weather).',
    'mcp_endpoint' => url('/mcp/uzbek'),
    'docs' => 'https://github.com/shaxzodbek-uzb/uzbek-mcp',
]);
