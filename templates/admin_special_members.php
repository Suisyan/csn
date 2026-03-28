<?php declare(strict_types=1); ?>
<section class="page-block">
  <div class="page-header page-header--product">
    <p class="hero__kicker">Admin</p>
    <h1 class="page-title">特別会員申請管理</h1>
    <p class="lead">特別会員申請、名刺画像の有無、承認状態を確認します。</p>
  </div>

  <div class="section-stack">
    <?php foreach ($requests as $request): ?>
      <article class="detail-panel">
        <h2><?= e((string) ($request['company_name'] ?? '')) ?> / <?= e((string) ($request['contact_name'] ?? '')) ?></h2>
        <div class="detail-meta">
          <div class="detail-meta__item"><strong>ID</strong><span><?= e((string) ($request['id'] ?? '')) ?></span></div>
          <div class="detail-meta__item"><strong>Email</strong><span><?= e((string) ($request['email'] ?? '')) ?></span></div>
          <div class="detail-meta__item"><strong>Status</strong><span><?= e((string) ($request['status'] ?? '')) ?></span></div>
          <div class="detail-meta__item"><strong>Requested</strong><span><?= e((string) ($request['requested_at'] ?? '')) ?></span></div>
          <div class="detail-meta__item"><strong>Files</strong><span><?= e((string) count($request['files'] ?? [])) ?></span></div>
        </div>
        <div class="action-row">
          <form action="/admin/special-members/review" method="post">
            <input type="hidden" name="request_id" value="<?= e((string) ($request['id'] ?? 0)) ?>">
            <input type="hidden" name="action" value="approve">
            <button type="submit" class="button button--primary">承認</button>
          </form>
          <form action="/admin/special-members/review" method="post">
            <input type="hidden" name="request_id" value="<?= e((string) ($request['id'] ?? 0)) ?>">
            <input type="hidden" name="action" value="reject">
            <button type="submit" class="button button--danger">却下</button>
          </form>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>
