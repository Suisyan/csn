<?php declare(strict_types=1); ?>
<section class="page-block">
  <div class="page-header">
    <p class="hero__kicker">Secure Auth</p>
    <h1 class="page-title">ログイン</h1>
    <p class="lead">平文パスワードをやめ、ハッシュ化前提に切り替えるための新しい入口です。</p>
  </div>

  <div class="section-stack">
    <?php if (($success ?? false) === true): ?>
      <div class="success-box">ログイン認証に成功しました。</div>
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
  </div>
</section>
