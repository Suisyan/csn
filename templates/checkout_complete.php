<?php declare(strict_types=1); ?>
<section class="page-block">
  <div class="page-header page-header--cart">
    <p class="hero__kicker">Order Complete</p>
    <h1 class="page-title">ご注文ありがとうございました</h1>
    <p class="lead">注文内容を受け付けました。確認メールをご登録メールアドレス宛に送信しています。</p>
  </div>

  <div class="section-stack">
    <div class="success-box">
      注文番号は <strong><?= e((string) ($orderId ?? 0)) ?></strong> です。
    </div>

    <div class="content-grid">
      <article class="detail-panel">
        <h2>お支払い案内</h2>
        <div class="detail-meta">
          <div class="detail-meta__item"><strong>お支払い方法</strong><span><?= e((string) ($paymentLabel ?? '')) ?></span></div>
          <div class="detail-meta__item"><strong>ご請求予定額</strong><span>¥<?= number_format((int) (($summary['total'] ?? 0))) ?></span></div>
          <?php if ((int) ($summary['point_discount'] ?? 0) > 0): ?>
            <div class="detail-meta__item"><strong>クールポイント利用</strong><span>-<?= number_format((int) ($summary['point_discount'] ?? 0)) ?> pt</span></div>
          <?php endif; ?>
          <?php if (($summary['point_eligible'] ?? false) === true): ?>
            <div class="detail-meta__item"><strong>加算予定ポイント</strong><span><?= number_format((int) ($summary['point_earned'] ?? 0)) ?> pt</span></div>
          <?php endif; ?>
        </div>
        <?php if (!empty($paypalUrl)): ?>
          <p class="muted">PayPalをご利用の場合は、注文メールに記載した下記URLからお支払いください。</p>
          <p><a class="button button--primary action-row__priority" href="<?= e((string) $paypalUrl) ?>" target="_blank" rel="noopener">PayPal決済へ進む</a></p>
          <p class="muted"><?= e((string) $paypalUrl) ?></p>
        <?php elseif (($prefill['payment'] ?? '') === 'bank'): ?>
          <p class="muted">銀行振込のご案内は、注文確認後にメールでご連絡します。</p>
        <?php else: ?>
          <p class="muted">代金引換で発送時にお支払いください。</p>
        <?php endif; ?>
      </article>

      <article class="detail-panel">
        <h2>お届け先</h2>
        <div class="detail-meta">
          <div class="detail-meta__item"><strong>お名前</strong><span><?= e((string) ($prefill['shipping_name'] ?? '')) ?></span></div>
          <div class="detail-meta__item"><strong>会社名・屋号</strong><span><?= e((string) ($prefill['shipping_shop'] ?? '')) ?></span></div>
          <div class="detail-meta__item"><strong>郵便番号</strong><span><?= e((string) ($prefill['shipping_zip'] ?? '')) ?></span></div>
          <div class="detail-meta__item"><strong>住所</strong><span><?= e(trim((string) (($prefill['shipping_address1'] ?? '') . ' ' . ($prefill['shipping_address2'] ?? '') . ' ' . ($prefill['shipping_address3'] ?? '')))) ?></span></div>
          <div class="detail-meta__item"><strong>TEL</strong><span><?= e((string) ($prefill['shipping_tel'] ?? '')) ?></span></div>
        </div>
      </article>
    </div>

    <div class="action-row">
      <a class="button button--primary" href="/search">商品検索へ戻る</a>
      <a class="button button--ghost" href="<?= e(storefront_home_url()) ?>">トップへ戻る</a>
    </div>
  </div>
</section>
