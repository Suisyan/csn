<?php declare(strict_types=1); ?>
<?php
$returnTo = (string) ($modalReturnTo ?? 'orders');
$page = (int) ($modalPage ?? 1);
$orderId = (int) ($order['s_id'] ?? 0);
?>
<div class="admin-order-detail">
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

  <div class="admin-detail-grid">
    <article class="admin-card">
      <h2>入金確認</h2>
      <form action="/admin/orders/<?= e((string) $orderId) ?>/bank" method="post" class="admin-order-form">
        <input type="hidden" name="return_to" value="<?= e($returnTo) ?>">
        <input type="hidden" name="page" value="<?= e((string) $page) ?>">
        <label>
          銀行振込状態
          <select name="bank_dep">
            <option value="0"<?= ((string) ($order['bank_dep'] ?? '0')) === '0' ? ' selected' : '' ?>>未確認</option>
            <option value="2"<?= ((string) ($order['bank_dep'] ?? '')) === '2' ? ' selected' : '' ?>>要確認</option>
            <option value="1"<?= ((string) ($order['bank_dep'] ?? '')) === '1' ? ' selected' : '' ?>>入金済</option>
          </select>
        </label>
        <button type="submit" class="button button--primary">入金状態を更新</button>
      </form>
    </article>

    <article class="admin-card">
      <h2>出荷処理</h2>
      <form action="/admin/orders/<?= e((string) $orderId) ?>/shipping" method="post" class="admin-order-form">
        <input type="hidden" name="return_to" value="<?= e($returnTo) ?>">
        <input type="hidden" name="page" value="<?= e((string) $page) ?>">
        <div class="form-grid">
          <label>
            配送会社
            <input type="text" name="unsou" value="<?= e((string) ($order['unsou'] ?? '')) ?>">
          </label>
          <label>
            追跡番号
            <input type="text" name="yamato_num" value="<?= e((string) ($order['yamato_num'] ?? '')) ?>">
          </label>
        </div>
        <label>
          配送メモ
          <input type="text" name="yamato_bikou" value="<?= e((string) ($order['yamato_bikou'] ?? '')) ?>">
        </label>
        <label>
          出荷状態
          <select name="shipment_state">
            <option value="pending"<?= ((string) ($order['mail_flag'] ?? '0') === '0' || (string) ($order['mail_flag'] ?? '') === '') ? ' selected' : '' ?>>未完了</option>
            <option value="shipped"<?= ((string) ($order['mail_flag'] ?? '') !== '0' && (string) ($order['mail_flag'] ?? '') !== '' && (string) ($order['mail_flag'] ?? '') !== '9') ? ' selected' : '' ?>>発送済み</option>
          </select>
        </label>
        <button type="submit" class="button button--primary">出荷情報を更新</button>
      </form>
    </article>
  </div>

  <article class="admin-card">
    <h2>注文サマリー</h2>
    <div class="admin-order-summary-grid">
      <div><span>注文番号</span><strong><?= e((string) ($order['s_id'] ?? '')) ?></strong></div>
      <div><span>注文日時</span><strong><?= e((string) ($order['ordered_at_label'] ?? '')) ?></strong></div>
      <div><span>会員区分</span><strong><?= e((string) ($order['member_label'] ?? '-')) ?></strong></div>
      <div><span>支払方法</span><strong><?= e((string) ($order['payment_label'] ?? '-')) ?></strong></div>
      <div><span>配送希望</span><strong><?= e((string) ($order['delivery_time'] ?? '-')) ?></strong></div>
      <div><span>状態</span><strong><?= e((string) ($order['shipment_label'] ?? '-')) ?></strong></div>
      <div><span>配送会社</span><strong><?= e((string) ($order['transport_label'] ?? '-')) ?></strong></div>
      <div><span>合計</span><strong>¥<?= number_format((int) ($order['total_amount'] ?? 0)) ?></strong></div>
    </div>
  </article>

  <div class="admin-order-section">
    <details>
      <summary>注文者情報</summary>
      <div class="admin-order-section__body">
        <div class="admin-detail-list">
          <div><span>会社名</span><strong><?= e((string) ($order['su_shop'] ?? '-')) ?></strong></div>
          <div><span>氏名</span><strong><?= e((string) ($order['su_name'] ?? '-')) ?></strong></div>
          <div><span>メール</span><strong><?= e((string) ($order['u_email'] ?? '-')) ?></strong></div>
          <div><span>郵便番号</span><strong><?= e((string) ($order['u_zip'] ?? '-')) ?></strong></div>
          <div><span>住所</span><strong><?= e((string) ($order['u_add'] ?? '-')) ?></strong></div>
          <div><span>電話番号</span><strong><?= e((string) ($order['u_tel'] ?? '-')) ?></strong></div>
        </div>
      </div>
    </details>

    <details>
      <summary>配送先・発送情報</summary>
      <div class="admin-order-section__body">
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
      </div>
    </details>

    <details>
      <summary>商品明細（<?= number_format(count($lines ?? [])) ?>件）</summary>
      <div class="admin-order-section__body">
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
      </div>
    </details>
  </div>
</div>
