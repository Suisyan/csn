<?php declare(strict_types=1); ?>
<section class="page-block">
  <div class="page-header">
    <p class="hero__kicker">Secure Auth</p>
    <h1 class="page-title">ログイン</h1>
    <p class="lead">会員と特別会員のログイン画面です。特別会員受付後は、この画面から入り、名刺画像アップロードへ進めます。</p>
  </div>

  <div class="section-stack">
    <?php if (($success ?? false) === true): ?>
      <div class="success-box">
        ログインに成功しました。現在の会員区分は <?= e(account_label($user ?? null)) ?> です。<?= e(account_summary($user ?? null)) ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($user) && ($success ?? false) !== true): ?>
      <div class="success-box">
        ログイン中: <?= e((string) ($user['name'] ?? $user['email'] ?? '')) ?> / <?= e(account_label($user)) ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
      <div class="errors"><?= e($error) ?></div>
    <?php endif; ?>

    <form class="form-card" action="/login" method="post">
      <div class="form-grid">
        <label>
          メールアドレス
          <input type="email" name="email" value="<?= e((string) old('email')) ?>" data-auto-focus>
        </label>
        <label>
          パスワード
          <input type="password" name="password">
        </label>
      </div>
      <p><button type="submit">ログイン</button></p>
    </form>
    <p class="muted">非会員は購入のみ利用できます。ログインは会員アカウントのみ利用できます。</p>
  </div>
</section>
