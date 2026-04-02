<?php declare(strict_types=1); ?>
<?php
$memberType = (string) ($user['member_type'] ?? '');
$deliveryList = is_array($deliveries ?? null) ? $deliveries : [];
$pointHistory = is_array($coolpointHistory ?? null) ? $coolpointHistory : [];
$profileRows = [
    'お名前' => (string) ($profile['u_name'] ?? $profile['name'] ?? ''),
    '会社名・屋号' => (string) ($profile['u_shop'] ?? $profile['b_name'] ?? ''),
    'Email' => (string) ($profile['email'] ?? ''),
    '郵便番号' => (string) ($profile['zip'] ?? ''),
    '住所1' => (string) ($profile['add1'] ?? ''),
    '住所2' => (string) ($profile['add2'] ?? ''),
    '住所3' => (string) ($profile['add3'] ?? ''),
    'TEL' => (string) ($profile['tel'] ?? ''),
];
?>
<section class="page-block">
  <div class="page-header page-header--account">
    <p class="hero__kicker">My Page</p>
    <h1 class="page-title">会員情報・購入履歴</h1>
    <p class="lead">旧 mypage の役割をこの画面に集約し、会員状態と登録情報、直近の注文状況をまとめて確認できるようにしました。</p>
    <div class="hero__signals">
      <span class="signal-pill"><?= e((string) account_label($user)) ?></span>
      <span class="signal-pill">直近注文: <?= count($purchases ?? []) ?>件</span>
    </div>
  </div>

  <?php if ($notice !== null): ?>
    <div class="notice notice--success">
      <strong><?= e($notice) ?></strong>
    </div>
  <?php endif; ?>

  <?php if ($deliveryError !== null): ?>
    <div class="notice notice--error">
      <strong><?= e($deliveryError) ?></strong>
    </div>
  <?php endif; ?>

  <div class="account-grid">
    <article class="detail-panel account-summary-card">
      <h2>マイページ概要</h2>
      <div class="summary-chip-grid">
        <div class="summary-chip">
          <span>会員区分</span>
          <strong><?= e((string) account_label($user)) ?></strong>
        </div>
        <div class="summary-chip">
          <span>価格状態</span>
          <strong><?= e((string) account_summary($user)) ?></strong>
        </div>
        <div class="summary-chip">
          <span>登録メール</span>
          <strong><?= e((string) ($profile['email'] ?? '')) ?></strong>
        </div>
      </div>
      <div class="account-action-row">
        <a class="button button--ghost" href="/search">商品検索へ</a>
        <a class="button button--ghost" href="/cart">カートを見る</a>
        <?php if ($memberType === 'biz'): ?>
          <a class="button button--primary" href="/search">特別会員価格で探す</a>
        <?php else: ?>
          <a class="button button--primary" href="/special-member/register">特別会員申請へ</a>
        <?php endif; ?>
      </div>
    </article>

    <article class="detail-panel">
      <h2>登録情報</h2>
      <div class="detail-meta">
        <?php foreach ($profileRows as $label => $value): ?>
          <div class="detail-meta__item">
            <strong><?= e($label) ?></strong>
            <span><?= e($value !== '' ? $value : '未登録') ?></span>
          </div>
        <?php endforeach; ?>
      </div>
      <p class="muted">旧 mypage の「お客様情報変更」で扱っていた主要項目を、まずは安全に確認できる状態に統合しています。</p>
    </article>
  </div>

  <div class="content-grid account-lower-grid">
    <article class="detail-panel">
      <h2>直近の購入履歴</h2>
      <p class="muted">旧 mypage の購入履歴を引き継ぐ形で、最新の注文を最大10件まで表示します。</p>
      <?php if (!empty($purchases)): ?>
        <div class="account-list">
          <?php foreach ($purchases as $purchase): ?>
            <div class="account-list__row">
              <div class="account-list__main">
                <strong>注文番号 <?= e((string) ($purchase['order_id'] ?? '')) ?></strong>
                <span class="account-list__meta"><?= e((string) ($purchase['ordered_at_label'] ?? '')) ?></span>
              </div>
              <div class="account-list__sub">
                <span>¥<?= number_format((int) ($purchase['total_amount'] ?? 0)) ?></span>
                <span><?= e((string) ($purchase['payment_label'] ?? '')) ?></span>
                <span class="pill"><?= e((string) ($purchase['shipment_label'] ?? '')) ?></span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="account-empty-state">
          <strong>購入履歴はまだありません。</strong>
          <p class="muted">注文データが未登録、または旧DBの注文テーブルがまだ接続されていない可能性があります。</p>
        </div>
      <?php endif; ?>
    </article>
  </div>

  <div class="account-tools-grid">
    <article class="detail-panel">
      <h2>発送先登録・一覧</h2>
      <p class="muted">旧 .biz mypage の発送先管理を、この共通マイページ内へ統合しています。</p>
      <?php if (($deliveryEnabled ?? false) === true): ?>
        <form class="account-delivery-form" action="/account/delivery/save" method="post">
          <div class="form-grid">
            <label>
              発送先名
              <input type="text" name="deliv_shop" value="">
            </label>
            <label>
              担当者名
              <input type="text" name="deliv_person" value="">
            </label>
            <label>
              郵便番号
              <input type="text" name="deliv_zip" value="">
            </label>
            <label>
              電話番号
              <input type="text" name="deliv_tel" value="">
            </label>
          </div>
          <label>
            住所
            <input type="text" name="deliv_add" value="">
          </label>
          <div class="account-action-row">
            <button type="submit">発送先を追加</button>
          </div>
        </form>

        <?php if (!empty($deliveryList)): ?>
          <div class="account-delivery-list">
            <?php foreach ($deliveryList as $delivery): ?>
              <article class="account-delivery-card">
                <form class="account-delivery-form" action="/account/delivery/save" method="post">
                  <input type="hidden" name="deliv_id" value="<?= e((string) ($delivery['deliv_id'] ?? '')) ?>">
                  <div class="form-grid">
                    <label>
                      発送先名
                      <input type="text" name="deliv_shop" value="<?= e((string) ($delivery['deliv_shop'] ?? '')) ?>">
                    </label>
                    <label>
                      担当者名
                      <input type="text" name="deliv_person" value="<?= e((string) ($delivery['deliv_person'] ?? '')) ?>">
                    </label>
                    <label>
                      郵便番号
                      <input type="text" name="deliv_zip" value="<?= e((string) ($delivery['deliv_zip'] ?? '')) ?>">
                    </label>
                    <label>
                      電話番号
                      <input type="text" name="deliv_tel" value="<?= e((string) ($delivery['deliv_tel'] ?? '')) ?>">
                    </label>
                  </div>
                  <label>
                    住所
                    <input type="text" name="deliv_add" value="<?= e((string) ($delivery['deliv_add'] ?? '')) ?>">
                  </label>
                  <div class="account-delivery-actions">
                    <button type="submit">更新する</button>
                  </div>
                </form>
                <form action="/account/delivery/<?= e((string) ($delivery['deliv_id'] ?? '0')) ?>/delete" method="post">
                  <button class="button button--danger" type="submit">削除</button>
                </form>
              </article>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="account-empty-state">
            <strong>発送先はまだ登録されていません。</strong>
            <p class="muted">業者会員向けに使われていた発送先管理を、この画面から追加できます。</p>
          </div>
        <?php endif; ?>
      <?php else: ?>
        <div class="account-empty-state">
          <strong>発送先機能はまだ未接続です。</strong>
          <p class="muted">`delivery` テーブルが使える環境では、この画面から登録・更新・削除ができます。</p>
        </div>
      <?php endif; ?>
    </article>

    <article class="detail-panel">
      <h2>クールポイント</h2>
      <p class="muted">旧 mypage / biz mypage のクールポイント残高と履歴を、この画面内で確認できます。</p>
      <?php if (($coolpointEnabled ?? false) === true): ?>
        <div class="summary-chip-grid">
          <div class="summary-chip">
            <span>現在ポイント</span>
            <strong><?= number_format((int) ($coolpointBalance ?? 0)) ?> pt</strong>
          </div>
          <div class="summary-chip">
            <span>表示履歴件数</span>
            <strong><?= count($pointHistory) ?>件</strong>
          </div>
        </div>

        <?php if (!empty($pointHistory)): ?>
          <div class="account-list">
            <?php foreach ($pointHistory as $point): ?>
              <div class="account-list__row">
                <div class="account-list__main">
                  <strong><?= e((string) ($point['cp_date'] ?? '')) ?></strong>
                  <span class="account-list__meta">注文番号 <?= e((string) (($point['cp_s_id'] ?? 0) !== 0 ? $point['cp_s_id'] : 'なし')) ?></span>
                </div>
                <div class="account-list__sub">
                  <span><?= number_format((int) ($point['cp_point'] ?? 0)) ?> pt</span>
                  <span class="pill"><?= e((string) ($point['state_label'] ?? '')) ?></span>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="account-empty-state">
            <strong>ポイント履歴はまだありません。</strong>
            <p class="muted">利用や加算が発生すると、ここに履歴が表示されます。</p>
          </div>
        <?php endif; ?>
      <?php else: ?>
        <div class="account-empty-state">
          <strong>ポイント機能はまだ未接続です。</strong>
          <p class="muted">`coolpoint` テーブルが使える環境では、現在残高と履歴が表示されます。</p>
        </div>
      <?php endif; ?>
    </article>
  </div>
</section>
