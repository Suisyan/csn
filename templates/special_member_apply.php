<?php declare(strict_types=1); ?>
<section class="page-block">
  <div class="page-header page-header--product">
    <p class="hero__kicker">特別会員</p>
    <h1 class="page-title">受付フォーム</h1>
    <p class="lead">未ログイン、未購入でも特別会員申請を受け付けます。</p>
  </div>

  <?php if ($success): ?>
    <div class="success-box">
      受付を保存しました。登録メールアドレスとパスワードでログインし、名刺画像アップロードを続けてください。
    </div>
  <?php else: ?>
    <?php if ($errors !== []): ?>
      <div class="errors">
        <?php foreach ($errors as $error): ?>
          <div><?= e((string) $error) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form class="form-card stack" action="/special-member/register" method="post">
      <div class="form-grid">
        <label>
          会社名
          <input type="text" name="company_name" value="<?= e((string) ($prefill['company_name'] ?? '')) ?>">
        </label>
        <label>
          店舗名
          <input type="text" name="shop_name" value="<?= e((string) ($prefill['shop_name'] ?? '')) ?>">
        </label>
        <label>
          ご担当者名
          <input type="text" name="contact_name" value="<?= e((string) ($prefill['contact_name'] ?? '')) ?>">
        </label>
        <label>
          Email
          <input type="email" name="email" value="<?= e((string) ($prefill['email'] ?? '')) ?>">
        </label>
        <label>
          TEL
          <input type="text" name="tel" value="<?= e((string) ($prefill['tel'] ?? '')) ?>">
        </label>
        <label>
          郵便番号
          <input type="text" name="zip" value="<?= e((string) ($prefill['zip'] ?? '')) ?>">
        </label>
        <label>
          住所1
          <input type="text" name="address_line1" value="<?= e((string) ($prefill['address_line1'] ?? '')) ?>">
        </label>
        <label>
          住所2
          <input type="text" name="address_line2" value="<?= e((string) ($prefill['address_line2'] ?? '')) ?>">
        </label>
        <label>
          住所3
          <input type="text" name="address_line3" value="<?= e((string) ($prefill['address_line3'] ?? '')) ?>">
        </label>
        <label>
          WebサイトURL
          <input type="text" name="website_url" value="<?= e((string) ($prefill['website_url'] ?? '')) ?>">
        </label>
        <label>
          業種
          <input type="text" name="business_type" value="<?= e((string) ($prefill['business_type'] ?? '')) ?>">
        </label>
        <label>
          パスワード
          <input type="password" name="password">
        </label>
        <label>
          パスワード確認
          <input type="password" name="password_confirm">
        </label>
      </div>

      <label>
        備考
        <textarea name="notes"><?= e((string) ($prefill['notes'] ?? '')) ?></textarea>
      </label>

      <label>
        <input type="checkbox" name="agreed_terms" value="yes" <?= (($prefill['agreed_terms'] ?? '') === 'yes') ? 'checked' : '' ?>>
        特別会員申請の審査と受付条件に同意します。
      </label>

      <div class="action-row">
        <a class="button button--ghost" href="/">トップへ戻る</a>
        <button type="submit" class="button button--primary">受付を送信</button>
      </div>
    </form>
  <?php endif; ?>
</section>
