<?php declare(strict_types=1); ?>
<section class="page-block">
  <div class="page-header">
    <p class="hero__kicker">Search</p>
    <h1 class="page-title">検索結果</h1>
    <p class="lead">ラジエーター＆コンデンサー検索結果を、旧画面の意味を保ったまま見やすく整理しています。</p>
  </div>

  <form class="form-card search-refine" action="/search" method="get">
    <div class="form-grid">
      <label>
        メーカー
        <input type="text" name="make" value="<?= e($filters['make'] ?: '') ?>" placeholder="例: トヨタ">
      </label>
      <label>
        型式
        <input type="text" name="katasiki" value="<?= e($filters['katasiki'] ?: '') ?>" placeholder="例: E-RA1">
      </label>
      <label>
        ミッション
        <select name="toc">
          <option value="">選択してください</option>
          <option value="A/T" <?= ($filters['toc'] ?? '') === 'A/T' ? 'selected' : '' ?>>A/T</option>
          <option value="M/T" <?= ($filters['toc'] ?? '') === 'M/T' ? 'selected' : '' ?>>M/T</option>
          <option value="CVT" <?= ($filters['toc'] ?? '') === 'CVT' ? 'selected' : '' ?>>CVT</option>
        </select>
      </label>
      <label>
        検索区分
        <select name="mode">
          <option value="2" <?= ($filters['mode'] ?? '2') === '2' ? 'selected' : '' ?>>日本車</option>
          <option value="1" <?= ($filters['mode'] ?? '') === '1' ? 'selected' : '' ?>>輸入車</option>
        </select>
      </label>
    </div>
    <p><button type="submit">条件を変更して再検索</button></p>
  </form>

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
        <p class="muted">車検証記載の型式をご入力ください。（例 E-RA1）正しい型式を入力いただいても検索できない場合は、お探しいたします。</p>
        <p><a class="button button--primary" href="/inquiry">適合確認を問い合わせる</a></p>
      </div>
    <?php else: ?>
      <div class="notice">
        <h2>検索の見方</h2>
        <p class="muted">旧検索結果に近づけて、品番、車名・モデル、販売価格、在庫、備考、納期を一覧で確認できるようにしています。</p>
      </div>
      <div class="search-list">
        <?php foreach ($products as $product): ?>
          <?php
            $stock = (int) ($product['stock'] ?? 0);
            $stockLabel = $stock >= 1 ? '在庫あり' : ($stock === 0 ? '在庫なし' : '要問合せ');
            $stockClass = $stock >= 1 ? 'stock-badge stock-badge--ok' : ($stock === 0 ? 'stock-badge stock-badge--none' : 'stock-badge stock-badge--ask');
            $displayPrice = (int) ($product['discount_price'] ?? $product['price']);
          ?>
          <article class="search-result search-result--ops">
            <div class="search-result__top">
              <div>
                <div class="pill"><?= e($product['category']) ?></div>
                <h2><?= e($product['parts_num']) ?></h2>
                <div class="muted"><?= e($product['make']) ?> / <?= e($product['name']) ?></div>
              </div>
              <div class="stock-panel">
                <span class="<?= e($stockClass) ?>"><?= e($stockLabel) ?></span>
              </div>
            </div>
            <div class="result-grid">
              <div class="result-grid__item">
                <strong>車名・モデル</strong>
                <span><?= e($product['make']) ?><br><?= e($product['name']) ?></span>
              </div>
              <div class="result-grid__item">
                <strong>型式</strong>
                <span><?= e($product['katasiki']) ?></span>
              </div>
              <div class="result-grid__item">
                <strong>ミッション</strong>
                <span><?= e($product['toc']) ?></span>
              </div>
              <div class="result-grid__item">
                <strong>販売価格</strong>
                <span class="price">¥<?= number_format($displayPrice) ?></span>
                <span class="muted">税込・送料込</span>
              </div>
              <div class="result-grid__item result-grid__item--full">
                <strong>備考</strong>
                <span><?= e($product['note'] ?: '-') ?></span>
              </div>
              <div class="result-grid__item result-grid__item--full">
                <strong>納期</strong>
                <span><?= e($product['lead_time']) ?><br><span class="muted">※土曜、日曜、祝日は発送不可</span></span>
              </div>
            </div>
            <div class="action-row">
              <a class="button" href="/inquiry">お問合せ・適合確認</a>
              <a class="button button--primary" href="/product/<?= e((string) $product['id']) ?>">商品詳細を見る</a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
