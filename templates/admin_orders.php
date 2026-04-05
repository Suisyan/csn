<?php declare(strict_types=1); ?>
<section class="admin-page">
  <div class="admin-page__header">
    <p class="admin-page__eyebrow">Orders</p>
    <h1 class="admin-page__title"><?= e((string) ($pageTitle ?? '受注管理')) ?></h1>
    <p class="admin-page__lead"><?= e((string) ($pageLead ?? '')) ?></p>
  </div>

  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>注文番号</th>
          <th>注文日時</th>
          <th>会社名 / 氏名</th>
          <th>支払方法</th>
          <th>合計</th>
          <th>配送</th>
          <th>追跡番号</th>
          <th>状態</th>
        </tr>
      </thead>
      <tbody>
        <?php if (($pendingOrders ?? []) === []): ?>
          <tr>
            <td colspan="8">現在、表示できる未完了受注はありません。</td>
          </tr>
        <?php else: ?>
          <?php foreach (($pendingOrders ?? []) as $order): ?>
            <tr>
              <td><a href="/admin/orders?s_id=<?= e((string) ($order['s_id'] ?? '')) ?>" class="admin-link"><?= e((string) ($order['s_id'] ?? '')) ?></a></td>
              <td><?= e((string) ($order['ordered_at_label'] ?? '')) ?></td>
              <td>
                <?= e((string) ($order['su_shop'] ?? '')) ?><br>
                <span><?= e((string) ($order['su_name'] ?? '')) ?></span>
              </td>
              <td><?= e((string) ($order['payment_label'] ?? '-')) ?></td>
              <td>¥<?= number_format((int) ($order['total_amount'] ?? 0)) ?></td>
              <td><?= e((string) ($order['transport_label'] ?? '-')) ?></td>
              <td><?= e((string) ($order['tracking_label'] ?? '-')) ?></td>
              <td><?= e((string) ($order['shipment_label'] ?? '-')) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
