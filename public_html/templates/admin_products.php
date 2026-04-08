<?php declare(strict_types=1); ?>
<section class="admin-page">
  <div class="admin-page__header">
    <p class="admin-page__eyebrow">Products</p>
    <h1 class="admin-page__title"><?= e((string) ($pageTitle ?? '商品管理')) ?></h1>
    <p class="admin-page__lead"><?= e((string) ($pageLead ?? '')) ?></p>
  </div>

  <div class="admin-grid admin-grid--products">
    <article class="admin-card">
      <h2>商品データ</h2>
      <p>商品追加・商品更新・CSV 出力の入口をこのセクションへまとめていきます。</p>
      <span class="admin-card__status">一覧と検索を先行実装</span>
    </article>
    <article class="admin-card">
      <h2>型式 / OEM</h2>
      <p>型式更新、OEM 更新、画像アップロードは次段階で統合予定です。</p>
      <span class="admin-card__status">続いて着手</span>
    </article>
    <article class="admin-card">
      <h2>旧メニュー構成</h2>
      <div class="admin-chip-list">
        <span class="admin-chip">商品更新</span>
        <span class="admin-chip">型式更新</span>
        <span class="admin-chip">OEM更新</span>
        <span class="admin-chip">商品検索</span>
        <span class="admin-chip">画像追加</span>
        <span class="admin-chip">商品追加</span>
      </div>
    </article>
  </div>

  <article class="admin-card admin-search-card">
    <div class="admin-panel__header">
      <div>
        <p class="admin-page__eyebrow">Product Search</p>
        <h2 class="admin-panel__title">商品検索</h2>
      </div>
    </div>

    <form action="/admin/products" method="get" class="admin-search-form">
      <label class="field">
        <span>検索キーワード</span>
        <input type="text" name="key" value="<?= e((string) ($keyword ?? '')) ?>" placeholder="型式 / 品番 / CMC / サプライヤー品番">
      </label>
      <button type="submit" class="v0-button">検索</button>
    </form>

    <?php if (($hasSearched ?? false) === false): ?>
      <p class="admin-muted">型式、品番、CMC、サプライヤー品番などで検索できます。</p>
    <?php else: ?>
      <p class="admin-muted"><?= number_format(count($products ?? [])) ?> 件を表示しています。最大 100 件まで表示します。</p>
    <?php endif; ?>
  </article>

  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>品番</th>
          <th>CMC</th>
          <th>サプライヤー</th>
          <th>大和品番</th>
          <th>型式 / 車種</th>
          <th>標準価格</th>
          <th>.NET</th>
          <th>.BIZ</th>
          <th>在庫</th>
        </tr>
      </thead>
      <tbody>
        <?php if (($products ?? []) === []): ?>
          <tr>
            <td colspan="10"><?= ($hasSearched ?? false) ? '該当する商品はありませんでした。' : '検索キーワードを入力すると商品一覧を表示します。' ?></td>
          </tr>
        <?php else: ?>
          <?php foreach (($products ?? []) as $product): ?>
            <?php $stock = $product['stock'] ?? null; ?>
            <tr>
              <td><?= e((string) ($product['p_id'] ?? '-')) ?></td>
              <td><strong><?= e((string) ($product['parts_num'] ?? '-')) ?></strong></td>
              <td><?= e((string) ($product['web_num'] ?? '-')) ?></td>
              <td><?= e((string) ($product['supp_num'] ?? '-')) ?></td>
              <td><?= e((string) ($product['daiwa_num'] ?? '-')) ?></td>
              <td>
                <?= e((string) ($product['katasiki'] ?? '-')) ?><br>
                <span class="admin-muted-inline">
                  <?= e(trim((string) (($product['make'] ?? '') . ' ' . ($product['name'] ?? '')))) ?>
                  <?php if ((int) ($product['model_count'] ?? 0) > 1): ?>
                    / <?= number_format((int) ($product['model_count'] ?? 0)) ?> 型式
                  <?php endif; ?>
                </span>
              </td>
              <td>¥<?= number_format((int) ($product['price'] ?? 0)) ?></td>
              <td>¥<?= number_format((int) ($product['member_price'] ?? 0)) ?></td>
              <td>¥<?= number_format((int) ($product['special_price'] ?? 0)) ?></td>
              <td>
                <?php if ($stock === null): ?>
                  -
                <?php else: ?>
                  <span class="<?= (int) $stock > 0 ? 'admin-stock admin-stock--ok' : 'admin-stock admin-stock--none' ?>">
                    <?= number_format((int) $stock) ?>
                  </span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
