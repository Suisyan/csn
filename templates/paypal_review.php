<?php declare(strict_types=1); ?>
<section class="page-block">
  <div class="page-header page-header--cart">
    <p class="hero__kicker">PayPal Express Checkout</p>
    <h1 class="page-title">PayPal 決済内容のご確認</h1>
    <p class="lead">このボタンを押すまで、PayPal 決済は完了しません。内容を確認して最終確定してください。</p>
  </div>

  <div class="section-stack">
    <div class="success-box">
      注文番号 <strong><?= e((string) $orderId) ?></strong> / ご請求金額 <strong>¥<?= number_format((int) ($amount ?? 0)) ?></strong>
    </div>

    <div class="content-grid">
      <article class="detail-panel">
        <h2>PayPal 情報</h2>
        <div class="detail-meta">
          <div class="detail-meta__item"><strong>PayPal ご登録名</strong><span><?= e((string) ($payerName ?? '')) ?></span></div>
          <div class="detail-meta__item"><strong>PayPal メールアドレス</strong><span><?= e((string) ($payerEmail ?? '')) ?></span></div>
          <div class="detail-meta__item"><strong>注文番号</strong><span><?= e((string) $orderId) ?></span></div>
        </div>
      </article>

      <article class="detail-panel">
        <h2>ご注文情報</h2>
        <div class="detail-meta">
          <div class="detail-meta__item"><strong>お届け先</strong><span><?= e((string) (($order['su_name'] ?? '') !== '' ? $order['su_name'] : '未登録')) ?></span></div>
          <div class="detail-meta__item"><strong>会社名・屋号</strong><span><?= e((string) ($order['su_shop'] ?? '')) ?></span></div>
          <div class="detail-meta__item"><strong>ご登録メール</strong><span><?= e((string) ($order['u_email'] ?? '')) ?></span></div>
          <div class="detail-meta__item"><strong>お支払い方法</strong><span>PayPal</span></div>
        </div>
      </article>
    </div>

    <form class="detail-panel" action="/thanks_pp.php" method="post">
      <input type="hidden" name="t" value="<?= e((string) $token) ?>">
      <input type="hidden" name="p" value="<?= e((string) $payerId) ?>">
      <input type="hidden" name="s_id" value="<?= e((string) $orderId) ?>">
      <div class="action-row">
        <button class="button button--primary action-row__priority" type="submit">PayPal 決済を確定する</button>
        <a class="button button--ghost" href="/pp_cancel.php?s_id=<?= e((string) $orderId) ?>">キャンセルする</a>
      </div>
      <p class="muted">旧サイトと同様に、この確定操作が終わるまで注文は PayPal 完了扱いになりません。</p>
    </form>
  </div>
</section>
