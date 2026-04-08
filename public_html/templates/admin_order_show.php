<?php declare(strict_types=1); ?>
<section class="admin-page">
  <div class="admin-page__header">
    <p class="admin-page__eyebrow">Order Detail</p>
    <h1 class="admin-page__title">受注明細 #<?= e((string) ($order['s_id'] ?? '')) ?></h1>
    <p class="admin-page__lead">旧管理画面に近い情報量で、注文情報・配送先・商品明細をまとめて確認できます。</p>
  </div>

  <?= render('admin_order_detail', [
      'order' => $order,
      'lines' => $lines ?? [],
      'delivery' => $delivery ?? null,
      'notice' => $notice ?? null,
      'error' => $error ?? null,
      'modalReturnTo' => $modalReturnTo ?? 'orders',
      'modalPage' => $modalPage ?? 1,
  ]) ?>

  <div class="admin-panel__footer">
    <a href="/admin/orders" class="v0-button v0-button--outline">受注一覧へ戻る</a>
  </div>
</section>
