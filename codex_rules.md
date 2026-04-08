# Codex Rules

- 修正対象は `csn` のみ
- `v0-radiator-sales-website` は参照専用
- ローカル / GitHub の正本パスは `/home/suisyan/Documents/csn/public_html`
- リモートの正本パスは `/home/umeoka/umeoka.sixcore.jp/public_html`
- 最終的にローカル / GitHub の `public_html` とリモートの `public_html` は同じ構成に揃える
- 今後の参照コードは `public_html` 基準へ寄せる
- 今後の更新対象は原則として `public_html` 配下のみとし、親ディレクトリ側の `admin` や `ra.php` は正本として扱わない
- PHPロジックは変更禁止
- CSSは既存優先
- 変更は最小限
- `git` 操作のみで対応し、`commit` と `push` のコメントは日本語にする
- 公開環境への接続と更新は `SSH` / `SCP` を使う
- 編集や更新を行ったら、必要なファイルは `SSH` / `SCP` でアップロードまで行う
- `/home/umeoka/umeoka.sixcore.jp/templates` `/home/umeoka/umeoka.sixcore.jp/src` `/home/umeoka/umeoka.sixcore.jp/assets` を最終的な正本にしない
- DB構造は当面、旧構造のまま維持し、統合や再設計は行わない
- 開発方針はアジャイルで進める
- まず動く状態を作り、不具合が出たら都度直しながら完成まで持っていく

## 最終的に残すべき構成

- ローカル / GitHub の正本ルート: `/home/suisyan/Documents/csn/public_html`
- リモートの正本ルート: `/home/umeoka/umeoka.sixcore.jp/public_html`
- 公開入口: `public_html/index.php` `public_html/ra.php` `public_html/check.php` `public_html/health.php`
- アセット: `public_html/assets/`
- アプリ本体: `public_html/src/`
- テンプレート: `public_html/templates/`
- データ関連: `public_html/database/` `public_html/storage/` `public_html/prod_picture/`
- 補助ファイル: `public_html/.htaccess` `public_html/.user.ini` `public_html/robots.txt`

## 最終的な正本にしない場所

- `/home/umeoka/umeoka.sixcore.jp/admin`
- `/home/umeoka/umeoka.sixcore.jp/ra.php`
- `/home/umeoka/umeoka.sixcore.jp/src`
- `/home/umeoka/umeoka.sixcore.jp/templates`
- `/home/umeoka/umeoka.sixcore.jp/assets`
