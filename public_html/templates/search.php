<?php declare(strict_types=1); ?>
<?php
  $selectedMode = ($filters['mode'] ?? '2') === '1' ? '輸入車' : '日本車';
  $selectedMake = ($filters['make'] ?? '') !== '' ? (string) $filters['make'] : '未指定';
  $selectedKatasiki = ($filters['katasiki'] ?? '') !== '' ? (string) $filters['katasiki'] : '未指定';
  $selectedToc = ($filters['toc'] ?? '') !== '' ? (string) $filters['toc'] : '未指定';
  $redirectTo = current_path() . (($_SERVER['QUERY_STRING'] ?? '') !== '' ? '?' . (string) $_SERVER['QUERY_STRING'] : '');
?>
<section class="border-b border-border bg-card py-12">
  <div class="mx-auto max-w-7xl px-4 lg:px-8">
    <p class="text-sm uppercase tracking-widest text-muted-foreground">Search</p>
    <h1 class="mt-2 text-3xl font-bold tracking-tight">商品検索</h1>
    <p class="mt-4 max-w-2xl text-muted-foreground">品番、型式、メーカー名から商品を検索できます。表示価格は現在の会員区分に合わせて自動表示されます。</p>
  </div>
</section>

<section class="py-8">
  <div class="mx-auto max-w-7xl px-4 lg:px-8">
    <form class="search-v0-toolbar" action="/search" method="get">
      <div class="search-v0-toolbar__query">
        <label>
          検索キーワード
          <input type="text" name="katasiki" value="<?= e((string) ($filters['katasiki'] ?? '')) ?>" placeholder="品番・型式・車種名で検索">
        </label>
      </div>
      <div class="search-v0-toolbar__filters">
        <label>
          メーカー
          <input type="text" name="make" value="<?= e((string) ($filters['make'] ?? '')) ?>" placeholder="例: トヨタ / BMW">
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
        <div class="search-v0-toolbar__action">
          <button type="submit" class="v0-button">検索する</button>
        </div>
      </div>
    </form>

    <div class="search-v0-summary">
      <div>
        <p class="text-sm text-muted-foreground">
          <?php if ($hasSearched ?? false): ?>
            <?= number_format(count($products)) ?> 件の商品が見つかりました
          <?php else: ?>
            検索条件を入力して商品を探してください
          <?php endif; ?>
        </p>
      </div>
      <div class="search-v0-summary__chips">
        <div class="summary-chip"><span>メーカー</span><strong><?= e($selectedMake) ?></strong></div>
        <div class="summary-chip"><span>型式</span><strong><?= e($selectedKatasiki) ?></strong></div>
        <div class="summary-chip"><span>ミッション</span><strong><?= e($selectedToc) ?></strong></div>
        <div class="summary-chip"><span>区分</span><strong><?= e($selectedMode) ?></strong></div>
      </div>
    </div>

    <?php if (!($hasSearched ?? false)): ?>
      <div class="search-v0-empty">
        <h2 class="text-lg font-semibold">検索を開始してください</h2>
        <p class="mt-2 max-w-md text-sm text-muted-foreground">型式がわかる場合は型式から、わからない場合はメーカー名だけでも候補が出ることがあります。</p>
      </div>
    <?php elseif ($products === []): ?>
      <div class="search-v0-empty">
        <h2 class="text-lg font-semibold">商品が見つかりませんでした</h2>
        <p class="mt-2 max-w-md text-sm text-muted-foreground">型式の表記ゆれ、ミッション条件、カテゴリの違いで見つからない場合があります。条件を変えるか、お問い合わせをご利用ください。</p>
        <p class="mt-6"><a class="v0-button v0-button--outline" href="/inquiry">適合確認を問い合わせる</a></p>
      </div>
    <?php else: ?>
      <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($products as $product): ?>
          <?php
            $stock = (int) ($product['stock'] ?? 0);
            $stockLabel = $stock > 0 ? '在庫あり' : ($stock === 0 ? 'お問い合わせ' : '要確認');
            $stockClass = $stock > 0 ? 'stock-badge stock-badge--ok' : ($stock === 0 ? 'stock-badge stock-badge--none' : 'stock-badge stock-badge--ask');
            $requiresInquiry = $stock <= 0;
            $displayPrice = (int) ($product['display_price'] ?? 0);
            $leadTime = (string) (($product['lead_time'] ?? '') !== '' ? $product['lead_time'] : 'お問い合わせください');
            $note = (string) (($product['note'] ?? '') !== '' ? $product['note'] : '-');
            $pictureNum = trim((string) ($product['picture_num'] ?? ''));
            $imagePath = $pictureNum !== '' ? '/images/prod_picture/' . rawurlencode($pictureNum) . '.jpg' : '';
            $inquiryParams = [
              'source' => 'fit',
              'category' => (string) ($product['category'] ?? ''),
              'parts_num' => (string) ($product['parts_num'] ?? ''),
              'katasiki' => (string) ($product['katasiki'] ?? ''),
              'syamei1' => (string) ($product['make'] ?? ''),
              'syamei2' => (string) ($product['name'] ?? ''),
              'toc' => (string) ($product['toc'] ?? ''),
            ];
            $inquiryParams = array_filter($inquiryParams, static fn (mixed $value): bool => $value !== null && $value !== '');
            $inquiryHref = '/inquiry' . ($inquiryParams !== [] ? '?' . http_build_query($inquiryParams) : '');
          ?>
          <article class="v0-card search-v0-card">
            <a href="/product/<?= e((string) ($product['id'] ?? 0)) ?>" class="search-v0-card__link">
              <div class="search-v0-card__media">
                <?php if ($imagePath !== ''): ?>
                  <img
                    src="<?= e($imagePath) ?>"
                    alt="<?= e((string) ($product['category'] ?? '')) ?> <?= e((string) ($product['parts_num'] ?? '')) ?>"
                    loading="lazy"
                    onload="if (this.nextElementSibling) { this.nextElementSibling.hidden = true; }"
                    onerror="this.style.display='none';if (this.nextElementSibling) { this.nextElementSibling.hidden = false; }"
                  >
                  <div class="search-v0-card__fallback" hidden>
                    <span>RAD</span>
                    <small>品番 <?= e((string) ($product['parts_num'] ?? '-')) ?></small>
                  </div>
                <?php else: ?>
                  <div class="search-v0-card__fallback">
                    <span>RAD</span>
                    <small>品番 <?= e((string) ($product['parts_num'] ?? '-')) ?></small>
                  </div>
                <?php endif; ?>
                <div class="search-v0-card__badge">
                  <span><?= e((string) ($product['make'] ?? '')) ?></span>
                </div>
              </div>
              <div class="v0-card__body search-v0-card__body">
                <p class="mb-1 text-xs text-muted-foreground"><?= e((string) ($product['parts_num'] ?? '-')) ?></p>
                <h2 class="font-semibold leading-tight text-foreground search-v0-card__title"><?= e((string) ($product['make'] ?? '')) ?> <?= e((string) ($product['name'] ?? '')) ?></h2>
                <div class="search-v0-card__meta">
                  <div><span class="font-medium">型式:</span> <?= e((string) ($product['katasiki'] ?? '-')) ?></div>
                  <div><span class="font-medium">ミッション:</span> <?= e((string) ($product['toc'] ?? '-')) ?></div>
                </div>
                <div class="search-v0-card__meta">
                  <div><span class="font-medium">エンジン:</span> <?= e((string) ($product['engine'] ?? '-')) ?></div>
                  <div><span class="font-medium">区分:</span> <?= e((string) ($product['category'] ?? '-')) ?></div>
                </div>
                <div class="search-v0-card__price">
                  <span class="text-xs text-muted-foreground"><?= e((string) ($product['display_price_label'] ?? '価格')) ?></span>
                  <strong>¥<?= number_format($displayPrice) ?></strong>
                </div>
                <p class="search-v0-card__leadtime text-sm text-muted-foreground">納期 <?= e($leadTime) ?></p>
                <div class="search-v0-card__stock">
                  <span class="<?= e($stockClass) ?>"><?= e($stockLabel) ?></span>
                </div>
                <p class="search-v0-card__note text-sm text-muted-foreground"><?= e($note) ?></p>
              </div>
            </a>
            <div class="search-v0-card__footer">
              <div class="search-v0-card__actions">
                <?php if ($requiresInquiry): ?>
                  <a class="v0-button" href="<?= e($inquiryHref) ?>">適合確認・問い合わせ</a>
                <?php else: ?>
                  <form action="/cart/add" method="post" class="search-v0-card__cart">
                    <input type="hidden" name="product_id" value="<?= e((string) ($product['id'] ?? 0)) ?>">
                    <input type="hidden" name="qty" value="1">
                    <input type="hidden" name="redirect_to" value="<?= e($redirectTo) ?>">
                    <button type="submit" class="v0-button">カートに追加</button>
                  </form>
                <?php endif; ?>
                <a class="v0-button v0-button--outline" href="/product/<?= e((string) ($product['id'] ?? 0)) ?>">商品詳細</a>
                <?php if (!$requiresInquiry): ?>
                  <a class="v0-button v0-button--outline" href="<?= e($inquiryHref) ?>">適合確認</a>
                <?php endif; ?>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
