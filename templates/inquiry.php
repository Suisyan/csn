<?php declare(strict_types=1); ?>
<?php
  $prefill = $prefill ?? [];
  $fitMode = (bool) ($fitMode ?? false);
  $vehicleMake = trim((string) ($prefill['syamei1'] ?? ''));
  $vehicleName = trim((string) ($prefill['syamei2'] ?? ''));
?>
<section class="page-block">
  <div class="page-header">
    <p class="hero__kicker">Inquiry</p>
    <h1 class="page-title">お問い合わせ</h1>
    <p class="lead">旧 `inquiry.php` と `mail_send.php` の役割を1導線に整理する新フォームです。</p>
  </div>

  <div class="section-stack">
    <?php if ($fitMode): ?>
      <div class="notice notice--hint">
        <h2>適合確認</h2>
        <p class="muted">検索結果から受け取った内容をもとに、必要事項を入力してください。</p>
        <div class="result-grid">
          <div class="result-grid__item"><span>部品区分</span><strong><?= e((string) ($prefill['category'] ?? '-')) ?></strong></div>
          <div class="result-grid__item"><span>品番</span><strong><?= e((string) ($prefill['parts_num'] ?? '-')) ?></strong></div>
          <div class="result-grid__item"><span>型式</span><strong><?= e((string) ($prefill['katasiki'] ?? '-')) ?></strong></div>
          <div class="result-grid__item"><span>車名・モデル</span><strong><?= e(trim($vehicleMake . ' / ' . $vehicleName, ' /')) ?></strong></div>
          <div class="result-grid__item result-grid__item--full"><span>ミッション</span><strong><?= e((string) ($prefill['toc'] ?? '-')) ?></strong></div>
        </div>
      </div>
    <?php endif; ?>

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
      <?php if ($fitMode): ?>
        <input type="hidden" name="source" value="fit">
        <input type="hidden" name="syamei1" value="<?= e((string) ($prefill['syamei1'] ?? '')) ?>">
        <input type="hidden" name="syamei2" value="<?= e((string) ($prefill['syamei2'] ?? '')) ?>">
      <?php endif; ?>
      <div class="form-grid">
        <label>
          お名前
          <input type="text" name="name" value="<?= e((string) old('name', $prefill['name'] ?? '')) ?>" data-auto-focus>
        </label>
        <label>
          E-mail
          <input type="email" name="email" value="<?= e((string) old('email', $prefill['email'] ?? '')) ?>">
        </label>
        <label>
          TEL
          <input type="text" name="tel" value="<?= e((string) old('tel', $prefill['tel'] ?? '')) ?>">
        </label>
        <label>
          部品区分
          <select name="category">
            <option value="">選択してください</option>
            <option value="ラジエーター" <?= old('category', $prefill['category'] ?? '') === 'ラジエーター' ? 'selected' : '' ?>>ラジエーター</option>
            <option value="コンデンサー" <?= old('category', $prefill['category'] ?? '') === 'コンデンサー' ? 'selected' : '' ?>>コンデンサー</option>
          </select>
        </label>
        <label>
          型式
          <input type="text" name="katasiki" value="<?= e((string) old('katasiki', $prefill['katasiki'] ?? '')) ?>" placeholder="例: E-AE91">
        </label>
        <label>
          品番
          <input type="text" name="parts_num" value="<?= e((string) old('parts_num', $prefill['parts_num'] ?? '')) ?>">
        </label>
        <label>
          ミッション
          <select name="toc">
            <option value="">選択してください</option>
            <option value="A/T" <?= old('toc', $prefill['toc'] ?? '') === 'A/T' ? 'selected' : '' ?>>A/T</option>
            <option value="M/T" <?= old('toc', $prefill['toc'] ?? '') === 'M/T' ? 'selected' : '' ?>>M/T</option>
            <option value="CVT" <?= old('toc', $prefill['toc'] ?? '') === 'CVT' ? 'selected' : '' ?>>CVT</option>
          </select>
        </label>
      </div>

      <label>
        コメント
        <textarea name="message"><?= e((string) old('message', $prefill['message'] ?? '')) ?></textarea>
      </label>

      <p><button type="submit">送信する</button></p>
    </form>
  </div>
</section>
