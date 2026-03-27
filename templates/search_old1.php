<?php declare(strict_types=1); ?>
<section class="page-block">
  <div class="page-header">
    <p class="hero__kicker">Search</p>
    <h1 class="page-title">検索結果</h1>
    <p class="lead">旧 `result.php` の役割を、UTF-8 と安全なSQLで置き換えるための新画面です。</p>
  </div>

  <div class="notice">
    <div class="detail-meta">
      <div class="detail-meta__item"><strong>メーカー</strong><span><?= e($filters['make'] ?: '指定なし') ?></span></div>
      <div class="detail-meta__item"><strong>型式</strong><span><?= e($filters['katasiki'] ?: '指定なし') ?></span></div>
      <div class="detail-meta__item"><strong>ミッション</strong><span><?= e($filters['toc'] ?: '指定なし') ?></span></div>
    </div>
  </div>

  <div class="section-stack">
    <?php if ($products === []): ?>
      <div class="notice">
        <h2>該当データがありません</h2>
        <p class="muted">検索条件を変えて再度お試しください。問い合わせ導線へつなぐこともできます。</p>
      </div>
    <?php else: ?>
      <div class="search-list">
        <?php foreach ($products as $product): ?>
          <a class="search-result" href="/product/<?= e((string) $product['id']) ?>">
            <div class="search-result__top">
              <div>
                <div class="pill"><?= e($product['category']) ?></div>
                <h2><?= e($product['make']) ?> <?= e($product['name']) ?></h2>
                <div class="muted">型式: <?= e($product['katasiki']) ?> / 品番: <?= e($product['parts_num']) ?></div>
              </div>
              <div class="price">¥<?= number_format((int) $product['price']) ?></div>
            </div>
            <div class="muted">ミッション: <?= e($product['toc']) ?> / 納期: <?= e($product['lead_time']) ?></div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
