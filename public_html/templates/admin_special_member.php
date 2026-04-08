<?php declare(strict_types=1); ?>
<section class="page-block">
  <div class="page-header page-header--product">
    <p class="hero__kicker">Admin</p>
    <h1 class="page-title">特別会員管理</h1>
    <p class="lead">申請、名刺画像アップロード、承認状態をここで管理します。</p>
  </div>

  <div class="section-stack">
    <?php foreach ($requests as $request): ?>
      <article class="detail-panel">
        <div class="detail-meta">
          <div class="detail-meta__item"><strong>ID</strong><span><?= e((string) ($request['id'] ?? '')) ?></span></div>
          <div class="detail-meta__item"><strong>Account</strong><span><?= e((string) ($request['acc_id'] ?? '')) ?></span></div>
          <div class="detail-meta__item"><strong>Company</strong><span><?= e((string) ($request['company_name'] ?? '')) ?></span></div>
          <div class="detail-meta__item"><strong>Email</strong><span><?= e((string) ($request['email'] ?? '')) ?></span></div>
          <div class="detail-meta__item"><strong>Status</strong><span><?= e((string) ($request['status'] ?? '')) ?></span></div>
          <div class="detail-meta__item"><strong>Files</strong><span><?= e((string) ($request['file_count'] ?? '0')) ?></span></div>
        </div>
        <?php if (!empty($request['files'])): ?>
          <div class="stack muted">
            <?php foreach ($request['files'] as $file): ?>
              <div><?= e((string) ($file['original_name'] ?? '')) ?> / <?= e((string) ($file['mime_type'] ?? '')) ?></div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
        <div class="action-row">
          <form action="/admin/special-member/<?= e((string) ($request['id'] ?? 0)) ?>/approve" method="post">
            <button type="submit" class="button button--primary">承認</button>
          </form>
          <form action="/admin/special-member/<?= e((string) ($request['id'] ?? 0)) ?>/reject" method="post">
            <button type="submit" class="button button--danger">却下</button>
          </form>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>
