<?php declare(strict_types=1); ?>
<section class="admin-login">
  <div class="admin-login__card">
    <p class="admin-login__eyebrow">Admin Login</p>
    <h1 class="admin-login__title">管理画面ログイン</h1>
    <p class="admin-login__lead">受注管理と商品管理を含む管理画面へ入るにはログインが必要です。</p>

    <?php if (!empty($error)): ?>
      <div class="admin-login__error"><?= e((string) $error) ?></div>
    <?php endif; ?>

    <form action="/admin/login" method="post" class="admin-login__form">
      <label>
        ユーザー名
        <input type="text" name="username" autocomplete="username">
      </label>
      <label>
        パスワード
        <input type="password" name="password" autocomplete="current-password">
      </label>
      <input type="hidden" name="redirect_to" value="<?= e((string) ($redirectTo ?? '/admin')) ?>">
      <button type="submit" class="v0-button">ログイン</button>
    </form>
  </div>
</section>
