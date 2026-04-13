# Public HTML Root Alignment Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** ローカル/GitHub の正本を `/home/suisyan/Documents/csn/public_html` 基準に寄せ、リモート `/home/umeoka/umeoka.sixcore.jp/public_html` と対応が分かるように運用ルールと参照コードを整理する

**Architecture:** 先に正本パスをルール文書へ明記し、つぎに現在のローカル構成とリモート構成の対応関係を確定する。最後に実際の公開で使っている参照コードを `public_html` 基準へ寄せ、不要な重複ディレクトリを段階的に削除する。

**Tech Stack:** PHP、Apache `.htaccess`、SSH/SCP、Markdown 運用文書

---

### Task 1: 正本パスと運用ルールを固定する

**Files:**
- Modify: `codex_rules.md`
- Modify: `document/ssh_scp_update_manual.md`
- Modify: `進捗状況.txt`

- [x] **Step 1: 正本パスを明記する**

```text
ローカル/GitHub 正本: /home/suisyan/Documents/csn/public_html
リモート正本: /home/umeoka/umeoka.sixcore.jp/public_html
```

- [x] **Step 2: 配下対応表を追記する**

```text
local public_html/assets/* -> remote public_html/assets/*
local public_html/*.php -> remote public_html/*.php
local templates/* は最終的に remote public_html/templates/* へ寄せる
local src/* は最終的に remote public_html/src/* へ寄せる
```

- [x] **Step 3: 誤更新防止ルールを追記する**

```text
/home/umeoka/umeoka.sixcore.jp/assets など親側へ直接アップロードしない
/home/umeoka/umeoka.sixcore.jp/templates など親側を正本にしない
```

### Task 2: ローカル構成を 1 つ上へ持ち上げる準備をする

**Files:**
- Verify: `/home/suisyan/Documents/csn`
- Verify: `/home/suisyan/Documents/csn/csn`

- [x] **Step 1: 親ディレクトリの現状を確認する**

Run: `find /home/suisyan/Documents/csn -maxdepth 2 -mindepth 1 | sort`
Expected: 実体が `/home/suisyan/Documents/csn/csn` に寄っていることを確認する

- [x] **Step 2: 移設先の競合を確認する**

Run: `ls -la /home/suisyan/Documents/csn`
Expected: `public_html` などの競合ディレクトリがまだ存在しないことを確認する

- [x] **Step 3: 親ディレクトリへ移す対象一覧を固定する**

```text
.env.example
.git
.gitignore
README.md
codex_rules.md
database
docs
document
prod_picture
public_html
src
storage
templates
進捗状況.txt
```

### Task 3: 実際に公開で使うディレクトリを切り分ける

**Files:**
- Verify: remote `/home/umeoka/umeoka.sixcore.jp/public_html`
- Verify: remote `/home/umeoka/umeoka.sixcore.jp/templates`
- Verify: remote `/home/umeoka/umeoka.sixcore.jp/src`
- Verify: remote `/home/umeoka/umeoka.sixcore.jp/assets`

- [x] **Step 1: `public_html` と親側の重複を確認する**

Run: `ssh ... 'find /home/umeoka/umeoka.sixcore.jp -maxdepth 1 -mindepth 1 -type d | sort'`
Expected: `public_html` と `templates` `src` `assets` が親側に共存していることを確認する

- [x] **Step 2: 参照中の親側ファイルを確認する**

```text
/home/umeoka/umeoka.sixcore.jp/ra.php
/home/umeoka/umeoka.sixcore.jp/templates/*
/home/umeoka/umeoka.sixcore.jp/src/*
/home/umeoka/umeoka.sixcore.jp/assets/*
```

- [x] **Step 3: 最終的な寄せ先を固定する**

```text
公開 PHP 入口: /home/umeoka/umeoka.sixcore.jp/public_html
参照コード: /home/umeoka/umeoka.sixcore.jp/public_html/src
参照テンプレート: /home/umeoka/umeoka.sixcore.jp/public_html/templates
参照アセット: /home/umeoka/umeoka.sixcore.jp/public_html/assets
```

### Task 4: 参照コードを `public_html` 基準へ寄せる

**Files:**
- Modify: remote `/home/umeoka/umeoka.sixcore.jp/ra.php`
- Modify: remote `/home/umeoka/umeoka.sixcore.jp/public_html/ra.php`
- Modify: remote `/home/umeoka/umeoka.sixcore.jp/public_html/index.php`

- [x] **Step 1: `ra.php` がどの `src/bootstrap.php` を読むか確認する**
- [x] **Step 2: 必要なら `public_html` 側を読むように修正する**
- [x] **Step 3: `/admin` `/check.php` などの導線が壊れないことを確認する**

### Task 5: 不要な重複ディレクトリを削除する

**Files:**
- Remove: remote `/home/umeoka/umeoka.sixcore.jp/csn`
- Consider remove: remote parent `templates` `src` `assets` after migration verification

- [x] **Step 1: まず `csn` を削除する前に参照されていないことを確認する**
- [x] **Step 2: `csn` を削除する**
- [x] **Step 3: 親側 `templates` `src` `assets` は参照移行が終わるまで保留し、切替後に削除する**
