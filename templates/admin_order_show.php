<?php declare(strict_types=1); ?>
<section class="admin-page">
  <div class="admin-page__header">
    <p class="admin-page__eyebrow">Order Detail</p>
    <h1 class="admin-page__title">受注明細 #<?= e((string) ($order['s_id'] ?? '')) ?></h1>
    <p class="admin-page__lead">旧管理画面に近い情報量で、注文情報・配送先・商品明細をまとめて確認できます。</p>
  </div>

  <div class="admin-detail-grid">
    <article class="admin-card">
      <h2>注文情報</h2>
      <div class="admin-detail-list">
        <div><span>注文番号</span><strong><?= e((string) ($order['s_id'] ?? '')) ?></strong></div>
        <div><span>注文日時</span><strong><?= e((string) ($order['ordered_at_label'] ?? '')) ?></strong></div>
        <div><span>会員区分</span><strong><?= e((string) ($order['member_label'] ?? '-')) ?></strong></div>
        <div><span>支払方法</span><strong><?= e((string) ($order['payment_label'] ?? '-')) ?></strong></div>
        <div><span>配送希望</span><strong><?= e((string) ($order['delivery_time'] ?? '-')) ?></strong></div>
        <div><span>状態</span><strong><?= e((string) ($order['shipment_label'] ?? '-')) ?></strong></div>
      </div>
    </article>

    <article class="admin-card">
      <h2>注文者情報</h2>
      <div class="admin-detail-list">
        <div><span>会社名</span><strong><?= e((string) ($order['su_shop'] ?? '-')) ?></strong></div>
        <div><span>氏名</span><strong><?= e((string) ($order['su_name'] ?? '-')) ?></strong></div>
        <div><span>メール</span><strong><?= e((string) ($order['u_email'] ?? '-')) ?></strong></div>
        <div><span>郵便番号</span><strong><?= e((string) ($order['u_zip'] ?? '-')) ?></strong></div>
        <div><span>住所</span><strong><?= e((string) ($order['u_add'] ?? '-')) ?></strong></div>
        <div><span>電話番号</span><strong><?= e((string) ($order['u_tel'] ?? '-')) ?></strong></div>
      </div>
    </article>
  </div>

  <div class="admin-detail-grid">
    <article class="admin-card">
      <h2>配送先</h2>
      <?php if (is_array($delivery ?? null)): ?>
        <div class="admin-detail-list">
          <div><span>会社名</span><strong><?= e((string) ($delivery['deliv_shop'] ?? '-')) ?></strong></div>
          <div><span>氏名</span><strong><?= e((string) ($delivery['deliv_person'] ?? '-')) ?></strong></div>
          <div><span>郵便番号</span><strong><?= e((string) ($delivery['deliv_zip'] ?? '-')) ?></strong></div>
          <div><span>住所</span><strong><?= e((string) ($delivery['deliv_add'] ?? '-')) ?></strong></div>
          <div><span>電話番号</span><strong><?= e((string) ($delivery['deliv_tel'] ?? '-')) ?></strong></div>
        </div>
      <?php else: ?>
        <p class="admin-muted">配送先データは登録されていません。</p>
      <?php endif; ?>
    </article>

    <article class="admin-card">
      <h2>発送情報</h2>
      <div class="admin-detail-list">
        <div><span>配送会社</span><strong><?= e((string) ($order['transport_label'] ?? '-')) ?></strong></div>
        <div><span>追跡番号</span><strong><?= e((string) ($order['tracking_label'] ?? '-')) ?></strong></div>
        <div><span>配送メモ</span><strong><?= e((string) ($order['yamato_bikou'] ?? '-')) ?></strong></div>
        <div><span>備考</span><strong><?= e((string) ($order['bikou'] ?? '-')) ?></strong></div>
        <div><span>内部備考</span><strong><?= e((string) ($order['bikou2'] ?? '-')) ?></strong></div>
      </div>
    </article>
  </div>

  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>品番</th>
          <th>商品名</th>
          <th>単価</th>
          <th>数量</th>
          <th>小計</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach (($lines ?? []) as $line): ?>
          <tr>
            <td><?= e((string) ($line['parts_num'] ?? '-')) ?></td>
            <td><?= e((string) ($line['syamei2'] ?? '-')) ?></td>
            <td>¥<?= number_format((int) ($line['ss_price'] ?? 0)) ?></td>
            <td><?= number_format((int) ($line['qty'] ?? 0)) ?></td>
            <td>¥<?= number_format((int) ($line['ss_total'] ?? 0)) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="4">合計</td>
          <td>¥<?= number_format((int) ($order['total_amount'] ?? 0)) ?></td>
        </tr>
      </tfoot>
    </table>
  </div>

  <div class="admin-panel__footer">
    <a href="/admin/orders" class="v0-button v0-button--outline">受注一覧へ戻る</a>
  </div>
</section>
