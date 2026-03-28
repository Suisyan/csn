param(
    [string]$InputPath = "D:\Documents\coolingshop.net\00Ka_csn_20260327023519.txt",
    [string]$OutputPath = "D:\Documents\csn\database\00Ka_csn_20260327023519_migration_ready.sql",
    [string]$DatabaseName = "csn_legacy",
    [switch]$DropAnalyticsTables
)

$ErrorActionPreference = "Stop"

if (-not (Test-Path -LiteralPath $InputPath)) {
    throw "Input file not found: $InputPath"
}

$raw = Get-Content -LiteralPath $InputPath -Raw -Encoding UTF8

$raw = [regex]::Replace(
    $raw,
    'CREATE DATABASE /\*!\d+ IF NOT EXISTS\*/ `[^`]+` /\*!\d+ DEFAULT CHARACTER SET utf8mb4 \*/;',
    "CREATE DATABASE IF NOT EXISTS ``$DatabaseName`` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
)

$raw = [regex]::Replace(
    $raw,
    'USE `[^`]+`;',
    "USE ``$DatabaseName``;"
)

$raw = $raw -replace 'SET character_set_client = utf8 \*/;', 'SET character_set_client = utf8mb4 */;'
$raw = $raw -replace 'ENGINE=MyISAM', 'ENGINE=InnoDB'
$raw = $raw -replace 'DEFAULT CHARSET=ujis', 'DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
$raw = $raw -replace 'DEFAULT CHARSET=utf8mb4;', 'DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
$raw = $raw -replace "DEFAULT '0000-00-00 00:00:00'", 'DEFAULT NULL'
$raw = $raw -replace "DEFAULT '0000-00-00'", 'DEFAULT NULL'
$raw = $raw -replace "'0000-00-00 00:00:00'", 'NULL'
$raw = $raw -replace "'0000-00-00'", 'NULL'
$raw = $raw -replace 'datetime NOT NULL DEFAULT NULL', 'datetime DEFAULT NULL'
$raw = $raw -replace 'date NOT NULL DEFAULT NULL', 'date DEFAULT NULL'
$raw = $raw -replace 'timestamp NOT NULL DEFAULT NULL', 'timestamp NULL DEFAULT NULL'

if ($DropAnalyticsTables) {
    $tables = @('eventlog', 'phplist_userstats', 'search_keyword', 'slog')
    foreach ($table in $tables) {
        $pattern = "(?s)--`r?`n-- Table structure for table ``$table````r?`n--.*?UNLOCK TABLES;`r?`n"
        $raw = [regex]::Replace($raw, $pattern, '')
    }
}

$header = @"
-- Prepared for staged migration into PHP 8 / MySQL 5.7 / UTF-8 environment
-- Source dump: $InputPath
-- Generated: $(Get-Date -Format "yyyy-MM-dd HH:mm:ss")
-- Notes:
-- 1. Storage engine normalized to InnoDB where possible.
-- 2. Legacy ujis table charset normalized to utf8mb4.
-- 3. Zero-date defaults and values normalized to NULL.
-- 4. Legacy auth table acc still contains insecure pass data; do not reuse directly for new login.

"@

[System.IO.File]::WriteAllText($OutputPath, $header + $raw, [System.Text.UTF8Encoding]::new($false))
Write-Host "Prepared migration dump written to $OutputPath"
