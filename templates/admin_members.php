<?php declare(strict_types=1); ?>
<section class="admin-page">
  <div class="admin-page__header">
    <p class="admin-page__eyebrow">Members</p>
    <h1 class="admin-page__title"><?= e((string) ($pageTitle ?? '会員管理')) ?></h1>
    <p class="admin-page__lead"><?= e((string) ($pageLead ?? '')) ?></p>
  </div>

  <article class="admin-card admin-search-card">
    <div class="admin-panel__header">
      <div>
        <p class="admin-page__eyebrow">Member Search</p>
        <h2 class="admin-panel__title">会員検索</h2>
      </div>
    </div>

    <form action="/admin/members" method="get" class="admin-search-form">
      <label class="field">
        <span>検索キーワード</span>
        <input type="text" name="key" value="<?= e((string) ($keyword ?? '')) ?>" placeholder="名前 / 会社名 / TEL / E-mail / 会員ID">
      </label>
      <button type="submit" class="v0-button">検索</button>
    </form>

    <?php if (($hasSearched ?? false) === false): ?>
      <p class="admin-muted">会員名、会社名、電話番号、メールアドレス、会員 ID で検索できます。</p>
    <?php else: ?>
      <p class="admin-muted"><?= number_format(count($members ?? [])) ?> 件を表示しています。最大 100 件まで表示します。</p>
    <?php endif; ?>
  </article>

  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>会員名 / 会社名</th>
          <th>住所</th>
          <th>TEL</th>
          <th>E-mail</th>
          <th>ポイント</th>
          <th>会員区分</th>
        </tr>
      </thead>
      <tbody>
        <?php if (($members ?? []) === []): ?>
          <tr>
            <td colspan="7"><?= ($hasSearched ?? false) ? '該当する会員はありませんでした。' : '検索キーワードを入力すると会員一覧を表示します。' ?></td>
          </tr>
        <?php else: ?>
          <?php foreach (($members ?? []) as $member): ?>
            <tr>
              <td><?= e((string) ($member['acc_id'] ?? '-')) ?></td>
              <td>
                <strong><?= e((string) ($member['u_name'] ?? '-')) ?></strong><br>
                <span class="admin-muted-inline"><?= e((string) ($member['u_shop'] ?? $member['b_name'] ?? '-')) ?></span>
              </td>
              <td>
                <?= e((string) ($member['zip'] ?? '')) ?><br>
                <span class="admin-muted-inline"><?= e((string) ($member['address'] ?? '-')) ?></span>
              </td>
              <td><?= e((string) ($member['tel'] ?? '-')) ?></td>
              <td><?= e((string) ($member['e_mail'] ?? '-')) ?></td>
              <td><?= number_format((int) ($member['coolpoint_total'] ?? 0)) ?></td>
              <td>
                <span class="admin-stock <?= ((string) ($member['state'] ?? '') === '2') ? 'admin-stock--ok' : 'admin-stock--none' ?>">
                  <?= e((string) ($member['member_label'] ?? '-')) ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
