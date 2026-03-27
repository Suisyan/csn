<?php declare(strict_types=1); ?>
<section class="page-block">
  <div class="page-header">
    <p class="hero__kicker">Search</p>
    <h1 class="page-title">検索結果</h1>
    <p class="lead">ラジエーター＆コンデンサー検索結果を、旧画面の意味を保ったまま見やすく整理しています。</p>
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
        <p class="muted">型式が短すぎる場合や、条件に一致する商品がない場合があります。検索条件を変えて再度お試しください。</p>
        <p><a class="button button--primary" href="/inquiry">適合確認を問い合わせる</a></p>
      </div>
    <?php else: ?>
      <div class="notice">
        <h2>検索の見方</h2>
        <p class="muted">型式、ミッション、品番、カテゴリを確認のうえ、詳細ページからお問い合わせへ進めます。</p>
      </div>
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
