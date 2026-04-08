# SSH / SCP 更新手順書

## 目的

CSN の公開環境 `umeoka.sixcore.jp` へ、ローカルの修正内容を SSH / SCP で安全に反映するための運用手順です。
この手順書は、人が読んで作業の流れを確認できることを目的にしています。

## 対象サーバー

- ホスト名: `umeoka.sixcore.jp`
- ポート: `10022`
- ユーザー: `umeoka`
- 鍵ファイル: `/home/suisyan/Documents/UMEOKA.co.jp/umeoka.key`
- 公開ディレクトリ: `/home/umeoka/umeoka.sixcore.jp/public_html`

## 正本パス

- ローカル / GitHub の正本: `/home/suisyan/Documents/csn/public_html`
- リモートの正本: `/home/umeoka/umeoka.sixcore.jp/public_html`

この 2 つは最終的に同じ構成に揃える前提で運用します。
親ディレクトリ側にある `/home/umeoka/umeoka.sixcore.jp/templates`、`/home/umeoka/umeoka.sixcore.jp/src`、`/home/umeoka/umeoka.sixcore.jp/assets` は整理対象であり、最終的な正本にしません。
親ディレクトリ側の `/home/umeoka/umeoka.sixcore.jp/admin` と `/home/umeoka/umeoka.sixcore.jp/ra.php` も、今後の更新先や正本として扱いません。

## 最終的に残すべき構成

- ローカル / GitHub
  - `/home/suisyan/Documents/csn/public_html/`
  - `/home/suisyan/Documents/csn/public_html/assets/`
  - `/home/suisyan/Documents/csn/public_html/src/`
  - `/home/suisyan/Documents/csn/public_html/templates/`
  - `/home/suisyan/Documents/csn/public_html/database/`
  - `/home/suisyan/Documents/csn/public_html/storage/`
  - `/home/suisyan/Documents/csn/public_html/prod_picture/`
- リモート
  - `/home/umeoka/umeoka.sixcore.jp/public_html/`
  - `/home/umeoka/umeoka.sixcore.jp/public_html/assets/`
  - `/home/umeoka/umeoka.sixcore.jp/public_html/src/`
  - `/home/umeoka/umeoka.sixcore.jp/public_html/templates/`
  - `/home/umeoka/umeoka.sixcore.jp/public_html/database/`
  - `/home/umeoka/umeoka.sixcore.jp/public_html/storage/`
  - `/home/umeoka/umeoka.sixcore.jp/public_html/prod_picture/`

公開入口として使うファイルは `public_html/index.php`、`public_html/ra.php`、`public_html/check.php`、`public_html/health.php` を基準にします。

## 基本方針

- 更新対象は `csn` のみとする
- 変更は最小限にする
- 先にローカルで確認し、必要なものだけを反映する
- 編集や更新を行ったら、必要なファイルはその作業の中でアップロードまで完了させる
- 反映後は必ずサーバー上で配置先と表示を確認する
- 鍵ファイルはコミットしない

## パス対応表

- ローカル / GitHub `public_html/*.php`
  - リモート `public_html/*.php`
- ローカル / GitHub `public_html/assets/*`
  - リモート `public_html/assets/*`
- ローカル / GitHub `public_html/templates/*`
  - リモート `public_html/templates/*`
- ローカル / GitHub `public_html/src/*`
  - リモート `public_html/src/*`

## 更新前確認

更新前に、まずローカルで対象ファイルが正しいかを確認します。

確認ポイント:

- 修正対象ファイルが意図した場所にあるか
- 余計なファイルまで含めていないか
- 公開先のパスを誤っていないか
- サーバーへ SSH 接続できるか

接続確認の例:

```bash
ssh -i /home/suisyan/Documents/UMEOKA.co.jp/umeoka.key \
  -p 10022 \
  umeoka@umeoka.sixcore.jp
```

接続後、公開ディレクトリの確認例:

```bash
ls -la /home/umeoka/umeoka.sixcore.jp/public_html
```

## 単一ファイルを更新する場合

1 つのファイルだけ差し替える場合は、SCP で直接アップロードします。

例: `public_html/check.php` を更新する場合

```bash
scp -i /home/suisyan/Documents/UMEOKA.co.jp/umeoka.key \
  -P 10022 \
  /home/suisyan/Documents/csn/public_html/check.php \
  umeoka@umeoka.sixcore.jp:/home/umeoka/umeoka.sixcore.jp/public_html/check.php
```

更新後は SSH で接続し、配置先に反映されたか確認します。

```bash
ssh -i /home/suisyan/Documents/UMEOKA.co.jp/umeoka.key \
  -p 10022 \
  umeoka@umeoka.sixcore.jp \
  "ls -l /home/umeoka/umeoka.sixcore.jp/public_html/check.php"
```

## ディレクトリ内のファイルを更新する場合

複数ファイルをまとめて反映したい場合も、必要な範囲だけを対象にします。
一括更新は便利ですが、意図しないファイルまで上書きしやすいため注意します。

例: `public_html/templates` ディレクトリ内の必要ファイルを更新する場合

```bash
scp -i /home/suisyan/Documents/UMEOKA.co.jp/umeoka.key \
  -P 10022 \
  /home/suisyan/Documents/csn/public_html/templates/admin_orders.php \
  umeoka@umeoka.sixcore.jp:/home/umeoka/umeoka.sixcore.jp/public_html/templates/admin_orders.php
```

ディレクトリ全体を扱う必要がある場合は、対象範囲を十分に確認してから `scp -r` を使います。

```bash
scp -r -i /home/suisyan/Documents/UMEOKA.co.jp/umeoka.key \
  -P 10022 \
  /home/suisyan/Documents/csn/public_html/assets \
  umeoka@umeoka.sixcore.jp:/home/umeoka/umeoka.sixcore.jp/public_html/
```

注意:

- `scp -r` は上書き範囲が広くなるため、単発ファイル更新より慎重に使う
- 構成整理が完了したあとは、`public_html/assets` は `/home/umeoka/umeoka.sixcore.jp/public_html/assets/` に揃える

## 更新後確認

更新後は、サーバー上のファイル配置とブラウザ表示を両方確認します。

サーバー上の確認例:

```bash
ssh -i /home/suisyan/Documents/UMEOKA.co.jp/umeoka.key \
  -p 10022 \
  umeoka@umeoka.sixcore.jp \
  "ls -la /home/umeoka/umeoka.sixcore.jp/public_html"
```

ブラウザ確認の例:

- `https://umeoka.sixcore.jp/check.php`
- 管理画面を更新した場合は対象画面を直接開く
- CSS / JS を更新した場合はキャッシュを避けて再読み込みする

確認ポイント:

- 500 エラーが出ていないか
- 対象画面だけでなく、関連画面も崩れていないか
- 文字化けやリンク切れがないか
- 管理画面更新時はログイン後の画面遷移も確認する

## よくある更新対象と配置先

- `public_html/check.php`
  - 配置先: `/home/umeoka/umeoka.sixcore.jp/public_html/check.php`
- `public_html/ra.php`
  - 配置先: `/home/umeoka/umeoka.sixcore.jp/public_html/ra.php`
- `public_html/assets/app.css`
  - 整理後の配置先: `/home/umeoka/umeoka.sixcore.jp/public_html/assets/app.css`
- `public_html/assets/app.js`
  - 整理後の配置先: `/home/umeoka/umeoka.sixcore.jp/public_html/assets/app.js`
- `public_html/templates/*.php`
  - 配置先: `/home/umeoka/umeoka.sixcore.jp/public_html/templates/`
- `public_html/src/*.php`
  - 配置先: `/home/umeoka/umeoka.sixcore.jp/public_html/src/`

## 注意点

- 鍵ファイルの権限や場所を不用意に変更しない
- 本番サーバー上で不要な削除操作は行わない
- PHP ロジック変更禁止の方針があるため、反映対象の内容を事前に確認する
- リモート側には `.git` もあるが、運用上は SSH / SCP で必要ファイルのみ更新する
- `/home/umeoka/umeoka.sixcore.jp/public_html/public_html/` は誤ってできた入れ子構成として扱い、今後の更新先にしない
- `/home/umeoka/umeoka.sixcore.jp/templates` `/home/umeoka/umeoka.sixcore.jp/src` `/home/umeoka/umeoka.sixcore.jp/assets` は移行中の暫定参照先として扱い、最終的な正本にしない
- 大きな更新の前後では、`進捗状況.txt` に作業内容を残す

## 迷ったときの進め方

迷った場合は、次の順で進めます。

1. まず SSH で接続し、リモートの配置先を確認する
2. 単一ファイル更新で済むなら、ディレクトリ丸ごとの更新は避ける
3. 更新後はファイル確認とブラウザ確認を両方行う
4. 不明点があるまま上書きしない
