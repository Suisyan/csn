<?php declare(strict_types=1); ?>
<?php
$currentUser = function_exists('current_user') ? current_user() : null;
$cartCount = function_exists('cart_count') ? cart_count() : 0;
$cartLabel = $cartCount > 0 ? 'Cart (' . $cartCount . ')' : 'Cart';
$accountTheme = function_exists('account_theme') ? account_theme($currentUser) : 'guest';
$accountLabel = function_exists('account_label') ? account_label($currentUser) : 'Guest';
$accountSummary = function_exists('account_summary') ? account_summary($currentUser) : 'Member helpers not loaded yet.';
$accountHref = $currentUser === null ? '/login' : '/account';
?>
<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title ?? config('APP_NAME', 'Sample Test Site')) ?></title>
  <meta name="description" content="Sample test site for search, member state, and cart flow.">
  <meta name="robots" content="noindex, nofollow, noarchive">
  <link rel="stylesheet" href="/assets/app.css?v=20260403-v0hero1">
</head>
<body>
  <div class="site-shell">
    <header class="site-header">
      <div class="site-header__inner">
        <a class="brand brand--shop" href="/">
          <span class="brand__eyebrow">Radiator Shop</span>
          <span class="brand__name">ラジエーターショップ</span>
        </a>
        <nav class="nav nav--header">
          <a href="/">ホーム</a>
          <a href="/search">商品検索</a>
          <a href="/cart"><?= e($cartLabel) ?></a>
          <a href="/inquiry">お問い合わせ</a>
          <a href="<?= e($accountHref) ?>"><?= $currentUser === null ? 'ログイン' : 'アカウント' ?></a>
          <?php if ($currentUser !== null): ?>
            <form class="nav-logout" action="/logout" method="post">
              <button type="submit">ログアウト</button>
            </form>
          <?php endif; ?>
        </nav>
      </div>
      <div class="site-header__inner site-header__inner--sub">
        <a class="brand brand--sub" href="/">
          <span class="brand__eyebrow">Sample Test</span>
          <span class="brand__name">Sample Test Site</span>
        </a>
        <p class="site-header__note">検索・価格・カート導線の確認用サイト</p>
      </div>
      <div class="site-status-bar site-status-bar--<?= e($accountTheme) ?>">
        <div class="site-status-bar__inner">
          <span class="member-badge member-badge--<?= e($accountTheme) ?>">
            <?= e($accountLabel) ?>
          </span>
          <span class="site-status-bar__text">
            <?php if (is_array($currentUser)): ?>
              <?= e((string) ($currentUser['name'] ?? $currentUser['email'] ?? '')) ?> /
            <?php endif; ?>
            <?= e($accountSummary) ?>
          </span>
        </div>
      </div>
    </header>

    <?= $content ?? '' ?>

    <footer class="site-footer">
      <div class="site-footer__inner">
        <div>Test domain: <?= e((string) config('APP_URL', '')) ?></div>
        <div>UTF-8 / PHP 8.5 / MySQL 5.7</div>
      </div>
    </footer>
  </div>
  <script src="/assets/app.js"></script>
</body>
</html>
