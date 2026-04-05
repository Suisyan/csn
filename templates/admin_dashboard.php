<?php declare(strict_types=1); ?>
<section class="admin-page">
  <div class="admin-page__header">
    <p class="admin-page__eyebrow">Admin</p>
    <h1 class="admin-page__title"><?= e((string) ($pageTitle ?? '管理画面トップ')) ?></h1>
    <p class="admin-page__lead"><?= e((string) ($pageLead ?? '')) ?></p>
  </div>

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
                <td><a href="/admin/orders?s_id=<?= e((string) ($order['s_id'] ?? '')) ?>" class="admin-link"><?= e((string) ($order['s_id'] ?? '')) ?></a></td>
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

  <div class="admin-grid">
    <article class="admin-card">
      <h2>受注管理</h2>
      <p>受注一覧、受注明細、発送メール、入金確認をここへ統合していく予定です。</p>
      <span class="admin-card__status">次の実装対象</span>
    </article>
    <article class="admin-card">
      <h2>商品管理</h2>
      <p>商品・型式・OEM・画像アップロードを旧管理画面に近い並びで整理します。</p>
      <span class="admin-card__status">続いて着手</span>
    </article>
    <article class="admin-card">
      <h2>特別会員申請</h2>
      <p>現在の申請承認画面はこの管理画面の中で引き続き利用できます。</p>
      <a href="/admin/special-members" class="v0-button v0-button--outline">申請一覧を見る</a>
    </article>
  </div>
</section>
