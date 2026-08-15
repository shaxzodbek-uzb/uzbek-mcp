# Uzbek MCP 🇺🇿

> The first [Model Context Protocol](https://modelcontextprotocol.io) server for the **Uzbek language** — give Claude (and any MCP client) native Uzbek text abilities, plus a few handy live Uzbekistan data feeds.

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![PHP 8.3+](https://img.shields.io/badge/PHP-8.3%2B-777bb4.svg)](https://www.php.net/)
[![Built with Laravel MCP](https://img.shields.io/badge/Laravel-MCP-ff2d20.svg)](https://github.com/laravel/mcp)

LLMs are notoriously shaky at Uzbek — they mangle Latin ↔ Cyrillic transliteration, get the `oʻ`/`gʻ` apostrophes wrong, and can't spell out a number in Uzbek for an invoice. **Uzbek MCP** gives them deterministic, offline tools that get it right, following the official 1995 national alphabet. It also ships a small set of no-API-key Uzbekistan data tools (currency, holidays, weather).

There is already a healthy ecosystem of Uzbek *fintech / data* MCP servers (payments, e-invoicing, statistics). Uzbek MCP fills the empty **language** niche, and is the only one built on PHP / [Laravel MCP](https://github.com/laravel/mcp).

---

## Tools

| Tool | What it does | Example |
| --- | --- | --- |
| `transliterate` | Convert Uzbek between Latin and Cyrillic (auto-detects direction) | `Oʻzbekiston` ⇄ `Ўзбекистон` |
| `normalize-text` | Fix apostrophes (`oʻ`/`gʻ` → U+02BB, tutuq belgisi → U+02BC), Unicode → NFC, collapse whitespace | `o'zbek` → `oʻzbek` |
| `number-to-words` | Spell an integer in written Uzbek (great for sum-in-words on invoices) | `1250` → `bir ming ikki yuz ellik` |
| `slugify` | ASCII URL slug from Uzbek text in either script | `Oʻzbekiston Respublikasi` → `ozbekiston-respublikasi` |
| `date-to-words` | Spell an ISO date in written Uzbek (contracts, invoices) | `2026-08-15` → `ikki ming yigirma oltinchi yil oʻn beshinchi avgust` |
| `phone-normalize` | Parse any common Uzbek phone format → E.164 + operator/region | `8 90 123-45-67` → `+998901234567` (Beeline) |
| `stir-validate` | Check the **shape** of a taxpayer number (9 digits) | `123 456 789` → valid shape |
| `currency-rate` | Official CBU exchange rate vs the soʻm, with optional date + amount conversion | `USD ×100` → `1 208 556 soʻm` |
| `public-holidays` | Official Uzbek public holidays for a year (uz + en, ISO dates) | `2026` → 10 holidays |
| `weather` | Current weather + today's forecast for an Uzbek city (Open-Meteo) | `Samarqand` → `28.8°C` |

The language tools are **offline and deterministic** (no network, no keys). The data tools call public APIs ([cbu.uz](https://cbu.uz), [open-meteo.com](https://open-meteo.com)) and need **no API key**.

> **`stir-validate` checks the shape, not the taxpayer.** It catches a missing digit, a stray
> character, or a placeholder left in a form — the errors that actually happen when a number is
> copied off an invoice. It deliberately implements **no checksum**: published descriptions of the
> STIR check digit disagree with one another, and a wrong checksum in an invoicing path is worse
> than none, because it rejects a real business's tax number with a confident-sounding error.
> Whether a STIR is issued, and whose it is, can only be answered by the State Tax Committee
> register. Never refuse a customer on this check alone.

> **`phone-normalize` operator names describe the allocated range.** Uzbekistan has number
> portability, so the operator for a prefix is who the range was issued to, not necessarily who
> serves that subscriber today.

---

## Quick start

### Requirements

- PHP **8.3+** (with the `mbstring` extension; `intl` recommended)
- [Composer](https://getcomposer.org)

### Install

```bash
git clone https://github.com/shaxzodbek-uzb/uzbek-mcp.git
cd uzbek-mcp
composer install
cp .env.example .env
php artisan key:generate
```

That's it — the server runs over stdio with `php artisan mcp:start uzbek`.

### Connect to Claude Desktop

Add this to your `claude_desktop_config.json` (use the **absolute** path to `artisan`):

```json
{
  "mcpServers": {
    "uzbek": {
      "command": "php",
      "args": ["/absolute/path/to/uzbek-mcp/artisan", "mcp:start", "uzbek"]
    }
  }
}
```

Restart Claude Desktop and ask it, for example: *"Transliterate 'Oʻzbekiston Respublikasi' to Cyrillic"* or *"Spell out 1 250 000 soʻm in Uzbek"*.

### Connect to Claude Code

```bash
claude mcp add uzbek -- php /absolute/path/to/uzbek-mcp/artisan mcp:start uzbek
```

### Use it over HTTP

The same server is also exposed at `POST /mcp/uzbek` (rate-limited). Serve it with `php artisan serve` and point a remote MCP client at `http://127.0.0.1:8000/mcp/uzbek`. **Add authentication middleware before exposing this publicly** — see `routes/ai.php`.

### Inspect it interactively

```bash
php artisan mcp:inspector uzbek
```

---

## Examples

```jsonc
// transliterate
{ "text": "Salom dunyo" }                         → "Салом дунё"
{ "text": "Ўзбекистон", "direction": "to_latin" } → "Oʻzbekiston"

// number-to-words
{ "number": 1250 }                                → "bir ming ikki yuz ellik"
{ "number": 1250, "currency": "soʻm" }            → "bir ming ikki yuz ellik soʻm"
{ "number": 1250, "script": "cyrillic" }          → "бир минг икки юз эллик"

// normalize-text
{ "text": "o'zbek  tili" }                        → "oʻzbek tili"

// slugify
{ "text": "Toshkent shahri" }                     → "toshkent-shahri"

// currency-rate
{ "currency": "USD", "amount": 100 }              → { rate, converted_sum, date, ... }

// public-holidays
{ "year": 2026 }                                  → { holidays: [ … ] }

// weather
{ "city": "Buxoro" }                              → { current, today, condition, ... }
```

---

## About the transliteration

Uzbek MCP implements the **official 1995 Latin alphabet** (the 2018/2021 `Ó`/`Ǵ` diacritic reforms were never adopted into law). It is careful about the details most tools get wrong:

- `Oʻ` and `Gʻ` use **U+02BB** (modifier letter turned comma) — straight quotes, backticks and curly quotes on input are accepted and normalized.
- The **tutuq belgisi** (glottal stop, Cyrillic `ъ`) uses **U+02BC** (modifier letter apostrophe) — a *different* character from the `Oʻ`/`Gʻ` mark.
- Cyrillic `е` → `ye` word-initially / after a vowel / after `ъ`,`ь`, otherwise `e`.
- Cyrillic `ц` → `ts` after a vowel, otherwise `s`.

A few mappings are inherently lossy (Latin `e` ← `е`/`э`, `ц` → `ts`/`s`, `ь` is dropped), so a Cyrillic → Latin → Cyrillic round-trip is not guaranteed for every input, but well-formed text round-trips cleanly.

---

## Development

```bash
composer test              # run the PHPUnit suite (40 tests)
composer lint              # format with Laravel Pint
./vendor/bin/pint --test   # check formatting without changing files
```

The Uzbek logic lives in framework-agnostic classes under [`app/Support/Uzbek`](app/Support/Uzbek) (`Transliterator`, `NumberConverter`, `TextHelper`, `Holidays`, `Cities`, `WeatherCodes`); the thin MCP wrappers are in [`app/Mcp`](app/Mcp).

---

## Contributing

Contributions are very welcome — extra city coordinates, lunar-holiday years, transliteration edge cases, or new tools. See [CONTRIBUTING.md](CONTRIBUTING.md).

## License

[MIT](LICENSE) © Shaxzodbek Qambaraliyev
