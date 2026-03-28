<?php declare(strict_types=1); ?>
<?php
$requestStatus = is_array($request) ? (string) ($request['status'] ?? 'pending') : null;
$bizStatus = (string) ($user['biz_status'] ?? 'none');
?>
<section class="page-block">
  <div class="page-header page-header--product">
    <p class="hero__kicker">Account</p>
    <h1 class="page-title">会員情報</h1>
    <p class="lead">会員区分と特別会員申請状態を、このページで確認します。</p>
  </div>

  <div class="content-grid">
    <article class="detail-panel">
      <h2>アカウント状態</h2>
      <div class="detail-meta">
        <div class="detail-meta__item"><strong>お名前</strong><span><?= e((string) ($profile['name'] ?? '')) ?></span></div>
        <div class="detail-meta__item"><strong>Email</strong><span><?= e((string) ($profile['email'] ?? '')) ?></span></div>
        <div class="detail-meta__item"><strong>会員区分</strong><span><?= e((string) account_label($user)) ?></span></div>
        <div class="detail-meta__item"><strong>特別会員状態</strong><span><?= e($bizStatus) ?></span></div>
      </div>
    </article>

    <article class="detail-panel">
      <h2>特別会員</h2>
      <?php if (($user['member_type'] ?? '') === 'biz' && $bizStatus === 'approved'): ?>
        <p class="muted">特別会員価格が有効です。</p>
      <?php elseif ($bizStatus === 'docs_pending'): ?>
        <p class="muted">申請受付済みです。ログイン後に名刺画像アップロードを完了してください。</p>
        <p><a class="button button--primary" href="/special-member/upload">名刺画像アップロードへ進む</a></p>
      <?php elseif ($bizStatus === 'pending'): ?>
        <p class="muted">特別会員申請を受付中です。管理承認待ちです。</p>
        <?php if (is_array($request)): ?>
          <p class="muted">最新申請: <?= e((string) ($request['requested_at'] ?? '')) ?> / status <?= e($requestStatus) ?></p>
        <?php endif; ?>
      <?php elseif ($bizStatus === 'rejected'): ?>
        <p class="muted">直近の申請は却下されました。必要なら再申請してください。</p>
        <p><a class="button button--primary" href="/special-member/register">特別会員申請へ進む</a></p>
      <?php else: ?>
        <p class="muted">旧 .biz ではなく、この共通アプリ内から特別会員申請を受け付けます。</p>
        <p><a class="button button--primary" href="/special-member/register">特別会員申請へ進む</a></p>
      <?php endif; ?>
    </article>
  </div>
</section>
