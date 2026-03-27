<?php declare(strict_types=1); ?>
<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title ?? config('APP_NAME', 'CSN')) ?></title>
  <meta name="description" content="Cooling Shop renewal workspace">
  <link rel="stylesheet" href="/assets/app.css">
</head>
<body>
  <div class="site-shell">
    <header class="site-header">
      <div class="site-header__inner">
        <a class="brand" href="/">
          <span class="brand__eyebrow">Cooling Shop</span>
          <span class="brand__name">ドットネット</span>
        </a>
        <nav class="nav">
          <a href="/">ホーム</a>
          <a href="/search">検索</a>
          <a href="/inquiry">お問い合わせ</a>
          <a href="/login">ログイン</a>
        </nav>
      </div>
    </header>

    <?= $content ?? '' ?>

    <footer class="site-footer">
      <div class="site-footer__inner">
        <div>テストドメイン: <?= e((string) config('APP_URL', '')) ?></div>
        <div>UTF-8 / PHP 8系 / MySQL 5.7 前提で再構成中です。</div>
      </div>
    </footer>
  </div>
  <script src="/assets/app.js"></script>
</body>
</html>
