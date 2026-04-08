<?php declare(strict_types=1); ?>
<section class="page-block">
  <div class="page-header page-header--cart">
    <p class="hero__kicker">PayPal</p>
    <h1 class="page-title"><?= e((string) ($title ?? 'PayPal')) ?></h1>
    <p class="lead"><?= e((string) ($message ?? '')) ?></p>
  </div>

  <div class="section-stack">
    <div class="success-box">
      注文番号は <strong><?= e((string) ($orderId ?? 0)) ?></strong> です。
    </div>

    <article class="detail-panel">
      <div class="detail-meta">
        <div class="detail-meta__item"><strong>PayPal 状態</strong><span><?= e((string) ($paymentStatus ?? '')) ?></span></div>
        <?php if (!empty($transactionId)): ?>
          <div class="detail-meta__item"><strong>取引ID</strong><span><?= e((string) $transactionId) ?></span></div>
        <?php endif; ?>
        <?php if (is_array($order)): ?>
          <div class="detail-meta__item"><strong>ご請求予定額</strong><span>¥<?= number_format((int) ($order['total_amount'] ?? 0)) ?></span></div>
          <div class="detail-meta__item"><strong>お届け先</strong><span><?= e((string) ($order['su_name'] ?? '')) ?></span></div>
        <?php endif; ?>
      </div>
    </article>

    <div class="action-row">
      <a class="button button--primary" href="/account">マイページへ戻る</a>
      <a class="button button--ghost" href="/search">商品検索へ戻る</a>
    </div>
  </div>
</section>
