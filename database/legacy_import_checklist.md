# Legacy DB Import Checklist

対象ダンプ:

- `D:\Documents\coolingshop.net\00Ka_csn_20260327023519.txt`

生成先:

- `D:\Documents\csn\database\00Ka_csn_20260327023519_migration_ready.sql`

## この整形で行うこと

- `CREATE DATABASE` と `USE` を移行用DB名に置換
- `MyISAM` を `InnoDB` に寄せる
- `ujis` を `utf8mb4` + `utf8mb4_unicode_ci` に寄せる
- `0000-00-00 00:00:00` などのゼロ日時を `NULL` に寄せる
- `acc.pass` は残るが、新ログインでは再利用しない前提にする

## 実行方法

PowerShell で:

```powershell
powershell -ExecutionPolicy Bypass -File D:\Documents\csn\database\prepare_legacy_dump.ps1
```

分析系ログテーブルを落として軽くしたい場合:

```powershell
powershell -ExecutionPolicy Bypass -File D:\Documents\csn\database\prepare_legacy_dump.ps1 -DropAnalyticsTables
```

## phpMyAdmin での移行手順

1. 移行先サーバで空のDBを作成
   DB名: `umeoka_csn`
2. `utf8mb4` を使うように設定
3. 生成された `00Ka_csn_20260327023519_migration_ready.sql` をインポート
4. 文字化けがないか `MODEL`, `PARTS`, `inquiry`, `acc` を最初に確認
5. 新サイト側では `acc.pass` を使わず、`users.password_hash` を別管理にする

## 重点確認テーブル

- `MODEL`
- `PARTS`
- `oe_num`
- `inquiry`
- `acc`
- `cart`
- `support`

## 注意

- `acc.pass` には平文または旧式ハッシュが混在している可能性があります
- `search_keyword`, `slog`, `eventlog` は容量が大きく、必須でなければ分離推奨です
- 一部データに文字化けが見える場合は、元データのエンコード確認が必要です
