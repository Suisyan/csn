<?php declare(strict_types=1); ?>
<section class="page-block">
  <div class="page-header">
    <p class="hero__kicker">Inquiry</p>
    <h1 class="page-title">お問い合わせ</h1>
    <p class="lead">旧 `inquiry.php` と `mail_send.php` の役割を1導線に整理する新フォームです。</p>
  </div>

  <div class="section-stack">
    <?php if ($errors !== []): ?>
      <div class="errors">
        <?php foreach ($errors as $error): ?>
          <div><?= e($error) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if (($success ?? false) === true): ?>
      <div class="success-box">お問い合わせを受け付けました。</div>
    <?php endif; ?>

    <form class="form-card" action="/inquiry" method="post">
      <div class="form-grid">
        <label>
          お名前
          <input type="text" name="name" value="<?= e((string) old('name')) ?>" data-auto-focus>
        </label>
        <label>
          E-mail
          <input type="email" name="email" value="<?= e((string) old('email')) ?>">
        </label>
        <label>
          TEL
          <input type="text" name="tel" value="<?= e((string) old('tel')) ?>">
        </label>
        <label>
          部品区分
          <select name="category">
            <option value="">選択してください</option>
            <option value="ラジエーター" <?= old('category') === 'ラジエーター' ? 'selected' : '' ?>>ラジエーター</option>
            <option value="コンデンサー" <?= old('category') === 'コンデンサー' ? 'selected' : '' ?>>コンデンサー</option>
          </select>
        </label>
        <label>
          型式
          <input type="text" name="katasiki" value="<?= e((string) old('katasiki')) ?>" placeholder="例: E-AE91">
        </label>
        <label>
          品番
          <input type="text" name="parts_num" value="<?= e((string) old('parts_num')) ?>">
        </label>
        <label>
          ミッション
          <select name="toc">
            <option value="">選択してください</option>
            <option value="A/T" <?= old('toc') === 'A/T' ? 'selected' : '' ?>>A/T</option>
            <option value="M/T" <?= old('toc') === 'M/T' ? 'selected' : '' ?>>M/T</option>
            <option value="CVT" <?= old('toc') === 'CVT' ? 'selected' : '' ?>>CVT</option>
          </select>
        </label>
      </div>

      <label>
        コメント
        <textarea name="message"><?= e((string) old('message')) ?></textarea>
      </label>

      <p><button type="submit">送信する</button></p>
    </form>
  </div>
</section>
