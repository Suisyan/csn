# Admin Order Modal Compact Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** 管理画面の受注明細モーダルを、要点を上段に集約し、詳細情報を折りたたみ表示にしてコンパクトにする

**Architecture:** `templates/admin_order_detail.php` で表示構造を再編成し、初期表示は更新操作と注文サマリーに絞る。`public_html/assets/app.css` でモーダル幅、カード余白、折りたたみ要素、明細テーブル密度を調整してコンパクトな見た目にする。

**Tech Stack:** PHP テンプレート、既存 CSS

---

### Task 1: 現状構造の最小変更方針で再構成する

**Files:**
- Modify: `templates/admin_order_detail.php`
- Modify: `templates/admin_orders.php`
- Modify: `templates/admin_dashboard.php`

- [ ] **Step 1: モーダルの初期表示要素を決める**

上段に残す要素:

```text
入金確認
出荷処理
注文番号
注文日時
支払方法
会員区分
合計
状態
```

折りたたみに移す要素:

```text
注文者情報
配送先
発送情報
商品明細
```

- [ ] **Step 2: モーダルのベース幅を縮める**

`templates/admin_orders.php` と `templates/admin_dashboard.php` のインライン幅指定を次に変更する。

```php
$modalDialogStyle = 'position:relative;width:min(920px,100%);max-height:100%;overflow:auto;margin:0;border-radius:24px;border:1px solid rgba(15,23,42,0.08);background:#f8f7f3;box-shadow:0 24px 80px rgba(15,23,42,0.24);';
```

- [ ] **Step 3: 受注明細テンプレートを要約表示中心に組み替える**

`templates/admin_order_detail.php` の構造を次のまとまりに寄せる。

```text
admin-detail-grid
  入金確認カード
  出荷処理カード

admin-card
  注文サマリー

details
  注文者情報
details
  配送先・発送情報
details
  商品明細
```

### Task 2: コンパクト表示用スタイルを追加する

**Files:**
- Modify: `public_html/assets/app.css`

- [ ] **Step 1: モーダル全体の余白と幅を詰める**

追加・更新対象:

```css
.admin-order-modal__dialog { width: min(920px, 100%); border-radius: 24px; }
.admin-order-modal__header { padding: 1rem 1.25rem 0.85rem; }
.admin-order-modal__body { padding: 1rem 1.25rem 1.25rem; }
.admin-order-detail { gap: 0.9rem; }
```

- [ ] **Step 2: サマリーと折りたたみ用クラスを追加する**

追加対象:

```css
.admin-order-summary-grid { display:grid; gap:0.75rem; grid-template-columns:repeat(2, minmax(0, 1fr)); }
.admin-order-summary-grid div { padding:0.7rem 0.8rem; border:1px solid rgba(15,23,42,0.08); border-radius:14px; background:#fff; }
.admin-order-section details { border:1px solid rgba(15,23,42,0.08); border-radius:16px; background:#fff; }
.admin-order-section summary { cursor:pointer; list-style:none; padding:0.9rem 1rem; font-weight:700; }
.admin-order-section__body { padding:0 1rem 1rem; }
```

- [ ] **Step 3: 明細テーブルとフォーム密度を詰める**

追加対象:

```css
.admin-order-form { gap: 0.65rem; }
.admin-order-detail .admin-card { padding: 1rem; }
.admin-order-detail .admin-table th,
.admin-order-detail .admin-table td { padding: 0.65rem 0.55rem; font-size: 0.92rem; }
```

### Task 3: 目視確認と反映

**Files:**
- Verify: `templates/admin_order_detail.php`
- Verify: `public_html/assets/app.css`
- Verify: `templates/admin_orders.php`
- Verify: `templates/admin_dashboard.php`

- [ ] **Step 1: 自動テスト基盤の有無を確認する**

Run: `rg --files -g 'phpunit.xml*' -g 'composer.json' -g 'package.json' -g 'tests/**'`
Expected: 画面変更を直接検証する自動テスト基盤が見当たらないことを確認する

- [ ] **Step 2: 差分を目視確認する**

Run: `git diff -- templates/admin_order_detail.php templates/admin_orders.php templates/admin_dashboard.php public_html/assets/app.css`
Expected: モーダル縮小、折りたたみ導入、サマリー化に関する差分だけが入っている

- [ ] **Step 3: 必要ファイルを SSH / SCP で反映する**

反映対象:

```text
templates/admin_order_detail.php -> /home/umeoka//umeoka.sixcore.jp/public_html/templates/admin_order_detail.php
templates/admin_orders.php -> /home/umeoka//umeoka.sixcore.jp/public_html/templates/admin_orders.php
templates/admin_dashboard.php -> /home/umeoka//umeoka.sixcore.jp/public_html/templates/admin_dashboard.php
public_html/assets/app.css -> /home/umeoka//umeoka.sixcore.jp/public_html/public_html/assets/app.css
```
