<?php declare(strict_types=1); ?>
<section class="admin-page">
  <div class="admin-page__header">
    <p class="admin-page__eyebrow">Orders</p>
    <h1 class="admin-page__title"><?= e((string) ($pageTitle ?? '受注管理')) ?></h1>
    <p class="admin-page__lead"><?= e((string) ($pageLead ?? '')) ?></p>
    <p class="admin-muted">全 <?= number_format((int) ($totalOrders ?? 0)) ?> 件 / <?= number_format((int) ($currentPage ?? 1)) ?> / <?= number_format((int) ($totalPages ?? 1)) ?> ページ</p>
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
        <?php if (($orders ?? []) === []): ?>
          <tr>
            <td colspan="8">現在、表示できる受注はありません。</td>
          </tr>
        <?php else: ?>
          <?php foreach (($orders ?? []) as $order): ?>
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
              <td>
                <span class="<?= ((string) ($order['mail_flag'] ?? '') === '0' || (string) ($order['mail_flag'] ?? '') === '') ? 'admin-stock admin-stock--none' : 'admin-stock admin-stock--ok' ?>">
                  <?= e((string) ($order['shipment_label'] ?? '-')) ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php if ((int) ($totalPages ?? 1) > 1): ?>
    <nav class="admin-pagination" aria-label="受注一覧ページ送り">
      <?php $currentPage = (int) ($currentPage ?? 1); ?>
      <?php $totalPages = (int) ($totalPages ?? 1); ?>
      <?php if ($currentPage > 1): ?>
        <a href="/admin/orders?page=<?= $currentPage - 1 ?>" class="admin-pagination__link">前へ</a>
      <?php endif; ?>

      <?php for ($page = max(1, $currentPage - 2); $page <= min($totalPages, $currentPage + 2); $page++): ?>
        <a href="/admin/orders?page=<?= $page ?>" class="admin-pagination__link<?= $page === $currentPage ? ' admin-pagination__link--active' : '' ?>"><?= $page ?></a>
      <?php endfor; ?>

      <?php if ($currentPage < $totalPages): ?>
        <a href="/admin/orders?page=<?= $currentPage + 1 ?>" class="admin-pagination__link">次へ</a>
      <?php endif; ?>
    </nav>
  <?php endif; ?>
</section>
