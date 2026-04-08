<?php declare(strict_types=1); ?>
<?php
$modalOrderId = (int) ($modalOrderId ?? 0);
$modalOpen = is_array($modalOrder ?? null);
$closeModalHref = (string) ($closeModalHref ?? '/admin/orders');
$modalShellStyle = 'position:fixed;top:0;right:0;bottom:0;left:0;width:100vw;height:100vh;padding:16px;z-index:99999;place-items:center;';
$modalBackdropStyle = 'position:absolute;top:0;right:0;bottom:0;left:0;display:block;background:rgba(15,23,42,0.48);';
$modalDialogStyle = 'position:relative;width:min(920px,100%);max-height:100%;overflow:auto;margin:0;border-radius:24px;border:1px solid rgba(15,23,42,0.08);background:#f8f7f3;box-shadow:0 24px 80px rgba(15,23,42,0.24);';
?>
<section class="admin-page">
  <div class="admin-page__header">
    <p class="admin-page__eyebrow">Orders</p>
    <h1 class="admin-page__title"><?= e((string) ($pageTitle ?? '受注管理')) ?></h1>
    <p class="admin-page__lead"><?= e((string) ($pageLead ?? '')) ?></p>
    <p class="admin-muted">全 <?= number_format((int) ($totalOrders ?? 0)) ?> 件 / <?= number_format((int) ($currentPage ?? 1)) ?> / <?= number_format((int) ($totalPages ?? 1)) ?> ページ</p>
  </div>

  <?php if (($notice ?? null) !== null): ?>
    <div class="notice notice--success">
      <strong><?= e((string) $notice) ?></strong>
    </div>
  <?php endif; ?>

  <?php if (($error ?? null) !== null): ?>
    <div class="notice notice--error">
      <strong><?= e((string) $error) ?></strong>
    </div>
  <?php endif; ?>

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
              <td>
                <a
                  href="/admin/orders?page=<?= e((string) ($currentPage ?? 1)) ?>&modal_order_id=<?= e((string) ($order['s_id'] ?? '')) ?>"
                  class="admin-link"
                  data-order-modal-link
                  data-order-modal-url="/admin/orders?s_id=<?= e((string) ($order['s_id'] ?? '')) ?>&modal=1"
                ><?= e((string) ($order['s_id'] ?? '')) ?></a>
              </td>
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

  <div class="admin-order-modal" data-order-modal<?= $modalOpen ? '' : ' hidden' ?> style="<?= e($modalShellStyle) ?>">
    <a href="<?= e($closeModalHref) ?>" class="admin-order-modal__backdrop" data-order-modal-close aria-label="閉じる" style="<?= e($modalBackdropStyle) ?>"></a>
    <div class="admin-order-modal__dialog" role="dialog" aria-modal="true" aria-label="受注明細" style="<?= e($modalDialogStyle) ?>">
      <div class="admin-order-modal__header">
        <div>
          <p class="admin-page__eyebrow">Order Detail</p>
          <h2 class="admin-panel__title">受注明細</h2>
        </div>
        <a href="<?= e($closeModalHref) ?>" class="admin-order-modal__close" data-order-modal-close aria-label="閉じる">×</a>
      </div>
      <div class="admin-order-modal__body" data-order-modal-body>
        <?php if ($modalOpen): ?>
          <?= render('admin_order_detail', [
              'order' => $modalOrder,
              'lines' => $modalLines ?? [],
              'delivery' => $modalDelivery ?? null,
              'notice' => $notice ?? null,
              'error' => $error ?? null,
              'modalReturnTo' => $modalReturnTo ?? 'orders',
              'modalPage' => $modalPage ?? 1,
          ]) ?>
        <?php else: ?>
          <div class="admin-order-modal__loading">受注明細を読み込み中です。</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
