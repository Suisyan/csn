# CSN Renewal

Coolingshop.net renewal workspace.

## Goals

- Keep existing copy and business meaning as much as possible
- Unify to UTF-8
- Target PHP 8.x and MySQL 5.7
- Modernize UI while reducing page/file sprawl
- Replace plaintext password handling with secure hashing
- Make environment switching easy for `umeoka.sixcore.jp`

## Structure

- `public/`: web root
- `src/`: application code
- `templates/`: layout and page templates
- `database/`: schema and migration notes
- `storage/`: logs and cache

## Notes

- This is the first migration base, not the full finished site.
- Existing text should be migrated page by page from the legacy site.
