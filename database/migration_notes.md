# Migration Notes

## Legacy to Renewal Mapping

- `index.php` -> `/`
- `result.php` -> `/search`
- `detail.php` -> `/product/{id}`
- `inquiry.php` + `mail_send.php` -> `/inquiry`
- legacy login pages -> `/login`

## Security Changes

- Remove string-concatenated SQL
- Use PDO prepared statements
- Remove plaintext password storage
- Use `password_hash()` and `password_verify()`

## Next Steps

1. Import legacy wording page by page
2. Migrate product/category tables with explicit column mapping
3. Replace fallback demo data with real DB data
4. Add cart and member area after search/detail/inquiry are stabilized
