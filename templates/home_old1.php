<?php declare(strict_types=1); ?>
<section class="hero">
  <div class="hero__panel">
    <span class="hero__kicker">All Renewal Base</span>
    <h1>ラジエーター・コンデンサーの検索体験を、今の標準へ。</h1>
    <p>
      旧サイトの文言や商流はできるだけ維持しつつ、表示、検索、問い合わせ、会員導線を
      UTF-8 と PHP 8 系で整理するための新しい土台です。
    </p>

    <form class="hero-search" action="/search" method="get">
      <div class="hero-search__grid">
        <label>
          メーカー
          <select name="make">
            <option value="">選択してください</option>
            <?php foreach ($makes as $make): ?>
              <option value="<?= e($make) ?>"><?= e($make) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label>
          型式
          <input type="text" name="katasiki" placeholder="例: E-AE91" data-auto-focus>
        </label>
        <label>
          ミッション
          <select name="toc">
            <option value="">選択してください</option>
            <option value="A/T">A/T</option>
            <option value="M/T">M/T</option>
            <option value="CVT">CVT</option>
          </select>
        </label>
      </div>
      <div>
        <button type="submit">検索する</button>
      </div>
    </form>
  </div>

  <div class="hero__aside">
    <article class="card">
      <h2>今回の方針</h2>
      <p class="muted">文言維持、モダンUI、UTF-8統一、SQL改善、認証改善、ファイル整理。</p>
    </article>
    <article class="card">
      <h2>移行の優先範囲</h2>
      <p class="muted">トップ、検索、詳細、問い合わせ、ログインを先行して一本化します。</p>
    </article>
    <article class="card">
      <h2>テスト環境</h2>
      <p class="muted">`umeoka.sixcore.jp` に向けて、ドメイン依存を環境設定へ寄せています。</p>
    </article>
  </div>
</section>

<section class="content-grid">
  <article class="card">
    <h2>旧サイトから引き継ぐもの</h2>
    <div class="stack muted">
      <div>商品検索の考え方</div>
      <div>問合せ導線の意味</div>
      <div>国内車・輸入車の分類</div>
      <div>業務で使っている部品項目</div>
    </div>
  </article>
  <article class="card">
    <h2>刷新するもの</h2>
    <div class="stack muted">
      <div>文字コードを UTF-8 に統一</div>
      <div>SQL をプリペアドステートメントへ統一</div>
      <div>認証を `password_hash` / `password_verify` 化</div>
      <div>画面テンプレートと共通レイアウトの整理</div>
    </div>
  </article>
</section>
