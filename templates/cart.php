<?php declare(strict_types=1); ?>
<?php $itemCount = count($lines); ?>
<section class="page-block page-block--cart">
  <div class="page-header page-header--cart">
    <p class="hero__kicker">Cart</p>
    <h1 class="page-title">ショッピングカート</h1>
    <p class="lead">カートに追加した商品を確認し、数量の変更や削除を行えます。内容を確定する前に、品番と数量をもう一度ご確認ください。</p>
  </div>

  <div class="cart-overview">
    <div class="cart-overview__card">
      <span class="cart-overview__label">現在のカート</span>
      <strong><?= number_format($itemCount) ?> 商品</strong>
      <p class="muted">このページはカート画面です。商品詳細は「商品詳細へ」から戻れます。</p>
    </div>
    <div class="cart-overview__card">
      <span class="cart-overview__label">合計金額</span>
      <strong>¥<?= number_format((int) $total) ?></strong>
      <p class="muted">表示価格は現在の会員区分に応じて自動で切り替わります。</p>
    </div>
  </div>

  <?php if ($lines === []): ?>
    <div class="notice notice--empty">
      <h2>カートは空です</h2>
      <p class="muted">商品検索から商品を追加すると、この画面にまとめて表示されます。</p>
      <p><a class="button button--primary" href="/search">商品検索へ戻る</a></p>
    </div>
  <?php else: ?>
    <div class="section-stack">
      <div class="cart-list">
        <?php foreach ($lines as $line): ?>
          <?php $product = $line['product']; ?>
          <?php $productId = (string) ($product['id'] ?? 0); ?>
          <article class="cart-card cart-card--active">
            <div class="cart-card__main">
              <div class="cart-card__meta">
                <span class="pill">カート内商品</span>
                <span class="catalog-code"><?= e((string) ($product['parts_num'] ?? '')) ?></span>
              </div>
              <h2 class="cart-card__title"><?= e((string) ($product['make'] ?? '')) ?> <?= e((string) ($product['name'] ?? '')) ?></h2>
              <div class="result-grid">
                <div class="result-grid__item">
                  <strong>型式</strong>
                  <span><?= e((string) ($product['katasiki'] ?? '-')) ?></span>
                </div>
                <div class="result-grid__item">
                  <strong>ミッション</strong>
                  <span><?= e((string) ($product['toc'] ?? '-')) ?></span>
                </div>
                <div class="result-grid__item">
                  <strong>価格区分</strong>
                  <span><?= e((string) ($product['display_price_label'] ?? '価格')) ?></span>
                </div>
                <div class="result-grid__item">
                  <strong>単価</strong>
                  <span>¥<?= number_format((int) $line['unit_price']) ?></span>
                </div>
              </div>
            </div>

            <div class="cart-card__side">
              <form class="cart-inline-form" action="/cart/update" method="post">
                <label class="cart-qty">
                  数量
                  <input
                    type="number"
                    name="qty[<?= e($productId) ?>]"
                    min="0"
                    <?php if ((int) ($line['max_qty'] ?? 0) > 0): ?>
                      max="<?= e((string) $line['max_qty']) ?>"
                    <?php endif; ?>
                    value="<?= e((string) $line['qty']) ?>"
                  >
                </label>
                <button type="submit" class="button">数量を更新</button>
              </form>

              <div class="cart-subtotal">
                小計
                <strong>¥<?= number_format((int) $line['subtotal']) ?></strong>
              </div>

              <div class="cart-card__actions cart-card__actions--stack">
                <a class="button button--ghost" href="/product/<?= e($productId) ?>">商品詳細へ</a>
                <form class="cart-inline-form" action="/cart/remove" method="post">
                  <input type="hidden" name="product_id" value="<?= e($productId) ?>">
                  <button type="submit" class="button button--danger">この商品を削除</button>
                </form>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

      <div class="cart-summary cart-summary--sticky">
        <div>
          <p class="cart-summary__label">ご注文金額合計</p>
          <p class="cart-summary__total">¥<?= number_format((int) $total) ?></p>
        </div>
        <div class="cart-summary__actions">
          <a class="button button--primary" href="/search">商品検索へ戻る</a>
          <a class="button button--ghost" href="/inquiry">まとめて問い合わせる</a>
        </div>
      </div>
    </div>
  <?php endif; ?>
</section>
