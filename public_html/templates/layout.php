<?php declare(strict_types=1); ?>
<?php
$isAdminPage = str_starts_with((string) current_path(), '/admin');
$isAdminShell = $isAdminPage && current_path() !== '/admin/login';
$currentUser = function_exists('current_user') ? current_user() : null;
$cartCount = function_exists('cart_count') ? cart_count() : 0;
$cartLabel = $cartCount > 0 ? 'Cart (' . $cartCount . ')' : 'Cart';
$accountTheme = function_exists('account_theme') ? account_theme($currentUser) : 'guest';
$accountLabel = function_exists('account_label') ? account_label($currentUser) : 'Guest';
$accountSummary = function_exists('account_summary') ? account_summary($currentUser) : 'Member helpers not loaded yet.';
$accountSummaryHtml = function_exists('account_summary_html') ? account_summary_html($currentUser) : e($accountSummary);
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
  <link rel="stylesheet" href="/assets/app.css?v=20260406-admin-modal">
</head>
<body>
  <?php if ($isAdminShell): ?>
    <?= render('admin_shell', ['content' => $content ?? '']) ?>
    <script src="/assets/app.js?v=20260406-admin-modal"></script>
  </body>
</html>
    <?php return; ?>
  <?php endif; ?>
  <div class="flex min-h-screen flex-col">
    <header class="sticky top-0 z-50 border-b border-border bg-card">
      <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 lg:px-8">
        <a href="<?= e(storefront_home_url()) ?>" class="flex flex-col text-decoration-none">
          <span class="text-xs uppercase tracking-widest text-muted-foreground">Radiator Shop</span>
          <span class="text-lg font-semibold tracking-tight">ラジエーターショップ</span>
        </a>

        <nav class="hidden items-center gap-8 md:flex">
          <a href="<?= e(storefront_home_url()) ?>" class="text-sm font-medium text-foreground transition-colors hover:text-muted-foreground">ホーム</a>
          <a href="/search" class="text-sm font-medium text-foreground transition-colors hover:text-muted-foreground">商品検索</a>
          <a href="/cart" class="text-sm font-medium text-foreground transition-colors hover:text-muted-foreground">カート</a>
          <a href="/inquiry" class="text-sm font-medium text-foreground transition-colors hover:text-muted-foreground">お問い合わせ</a>
          <a href="<?= e($accountHref) ?>" class="text-sm font-medium text-foreground transition-colors hover:text-muted-foreground"><?= $currentUser === null ? 'ログイン' : 'マイページ' ?></a>
        </nav>

        <div class="flex items-center gap-4">
          <a href="/search" class="hidden md:block">
            <span class="v0-icon-button">S</span>
          </a>
          <a href="<?= e($accountHref) ?>" class="hidden md:block">
            <span class="v0-icon-button">U</span>
          </a>
          <a href="/cart" class="relative">
            <span class="v0-icon-button">
              C
              <?php if ($cartCount > 0): ?>
                <span class="v0-cart-badge"><?= e((string) $cartCount) ?></span>
              <?php endif; ?>
            </span>
          </a>
        </div>
      </div>
      <div class="site-status-bar site-status-bar--<?= e($accountTheme) ?>">
        <div class="site-status-bar__inner">
          <span class="member-badge member-badge--<?= e($accountTheme) ?>"><?= e($accountLabel) ?></span>
          <span class="site-status-bar__text">
            <?php if (is_array($currentUser)): ?>
              <?= e((string) ($currentUser['name'] ?? $currentUser['email'] ?? '')) ?> /
            <?php endif; ?>
            <?= $accountSummaryHtml ?>
          </span>
        </div>
      </div>
    </header>

    <main class="flex-1">
      <?= $content ?? '' ?>
    </main>

    <footer class="border-t border-border bg-card">
      <div class="mx-auto max-w-7xl px-4 py-12 lg:px-8">
        <div class="grid grid-cols-1 gap-8 md:grid-cols-4">
          <div class="md:col-span-2">
            <a href="<?= e(storefront_home_url()) ?>" class="flex flex-col text-decoration-none">
              <span class="text-xs uppercase tracking-widest text-muted-foreground">Radiator Shop</span>
              <span class="text-lg font-semibold tracking-tight">ラジエーターショップ</span>
            </a>
            <p class="mt-4 max-w-md text-sm text-muted-foreground">自動車用ラジエーターの専門店として、国内外のメーカーに対応した高品質な製品を幅広く取り揃えています。</p>
          </div>
          <div>
            <h3 class="text-sm font-semibold">ショップ</h3>
            <ul class="mt-4 space-y-2">
              <li><a href="/search" class="text-sm text-muted-foreground hover:text-foreground">商品検索</a></li>
              <li><a href="/cart" class="text-sm text-muted-foreground hover:text-foreground">カート</a></li>
              <li><a href="/inquiry" class="text-sm text-muted-foreground hover:text-foreground">お問い合わせ</a></li>
            </ul>
          </div>
          <div>
            <h3 class="text-sm font-semibold">会社情報</h3>
            <ul class="mt-4 space-y-2">
              <li><span class="text-sm text-muted-foreground">営業時間: 9:00 - 18:00</span></li>
              <li><span class="text-sm text-muted-foreground">定休日: 土日祝</span></li>
              <li><span class="text-sm text-muted-foreground">URL: <?= e((string) config('APP_URL', '')) ?></span></li>
            </ul>
          </div>
        </div>
        <div class="mt-12 border-t border-border pt-8">
          <p class="text-center text-sm text-muted-foreground">&copy; <?= e((string) date('Y')) ?> ラジエーターショップ. All rights reserved.</p>
        </div>
      </div>
    </footer>
  </div>
  <script src="/assets/app.js?v=20260406-admin-modal"></script>
</body>
</html>
