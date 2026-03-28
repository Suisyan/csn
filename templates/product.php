<?php declare(strict_types=1); ?>
<?php
  $displayPrice = (int) ($product['display_price'] ?? 0);
  $stock = (int) ($product['stock'] ?? 0);
  $stockLabel = $stock > 0 ? '在庫あり' : ($stock === 0 ? '欠品・要確認' : '在庫確認');
  $leadTime = (string) ($product['lead_time'] ?? '');
  $note = (string) ($product['note'] ?? '');
?>
<section class="page-block page-block--product">
  <div class="page-header page-header--product">
    <p class="hero__kicker">Product Detail</p>
    <h1 class="page-title"><?= e((string) ($product['make'] ?? '')) ?> <?= e((string) ($product['name'] ?? '')) ?></h1>
    <p class="lead">商品詳細ページです。価格、在庫、納期、備考を確認したうえで、必要に応じてカートへ追加してください。</p>
    <div class="page-context-chip">このページは商品詳細です</div>
  </div>

  <div class="content-grid">
    <article class="detail-panel">
      <h2>商品情報</h2>
      <div class="detail-meta">
        <div class="detail-meta__item"><strong>カテゴリ</strong><span><?= e((string) ($product['category'] ?? '-')) ?></span></div>
        <div class="detail-meta__item"><strong>品番</strong><span><?= e((string) ($product['parts_num'] ?? '-')) ?></span></div>
        <div class="detail-meta__item"><strong>型式</strong><span><?= e((string) ($product['katasiki'] ?? '-')) ?></span></div>
        <div class="detail-meta__item"><strong>エンジン</strong><span><?= e((string) ($product['engine'] ?? '-')) ?></span></div>
        <div class="detail-meta__item"><strong>ミッション</strong><span><?= e((string) ($product['toc'] ?? '-')) ?></span></div>
        <div class="detail-meta__item"><strong>納期</strong><span><?= e($leadTime !== '' ? $leadTime : '要確認') ?></span></div>
        <div class="detail-meta__item"><strong>在庫</strong><span><?= e($stockLabel) ?></span></div>
      </div>
    </article>

    <article class="detail-panel">
      <h2>価格と手配</h2>
      <div class="price">¥<?= number_format($displayPrice) ?></div>
      <p class="muted"><?= e((string) ($product['display_price_label'] ?? '価格')) ?></p>
      <p class="muted"><?= e((string) ($product['price_note'] ?? '')) ?></p>
      <p class="muted"><?= e($note !== '' ? $note : '適合確認が必要な場合は、お問い合わせください。') ?></p>
      <div class="action-row">
        <form action="/cart/add" method="post">
          <input type="hidden" name="product_id" value="<?= e((string) ($product['id'] ?? 0)) ?>">
          <input type="hidden" name="qty" value="1">
          <input type="hidden" name="redirect_to" value="/cart">
          <button type="submit" class="button button--primary">カートに入れる</button>
        </form>
        <a class="button button--ghost" href="/cart">カートを見る</a>
        <a class="button" href="/inquiry">この商品について問い合わせる</a>
      </div>
    </article>
  </div>
</section>
