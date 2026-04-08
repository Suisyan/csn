<?php declare(strict_types=1); ?>
<?php $admin = current_admin(); ?>
<div class="admin-shell">
  <header class="admin-header">
    <div class="admin-header__inner">
      <a href="/admin" class="admin-brand">
        <span class="admin-brand__eyebrow">Cooling Shop</span>
        <span class="admin-brand__name">管理画面</span>
      </a>
      <div class="admin-header__meta">
        <span class="admin-header__user"><?= e((string) (($admin['username'] ?? 'admin'))) ?></span>
        <form action="/admin/logout" method="post">
          <button type="submit" class="admin-logout">ログアウト</button>
        </form>
      </div>
    </div>
  </header>

  <div class="admin-nav-wrap">
    <nav class="admin-nav">
      <a href="/admin" class="admin-nav__item<?= current_path() === '/admin' ? ' admin-nav__item--active' : '' ?>">TOP</a>
      <a href="/admin/orders" class="admin-nav__item<?= current_path() === '/admin/orders' || str_starts_with(current_path(), '/admin/orders/') ? ' admin-nav__item--active' : '' ?>">受注管理</a>
      <a href="/admin/products" class="admin-nav__item<?= current_path() === '/admin/products' ? ' admin-nav__item--active' : '' ?>">商品管理</a>
      <a href="/admin/members" class="admin-nav__item<?= current_path() === '/admin/members' ? ' admin-nav__item--active' : '' ?>">会員管理</a>
      <a href="/admin/special-members" class="admin-nav__item<?= current_path() === '/admin/special-members' || current_path() === '/admin/special-member' ? ' admin-nav__item--active' : '' ?>">特別会員</a>
      <a href="/admin/inquiries" class="admin-nav__item<?= current_path() === '/admin/inquiries' ? ' admin-nav__item--active' : '' ?>">その他</a>
    </nav>
  </div>

  <main class="admin-main">
    <?= $content ?? '' ?>
  </main>
</div>
