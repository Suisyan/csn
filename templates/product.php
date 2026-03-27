<?php declare(strict_types=1); ?>
<section class="page-block">
  <div class="page-header">
    <p class="hero__kicker">Product Detail</p>
    <h1 class="page-title"><?= e($product['make']) ?> <?= e($product['name']) ?></h1>
    <p class="lead">旧 `detail.php` の情報密度を保ちながら、見やすい構成に組み替えています。</p>
  </div>

  <div class="content-grid">
    <article class="detail-panel">
      <h2>製品情報</h2>
      <div class="detail-meta">
        <div class="detail-meta__item"><strong>カテゴリ</strong><span><?= e($product['category']) ?></span></div>
        <div class="detail-meta__item"><strong>品番</strong><span><?= e($product['parts_num']) ?></span></div>
        <div class="detail-meta__item"><strong>型式</strong><span><?= e($product['katasiki']) ?></span></div>
        <div class="detail-meta__item"><strong>エンジン</strong><span><?= e($product['engine'] ?? '-') ?></span></div>
        <div class="detail-meta__item"><strong>ミッション</strong><span><?= e($product['toc']) ?></span></div>
        <div class="detail-meta__item"><strong>納期</strong><span><?= e($product['lead_time']) ?></span></div>
      </div>
    </article>

    <article class="detail-panel">
      <h2>価格とご案内</h2>
      <div class="price">¥<?= number_format((int) $product['price']) ?></div>
      <p class="muted"><?= e($product['note'] ?? '') ?></p>
      <p><a class="button button--primary" href="/inquiry">この商品について問い合わせる</a></p>
    </article>
  </div>
</section>
