<?php declare(strict_types=1); ?>
<?php
  $selectedMode = ($filters['mode'] ?? '2') === '1' ? '輸入車' : '日本車';
  $selectedMake = ($filters['make'] ?? '') !== '' ? (string) $filters['make'] : '未指定';
  $selectedKatasiki = ($filters['katasiki'] ?? '') !== '' ? (string) $filters['katasiki'] : '未指定';
  $selectedToc = ($filters['toc'] ?? '') !== '' ? (string) $filters['toc'] : '未指定';
  $redirectTo = current_path() . (($_SERVER['QUERY_STRING'] ?? '') !== '' ? '?' . (string) $_SERVER['QUERY_STRING'] : '');
?>
<section class="page-block">
  <div class="page-header">
    <p class="hero__kicker">検索</p>
    <h1 class="page-title">商品検索結果</h1>
    <p class="lead">非会員・会員・特別会員それぞれに、現在の区分に合った価格だけを表示します。</p>
  </div>

  <form class="form-card search-refine" action="/search" method="get">
    <div class="search-refine__head">
      <div>
        <p class="search-refine__eyebrow">再検索</p>
        <h2 class="search-refine__title">条件を変えて絞り込む</h2>
      </div>
      <p class="muted">日本車はミッション条件込みで、輸入車はカテゴリ中心で探せるようにしています。</p>
    </div>
    <div class="form-grid">
      <label>
        メーカー
        <input type="text" name="make" value="<?= e((string) ($filters['make'] ?? '')) ?>" placeholder="例: トヨタ / BMW">
      </label>
      <label>
        型式
        <input type="text" name="katasiki" value="<?= e((string) ($filters['katasiki'] ?? '')) ?>" placeholder="例: 6AA-MXPH15 / ABA-VR20">
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
    <div class="search-refine__actions">
      <button type="submit">条件を変更して検索</button>
    </div>
  </form>

  <section class="result-summary">
    <div class="result-summary__main">
      <span class="pill pill--mode"><?= e($selectedMode) ?>検索</span>
      <h2 class="result-summary__title">
        <?php if ($hasSearched ?? false): ?>
          検索結果 <?= number_format(count($products)) ?> 件
        <?php else: ?>
          型式またはメーカーから検索してください
        <?php endif; ?>
      </h2>
      <p class="muted">
        <?php if ($hasSearched ?? false): ?>
          一覧では現在の会員区分に合った価格のみを表示しています。適合確認が必要な場合はお問い合わせへ進めます。
        <?php else: ?>
          まずは型式またはメーカーを入力してください。該当候補があれば一覧に表示されます。
        <?php endif; ?>
      </p>
    </div>
    <div class="result-summary__filters">
      <div class="summary-chip"><span>メーカー</span><strong><?= e($selectedMake) ?></strong></div>
      <div class="summary-chip"><span>型式</span><strong><?= e($selectedKatasiki) ?></strong></div>
      <div class="summary-chip"><span>ミッション</span><strong><?= e($selectedToc) ?></strong></div>
      <div class="summary-chip"><span>区分</span><strong><?= e($selectedMode) ?></strong></div>
    </div>
  </section>

  <div class="section-stack">
    <?php if (!($hasSearched ?? false)): ?>
      <div class="notice">
        <h2>検索を開始してください</h2>
        <p class="muted">型式がわかる場合は型式から、わからない場合はメーカー名だけでも候補が出ることがあります。</p>
      </div>
    <?php elseif ($products === []): ?>
      <div class="notice notice--empty">
        <h2>該当データがありません</h2>
        <p class="muted">型式の表記ゆれ、ミッション条件、カテゴリの違いで見つからない場合があります。条件を変えるか、お問い合わせをご利用ください。</p>
        <p><a class="button button--primary" href="/inquiry">適合確認を問い合わせる</a></p>
      </div>
    <?php else: ?>
      <div class="search-list search-list--enhanced">
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
          <article class="search-result search-result--catalog">
            <div class="search-result__top">
              <div class="search-result__media">
                <?php if ($imagePath !== ''): ?>
                  <img
                    src="<?= e($imagePath) ?>"
                    alt="<?= e((string) ($product['category'] ?? '')) ?> <?= e((string) ($product['parts_num'] ?? '')) ?>"
                    loading="lazy"
                    onload="this.parentElement.classList.add('search-result__media--loaded');if (this.nextElementSibling) { this.nextElementSibling.hidden = true; }"
                    onerror="this.parentElement.classList.add('search-result__media--error');this.style.display='none';if (this.nextElementSibling) { this.nextElementSibling.hidden = false; }"
                  >
                  <div class="search-result__media-fallback" hidden>
                    <span>画像確認中</span>
                    <small>品番 <?= e((string) ($product['parts_num'] ?? '-')) ?></small>
                  </div>
                <?php else: ?>
                  <div class="search-result__media-fallback">
                    <span>画像準備中</span>
                    <small>品番 <?= e((string) ($product['parts_num'] ?? '-')) ?></small>
                  </div>
                <?php endif; ?>
              </div>
              <div class="search-result__identity">
                <div class="search-result__badges">
                  <span class="pill"><?= e((string) ($product['category'] ?? '')) ?></span>
                  <span class="catalog-code"><?= e((string) ($product['parts_num'] ?? '')) ?></span>
                </div>
                <div class="search-result__partline">
                  <span class="part-label">品番</span>
                  <h2 class="search-result__parts-num"><?= e((string) ($product['parts_num'] ?? '')) ?></h2>
                </div>
                <h3 class="search-result__title"><?= e((string) ($product['make'] ?? '')) ?> <?= e((string) ($product['name'] ?? '')) ?></h3>
                <p class="search-result__subtitle">型式 <?= e((string) ($product['katasiki'] ?? '-')) ?> / ミッション <?= e((string) ($product['toc'] ?? '-')) ?></p>
              </div>
            </div>

            <div class="catalog-board">
              <div class="catalog-board__section">
                <h3>適合情報</h3>
                <div class="catalog-specs">
                  <div class="catalog-spec"><span>車名・モデル</span><strong><?= e((string) ($product['make'] ?? '')) ?> / <?= e((string) ($product['name'] ?? '')) ?></strong></div>
                  <div class="catalog-spec"><span>型式</span><strong><?= e((string) ($product['katasiki'] ?? '-')) ?></strong></div>
                  <div class="catalog-spec"><span>エンジン</span><strong><?= e((string) ($product['engine'] ?? '-')) ?></strong></div>
                  <div class="catalog-spec"><span>ミッション</span><strong><?= e((string) ($product['toc'] ?? '-')) ?></strong></div>
                </div>
              </div>

              <div class="catalog-board__section catalog-board__section--price">
                <h3>販売情報</h3>
                <div class="catalog-price">
                  <div>
                    <span class="catalog-price__label"><?= e((string) ($product['display_price_label'] ?? '価格')) ?></span>
                    <strong class="price">¥<?= number_format($displayPrice) ?></strong>
                  </div>
                  <div class="catalog-price__meta">
                    <span><?= e((string) ($product['price_note'] ?? '')) ?></span>
                    <span>納期 <?= e($leadTime) ?></span>
                  </div>
                </div>
                <div class="stock-panel">
                  <div class="stock-card">
                    <span class="stock-card__label">在庫</span>
                    <span class="<?= e($stockClass) ?>"><?= e($stockLabel) ?></span>
                  </div>
                </div>
              </div>
            </div>

            <div class="search-result__foot">
              <div class="result-note">
                <strong>備考</strong>
                <span><?= e($note) ?></span>
              </div>
              <div class="action-row">
                <?php if ($requiresInquiry): ?>
                  <a class="button button--primary action-row__priority" href="<?= e($inquiryHref) ?>">適合確認・問い合わせ</a>
                <?php else: ?>
                  <form action="/cart/add" method="post">
                    <input type="hidden" name="product_id" value="<?= e((string) ($product['id'] ?? 0)) ?>">
                    <input type="hidden" name="qty" value="1">
                    <input type="hidden" name="redirect_to" value="<?= e($redirectTo) ?>">
                    <button type="submit" class="button button--primary">カートに入れる</button>
                  </form>
                <?php endif; ?>
                <a class="button" href="/product/<?= e((string) ($product['id'] ?? 0)) ?>">商品詳細</a>
                <?php if (!$requiresInquiry): ?>
                  <a class="button" href="<?= e($inquiryHref) ?>">適合確認・問い合わせ</a>
                <?php endif; ?>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
