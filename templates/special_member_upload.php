<?php declare(strict_types=1); ?>
<section class="page-block">
  <div class="page-header page-header--product">
    <p class="hero__kicker">特別会員</p>
    <h1 class="page-title">名刺画像アップロード</h1>
    <p class="lead">申請後にログインし、名刺画像をアップロードしてください。</p>
  </div>

  <?php if ($success): ?>
    <div class="success-box">
      名刺画像を受け付けました。現在は管理承認待ちです。
    </div>
  <?php else: ?>
    <?php if ($errors !== []): ?>
      <div class="errors">
        <?php foreach ($errors as $error): ?>
          <div><?= e((string) $error) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form class="form-card stack" action="/special-member/upload" method="post" enctype="multipart/form-data">
      <label>
        名刺画像
        <input type="file" name="card_image" accept=".jpg,.jpeg,.png,.pdf">
        <span class="muted">jpg / jpeg / png / pdf, max 5MB</span>
      </label>

      <div class="action-row">
        <a class="button button--ghost" href="/account">会員情報へ戻る</a>
        <button type="submit" class="button button--primary">アップロード</button>
      </div>
    </form>
  <?php endif; ?>
</section>
