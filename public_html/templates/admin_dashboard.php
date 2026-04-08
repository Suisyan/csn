<?php declare(strict_types=1); ?>
<?php
$modalOpen = is_array($modalOrder ?? null);
$closeModalHref = (string) ($closeModalHref ?? '/admin');
$modalShellStyle = 'position:fixed;top:0;right:0;bottom:0;left:0;width:100vw;height:100vh;padding:16px;z-index:99999;place-items:center;';
$modalBackdropStyle = 'position:absolute;top:0;right:0;bottom:0;left:0;display:block;background:rgba(15,23,42,0.48);';
$modalDialogStyle = 'position:relative;width:min(920px,100%);max-height:100%;overflow:auto;margin:0;border-radius:24px;border:1px solid rgba(15,23,42,0.08);background:#f8f7f3;box-shadow:0 24px 80px rgba(15,23,42,0.24);';
?>
<section class="admin-page">
  <div class="admin-page__header">
    <p class="admin-page__eyebrow">Admin</p>
    <h1 class="admin-page__title"><?= e((string) ($pageTitle ?? '管理画面トップ')) ?></h1>
    <p class="admin-revision-badge">確認用 改版日時: 2026-04-08 19:24</p>
    <p class="admin-page__lead"><?= e((string) ($pageLead ?? '')) ?></p>
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

  <div class="admin-panel">
    <div class="admin-panel__header">
      <div>
        <p class="admin-page__eyebrow">Pending Orders</p>
        <h2 class="admin-panel__title">未完了の受注</h2>
      </div>
      <div class="admin-panel__meta">
        <strong><?= number_format((int) ($pendingCount ?? 0)) ?></strong>
        <span>件</span>
      </div>
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
            <th>状態</th>
          </tr>
        </thead>
        <tbody>
          <?php if (($pendingOrders ?? []) === []): ?>
            <tr>
              <td colspan="7">現在、未完了の受注はありません。</td>
            </tr>
          <?php else: ?>
            <?php foreach (($pendingOrders ?? []) as $order): ?>
              <tr>
                <td>
                  <a
                    href="/admin?modal_order_id=<?= e((string) ($order['s_id'] ?? '')) ?>"
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
                <td><?= e((string) ($order['shipment_label'] ?? '-')) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="admin-panel__footer">
      <a href="/admin/orders" class="v0-button v0-button--outline">受注一覧を見る</a>
    </div>
  </div>

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
              'modalReturnTo' => $modalReturnTo ?? 'dashboard',
              'modalPage' => $modalPage ?? 1,
          ]) ?>
        <?php else: ?>
          <div class="admin-order-modal__loading">受注明細を読み込み中です。</div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="admin-panel">
    <div class="admin-panel__header">
      <div>
        <p class="admin-page__eyebrow">Special Members</p>
        <h2 class="admin-panel__title">特別会員の申請保留</h2>
      </div>
      <div class="admin-panel__meta">
        <strong><?= number_format((int) ($specialPendingCount ?? 0)) ?></strong>
        <span>件</span>
      </div>
    </div>

    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>申請ID</th>
            <th>会社名 / 担当者</th>
            <th>Email</th>
            <th>状態</th>
            <th>申請日時</th>
          </tr>
        </thead>
        <tbody>
          <?php if (($specialPendingRequests ?? []) === []): ?>
            <tr>
              <td colspan="5">現在、保留中の特別会員申請はありません。</td>
            </tr>
          <?php else: ?>
            <?php foreach (($specialPendingRequests ?? []) as $request): ?>
              <tr>
                <td><?= e((string) ($request['id'] ?? '')) ?></td>
                <td>
                  <?= e((string) ($request['company_name'] ?? '')) ?><br>
                  <span><?= e((string) ($request['contact_name'] ?? '')) ?></span>
                </td>
                <td><?= e((string) ($request['email'] ?? '-')) ?></td>
                <td><?= e(special_member_status_label((string) ($request['status'] ?? ''))) ?></td>
                <td><?= e((string) ($request['requested_at'] ?? '-')) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="admin-panel__footer">
      <a href="/admin/special-members" class="v0-button v0-button--outline">申請一覧を見る</a>
    </div>
  </div>

  <div class="admin-grid">
    <article class="admin-card">
      <h2>受注管理</h2>
      <p>受注一覧、受注明細、発送メール、入金確認をここへ統合していく予定です。</p>
      <span class="admin-card__status">次の実装対象</span>
    </article>
    <article class="admin-card">
      <h2>商品管理</h2>
      <p>商品・型式・OEM・画像アップロードを旧管理画面に近い並びで整理します。</p>
      <a href="/admin/products" class="v0-button v0-button--outline">商品管理を開く</a>
    </article>
    <article class="admin-card">
      <h2>特別会員申請</h2>
      <p>現在の申請承認画面はこの管理画面の中で引き続き利用できます。</p>
      <a href="/admin/special-members" class="v0-button v0-button--outline">申請一覧を見る</a>
    </article>
  </div>
</section>
