<?php declare(strict_types=1); ?>
<section class="admin-page">
  <div class="admin-page__header">
    <p class="admin-page__eyebrow">Admin</p>
    <h1 class="admin-page__title"><?= e((string) ($pageTitle ?? '特別会員申請管理')) ?></h1>
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

  <article class="admin-card admin-search-card">
    <div class="admin-panel__header">
      <div>
        <h2 class="admin-panel__title">承認ポイント設定</h2>
        <p class="admin-muted-inline">初回承認のときだけ付与します。再承認では重複付与しません。</p>
      </div>
    </div>
    <form action="/admin/special-members/settings" method="post" class="admin-search-form">
      <label>
        承認ポイント
        <input type="number" min="0" name="special_member_approval_bonus" value="<?= e((string) ($approvalBonusPoints ?? 1000)) ?>">
      </label>
      <button type="submit" class="v0-button">保存</button>
    </form>
  </article>

  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>会社名 / 担当者</th>
          <th>Email</th>
          <th>状態</th>
          <th>申請日時</th>
          <th>ファイル数</th>
          <th>操作</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($requests as $request): ?>
          <tr>
            <td><?= e((string) ($request['id'] ?? '')) ?></td>
            <td>
              <strong><?= e((string) ($request['company_name'] ?? '')) ?></strong><br>
              <span><?= e((string) ($request['contact_name'] ?? '')) ?></span>
            </td>
            <td><?= e((string) ($request['email'] ?? '')) ?></td>
            <td><?= e(special_member_status_label((string) ($request['status'] ?? ''))) ?></td>
            <td><?= e((string) ($request['requested_at'] ?? '')) ?></td>
            <td><?= e((string) count($request['files'] ?? [])) ?></td>
            <td>
              <div class="admin-table__actions">
                <?php if ((string) ($request['status'] ?? '') !== 'approved'): ?>
                  <form action="/admin/special-members/<?= e((string) ($request['id'] ?? 0)) ?>/approve" method="post">
                    <button type="submit" class="v0-button">承認</button>
                  </form>
                <?php else: ?>
                  <span class="admin-muted-inline">承認済み</span>
                <?php endif; ?>

                <?php if ((string) ($request['status'] ?? '') !== 'rejected'): ?>
                  <form action="/admin/special-members/<?= e((string) ($request['id'] ?? 0)) ?>/reject" method="post">
                    <button type="submit" class="v0-button v0-button--outline">却下</button>
                  </form>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
