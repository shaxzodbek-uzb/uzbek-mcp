<?php

declare(strict_types=1);

use App\Mcp\Servers\UzbekServer;
use Laravel\Mcp\Facades\Mcp;

/*
|--------------------------------------------------------------------------
| MCP Servers
|--------------------------------------------------------------------------
|
| The Uzbek MCP server is exposed two ways:
|
|  - Local (stdio): run by an MCP client via `php artisan mcp:start uzbek`.
|    This is what Claude Desktop / Claude Code use.
|  - Web (HTTP): available at POST /mcp/uzbek for remote clients. It is rate
|    limited; add authentication middleware before exposing it publicly.
|
*/

Mcp::local('uzbek', UzbekServer::class);

Mcp::web('/mcp/uzbek', UzbekServer::class)
    ->middleware(['throttle:60,1']);
