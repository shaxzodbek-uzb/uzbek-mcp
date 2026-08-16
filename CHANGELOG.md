# Changelog

All notable changes to this project are documented here. The format is based on
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to
[Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.2.0] - 2026-08-16

First tagged release. 0.1.0 below was written up but never tagged, so this is the
first version `composer create-project shaxzodbek-uzb/uzbek-mcp` can resolve.

### Added

- `phone-normalize` — parse any of the common ways an Uzbek number is written
  (`+998 90 123 45 67`, `998901234567`, `8 90 123-45-67`, `90 123 45 67`) and
  return it in E.164, international and local forms, plus the operator or region
  the 2-digit prefix belongs to. Number portability means that prefix describes
  the allocated range rather than today's carrier, and the tool says so instead
  of implying a live lookup.
- `date-to-words` — a date written out in Uzbek, in either script.
- `stir-validate` — checks the *format* of a 9-digit STIR (taxpayer number).
  **It deliberately does not verify a checksum.** The published descriptions of
  the STIR check digit disagree with each other, and a wrong algorithm would
  reject real taxpayers — a validator that is confidently wrong is worse than one
  that is honestly narrow. The tool states this limit in its own output.
- Test suite grows from 40 to 67 (unit + feature).

## [0.1.0] - 2026-06-20 *(never tagged)*

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
