# Contributing to Uzbek MCP

Thanks for your interest! This project aims to be the most correct, deterministic
Uzbek-language toolbelt for AI agents. Contributions of all sizes are welcome.

## Good first contributions

- **Transliteration edge cases** — add a failing test in `tests/Unit/TransliteratorTest.php`
  for any word that converts incorrectly, then fix the mapping in
  `app/Support/Uzbek/Transliterator.php`.
- **More cities** — add coordinates to `app/Support/Uzbek/Cities.php` (with an alias if the
  English/Russian name differs) and a test in `tests/Unit/CitiesTest.php`.
- **Future lunar-holiday years** — add Ramazon/Qurbon hayit dates to the `LUNAR` table in
  `app/Support/Uzbek/Holidays.php` as each year's government decree is published.
- **New tools** — e.g. syllabification, spell-checking, or other Uzbekistan open-data feeds.

## Development setup

```bash
composer install
cp .env.example .env
php artisan key:generate
```

## Before you open a PR

```bash
composer test              # all tests must pass
./vendor/bin/pint          # format the code (Laravel preset)
```

Please:

- Keep the core logic framework-agnostic in `app/Support/Uzbek` and keep the MCP tools in
  `app/Mcp/Tools` thin (validate → call a support class → return a `Response`).
- Add or update tests for any behavior change.
- Use the correct Unicode apostrophes: **U+02BB** (`ʻ`) for `oʻ`/`gʻ`, **U+02BC** (`ʼ`) for the
  tutuq belgisi — never a straight ASCII `'` in expected output.

## Reporting issues

Open a GitHub issue with the input, the actual output, and the expected output. For
transliteration bugs, please cite the rule or source you're basing the expectation on.
