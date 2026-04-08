<?php declare(strict_types=1); ?>
<?php $prefill = $prefill ?? []; $summary = $summary ?? ['lines' => []]; ?>
<section class="page-block">
  <div class="page-header page-header--cart">
    <p class="hero__kicker">Confirm</p>
    <h1 class="page-title">ご注文内容確認</h1>
    <p class="lead">旧 `confirm.php` 相当の確認画面です。内容に問題がなければ注文を確定してください。</p>
  </div>

  <div class="section-stack">
    <div class="content-grid">
      <article class="detail-panel">
        <h2>ご購入者情報</h2>
        <div class="detail-meta">
          <div class="detail-meta__item"><strong>お名前</strong><span><?= e((string) ($prefill['customer_name'] ?? '')) ?></span></div>
          <div class="detail-meta__item"><strong>会社名・屋号</strong><span><?= e((string) ($prefill['customer_shop'] ?? '')) ?></span></div>
          <div class="detail-meta__item"><strong>郵便番号</strong><span><?= e((string) ($prefill['customer_zip'] ?? '')) ?></span></div>
          <div class="detail-meta__item"><strong>住所</strong><span><?= e(trim(($prefill['customer_address1'] ?? '') . ' ' . ($prefill['customer_address2'] ?? '') . ' ' . ($prefill['customer_address3'] ?? ''))) ?></span></div>
          <div class="detail-meta__item"><strong>TEL</strong><span><?= e((string) ($prefill['customer_tel'] ?? '')) ?></span></div>
          <div class="detail-meta__item"><strong>E-mail</strong><span><?= e((string) ($prefill['customer_email'] ?? '')) ?></span></div>
        </div>
      </article>

      <article class="detail-panel">
        <h2>お届け先・お支払い</h2>
        <div class="detail-meta">
          <div class="detail-meta__item"><strong>お届け先名</strong><span><?= e((string) ($prefill['shipping_name'] ?? '')) ?></span></div>
          <div class="detail-meta__item"><strong>会社名・屋号</strong><span><?= e((string) ($prefill['shipping_shop'] ?? '')) ?></span></div>
          <div class="detail-meta__item"><strong>郵便番号</strong><span><?= e((string) ($prefill['shipping_zip'] ?? '')) ?></span></div>
          <div class="detail-meta__item"><strong>住所</strong><span><?= e(trim(($prefill['shipping_address1'] ?? '') . ' ' . ($prefill['shipping_address2'] ?? '') . ' ' . ($prefill['shipping_address3'] ?? ''))) ?></span></div>
          <div class="detail-meta__item"><strong>TEL</strong><span><?= e((string) ($prefill['shipping_tel'] ?? '')) ?></span></div>
          <div class="detail-meta__item"><strong>配達時間帯</strong><span><?= e((string) (($prefill['delivery_time'] ?? '') !== '' ? $prefill['delivery_time'] : '指定なし')) ?></span></div>
          <div class="detail-meta__item"><strong>お支払い方法</strong><span><?= e(match ((string) ($prefill['payment'] ?? 'bank')) { 'yamato' => '代金引換', 'card' => 'PayPal案内', default => '銀行振込' }) ?></span></div>
        </div>
        <?php if (($prefill['payment'] ?? '') === 'card'): ?>
          <p class="muted">PayPal のお支払いURLは、注文完了後に表示されるリンクと注文完了メールから進めます。</p>
          <?php if (!empty($paypalUrl)): ?>
            <p class="muted">案内URL例: <?= e((string) $paypalUrl) ?></p>
          <?php endif; ?>
        <?php endif; ?>
      </article>
    </div>

    <div class="detail-panel">
      <h2>注文商品</h2>
      <div class="cart-list">
        <?php foreach (($summary['lines'] ?? []) as $line): ?>
          <?php $product = $line['product']; ?>
          <article class="cart-card">
            <div class="cart-card__main">
              <div class="cart-card__meta">
                <span class="pill"><?= e((string) ($product['category'] ?? '')) ?></span>
                <span class="catalog-code"><?= e((string) ($product['parts_num'] ?? '')) ?></span>
              </div>
              <h3 class="cart-card__title"><?= e((string) ($product['make'] ?? '')) ?> <?= e((string) ($product['name'] ?? '')) ?></h3>
            </div>
            <div class="cart-card__side">
              <div class="detail-meta">
                <div class="detail-meta__item"><strong>数量</strong><span><?= e((string) ($line['qty'] ?? 0)) ?></span></div>
                <div class="detail-meta__item"><strong>単価</strong><span>¥<?= number_format((int) ($line['unit_price'] ?? 0)) ?></span></div>
                <div class="detail-meta__item"><strong>小計</strong><span>¥<?= number_format((int) ($line['subtotal'] ?? 0)) ?></span></div>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
      <div class="cart-summary">
        <div>
          <p class="cart-summary__label">商品合計</p>
          <p class="cart-summary__label">送料</p>
          <p class="cart-summary__label">手数料</p>
          <?php if ((int) ($summary['point_discount'] ?? 0) > 0): ?>
            <p class="cart-summary__label">クールポイント利用</p>
          <?php endif; ?>
        </div>
        <div>
          <p class="cart-summary__label">¥<?= number_format((int) ($summary['subtotal'] ?? 0)) ?></p>
          <p class="cart-summary__label">¥<?= number_format((int) ($summary['shipping_fee'] ?? 0)) ?></p>
          <p class="cart-summary__label">¥<?= number_format((int) ($summary['payment_fee'] ?? 0)) ?></p>
          <?php if ((int) ($summary['point_discount'] ?? 0) > 0): ?>
            <p class="cart-summary__label">-¥<?= number_format((int) ($summary['point_discount'] ?? 0)) ?></p>
          <?php endif; ?>
        </div>
        <div>
          <p class="cart-summary__label">ご請求予定額</p>
          <p class="cart-summary__total">¥<?= number_format((int) ($summary['total'] ?? 0)) ?></p>
        </div>
      </div>
      <?php if (($summary['point_eligible'] ?? false) === true): ?>
        <p class="muted">今回加算予定ポイント: <?= number_format((int) ($summary['point_earned'] ?? 0)) ?> pt</p>
      <?php endif; ?>
    </div>

    <form class="detail-panel" action="/checkout/complete" method="post">
      <?php foreach ($prefill as $key => $value): ?>
        <input type="hidden" name="<?= e((string) $key) ?>" value="<?= e((string) $value) ?>">
      <?php endforeach; ?>
      <div class="action-row">
        <button class="button button--ghost" type="button" onclick="history.back()">入力画面へ戻る</button>
        <button class="button button--primary" type="submit">注文を確定する</button>
      </div>
    </form>
  </div>
</section>
