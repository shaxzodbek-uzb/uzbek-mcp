# Changelog

All notable changes to this project are documented here. The format is based on
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to
[Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.0] - 2026-06-20

Initial release.

### Added

- **Language tools** (offline, deterministic, official 1995 Uzbek alphabet):
  - `transliterate` — Latin ↔ Cyrillic with auto-detection, correct `oʻ`/`gʻ` (U+02BB) and
    tutuq belgisi (U+02BC), positional `е`/`ye` and `ц`/`ts`/`s` rules.
  - `normalize-text` — apostrophe/Unicode normalization and whitespace collapsing.
  - `number-to-words` — integers to written Uzbek (Latin or Cyrillic), optional currency unit.
  - `slugify` — ASCII URL slugs from Uzbek text in either script.
- **Uzbekistan data tools** (no API key):
  - `currency-rate` — CBU exchange rates with optional date and amount conversion.
  - `public-holidays` — official holidays per year (fixed + lunar lookup 2024–2027).
  - `weather` — current weather + today's forecast via Open-Meteo, with offline city lookup.
- Local (stdio) and web (HTTP) transports via Laravel MCP.
- 40 tests (unit + feature).

[0.1.0]: https://github.com/shaxzodbek-uzb/uzbek-mcp/releases/tag/v0.1.0
