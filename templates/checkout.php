<?php declare(strict_types=1); ?>
<?php $prefill = $prefill ?? []; $summary = $summary ?? ['lines' => []]; ?>
<section class="page-block">
  <div class="page-header page-header--cart">
    <p class="hero__kicker">Checkout</p>
    <h1 class="page-title">ご注文手続き</h1>
    <p class="lead">旧 `checkout.php` の役割を共通カートからの注文導線へ統合しました。お客様情報、お届け先、お支払い方法を確認してください。</p>
  </div>

  <div class="section-stack">
    <?php if (($errors ?? []) !== []): ?>
      <div class="errors">
        <?php foreach (($errors ?? []) as $error): ?>
          <div><?= e($error) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="result-summary">
      <div>
        <span class="pill pill--mode">注文内容</span>
        <h2 class="result-summary__title">カート内容の最終確認</h2>
        <p class="muted">銀行振込、代金引換、PayPal案内のいずれかを選べます。PayPal は注文完了メールに記載されるURLからお支払いします。</p>
      </div>
      <div class="result-summary__filters">
        <div class="summary-chip"><span>商品点数</span><strong><?= count($summary['lines'] ?? []) ?>件</strong></div>
        <div class="summary-chip"><span>商品合計</span><strong>¥<?= number_format((int) ($summary['subtotal'] ?? 0)) ?></strong></div>
        <div class="summary-chip"><span>送料</span><strong>¥<?= number_format((int) ($summary['shipping_fee'] ?? 0)) ?></strong></div>
        <?php if (($summary['point_eligible'] ?? false) === true): ?>
          <div class="summary-chip"><span>利用可能Pt</span><strong><?= number_format((int) ($summary['point_available'] ?? 0)) ?> pt</strong></div>
        <?php endif; ?>
        <div class="summary-chip"><span>現時点合計</span><strong>¥<?= number_format((int) ($summary['total'] ?? 0)) ?></strong></div>
      </div>
    </div>

    <form class="form-card" action="/checkout/confirm" method="post">
      <h2>ご購入者情報</h2>
      <div class="form-grid">
        <label>お名前<input type="text" name="customer_name" value="<?= e((string) ($prefill['customer_name'] ?? '')) ?>"></label>
        <label>会社名・屋号<input type="text" name="customer_shop" value="<?= e((string) ($prefill['customer_shop'] ?? '')) ?>"></label>
        <label>郵便番号<input type="text" name="customer_zip" value="<?= e((string) ($prefill['customer_zip'] ?? '')) ?>"></label>
        <label>TEL<input type="text" name="customer_tel" value="<?= e((string) ($prefill['customer_tel'] ?? '')) ?>"></label>
        <label>E-mail<input type="email" name="customer_email" value="<?= e((string) ($prefill['customer_email'] ?? '')) ?>"></label>
        <label>住所1<input type="text" name="customer_address1" value="<?= e((string) ($prefill['customer_address1'] ?? '')) ?>"></label>
        <label>住所2<input type="text" name="customer_address2" value="<?= e((string) ($prefill['customer_address2'] ?? '')) ?>"></label>
        <label>住所3<input type="text" name="customer_address3" value="<?= e((string) ($prefill['customer_address3'] ?? '')) ?>"></label>
      </div>

      <h2>お届け先</h2>
      <label>
        <input type="checkbox" name="same_as_customer" value="yes" <?= ($prefill['same_as_customer'] ?? 'yes') === 'yes' ? 'checked' : '' ?>>
        ご購入者情報と同じ
      </label>
      <div class="form-grid">
        <label>お届け先名<input type="text" name="shipping_name" value="<?= e((string) ($prefill['shipping_name'] ?? '')) ?>"></label>
        <label>会社名・屋号<input type="text" name="shipping_shop" value="<?= e((string) ($prefill['shipping_shop'] ?? '')) ?>"></label>
        <label>郵便番号<input type="text" name="shipping_zip" value="<?= e((string) ($prefill['shipping_zip'] ?? '')) ?>"></label>
        <label>TEL<input type="text" name="shipping_tel" value="<?= e((string) ($prefill['shipping_tel'] ?? '')) ?>"></label>
        <label>住所1<input type="text" name="shipping_address1" value="<?= e((string) ($prefill['shipping_address1'] ?? '')) ?>"></label>
        <label>住所2<input type="text" name="shipping_address2" value="<?= e((string) ($prefill['shipping_address2'] ?? '')) ?>"></label>
        <label>住所3<input type="text" name="shipping_address3" value="<?= e((string) ($prefill['shipping_address3'] ?? '')) ?>"></label>
        <label>
          配達時間帯
          <select name="delivery_time">
            <option value="">指定なし</option>
            <?php foreach (['午前中', '14-16時', '16-18時', '18-20時', '19-21時'] as $slot): ?>
              <option value="<?= e($slot) ?>" <?= ($prefill['delivery_time'] ?? '') === $slot ? 'selected' : '' ?>><?= e($slot) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
      </div>

      <h2>お支払い方法</h2>
      <div class="tab-row">
        <label class="tab-row__item<?= ($prefill['payment'] ?? 'bank') === 'bank' ? ' tab-row__item--active' : '' ?>">
          <input type="radio" name="payment" value="bank" <?= ($prefill['payment'] ?? 'bank') === 'bank' ? 'checked' : '' ?>>
          銀行振込
        </label>
        <label class="tab-row__item<?= ($prefill['payment'] ?? '') === 'yamato' ? ' tab-row__item--active' : '' ?>">
          <input type="radio" name="payment" value="yamato" <?= ($prefill['payment'] ?? '') === 'yamato' ? 'checked' : '' ?>>
          代金引換
        </label>
        <label class="tab-row__item<?= ($prefill['payment'] ?? '') === 'card' ? ' tab-row__item--active' : '' ?>">
          <input type="radio" name="payment" value="card" <?= ($prefill['payment'] ?? '') === 'card' ? 'checked' : '' ?>>
          PayPal案内
        </label>
      </div>
      <p class="muted">PayPal を選んだ場合は、注文完了メールに記載されるURLからお支払いください。サイト内で直接カード決済は行いません。</p>

      <?php if (($summary['point_eligible'] ?? false) === true): ?>
        <h2>クールポイント</h2>
        <div class="form-grid">
          <label>
            利用ポイント
            <input type="text" name="coolpoint_use" inputmode="numeric" value="<?= e((string) ($prefill['coolpoint_use'] ?? '0')) ?>">
          </label>
        </div>
        <p class="muted">現在の利用可能ポイント: <?= number_format((int) ($summary['point_available'] ?? 0)) ?> pt / 今回加算予定: <?= number_format((int) ($summary['point_earned'] ?? 0)) ?> pt</p>
      <?php else: ?>
        <input type="hidden" name="coolpoint_use" value="0">
      <?php endif; ?>

      <label>
        備考・ご要望
        <textarea name="notes"><?= e((string) ($prefill['notes'] ?? '')) ?></textarea>
      </label>

      <div class="action-row">
        <a class="button button--ghost" href="/cart">カートへ戻る</a>
        <button type="submit" class="button button--primary">ご注文内容を確認する</button>
      </div>
    </form>
  </div>
</section>
