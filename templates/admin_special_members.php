<?php declare(strict_types=1); ?>
<section class="admin-page">
  <div class="admin-page__header">
    <p class="admin-page__eyebrow">Admin</p>
    <h1 class="admin-page__title"><?= e((string) ($pageTitle ?? '特別会員申請管理')) ?></h1>
    <p class="admin-page__lead"><?= e((string) ($pageLead ?? '')) ?></p>
  </div>

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
            <td><?= e((string) ($request['status'] ?? '')) ?></td>
            <td><?= e((string) ($request['requested_at'] ?? '')) ?></td>
            <td><?= e((string) count($request['files'] ?? [])) ?></td>
            <td>
              <div class="admin-table__actions">
                <form action="/admin/special-members/<?= e((string) ($request['id'] ?? 0)) ?>/approve" method="post">
                  <button type="submit" class="v0-button">承認</button>
                </form>
                <form action="/admin/special-members/<?= e((string) ($request['id'] ?? 0)) ?>/reject" method="post">
                  <button type="submit" class="v0-button v0-button--outline">却下</button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
