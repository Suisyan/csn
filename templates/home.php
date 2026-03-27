<?php declare(strict_types=1); ?>
<section class="hero">
  <div class="hero__panel">
    <span class="hero__kicker">ラジエター簡単検索</span>
    <h1>ラジエーター交換、ラジエーター修理、ラジエーター水漏れの際は、ネット販売のパイオニア。</h1>
    <p>
      輸入車、日本車ラジエーターのネット販売のパイオニアです。ラジエーター修理、ラジエーター交換なら、
      車検証1枚で検索、購入できます。全品送料無料です。
    </p>

    <form class="hero-search" action="/search" method="get">
      <div class="tab-row">
        <span class="tab-row__item tab-row__item--active">日本車ラジエーター検索</span>
        <span class="tab-row__item">輸入車ラジエーター検索</span>
        <span class="tab-row__item">純正品番検索</span>
      </div>
      <div class="hero-search__grid">
        <label>
          メーカー
          <select name="make">
            <option value="">選択してください</option>
            <?php foreach ($makes as $make): ?>
              <option value="<?= e($make) ?>"><?= e($make) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label>
          型式
          <input type="text" name="katasiki" placeholder="例: E-AE91" data-auto-focus>
        </label>
        <label>
          ミッション
          <select name="toc">
            <option value="">選択してください</option>
            <option value="A/T">A/T</option>
            <option value="M/T">M/T</option>
            <option value="CVT">CVT</option>
          </select>
        </label>
      </div>
      <div>
        <button type="submit">検索する</button>
      </div>
      <input type="hidden" name="mode" value="2">
    </form>
  </div>

  <div class="hero__aside">
    <article class="card">
      <h2>日本車ラジエーター＆コンデンサー検索</h2>
      <p class="muted">メーカー、型式、ミッションを入力して検索してください。例）E-RA1</p>
    </article>
    <article class="card">
      <h2>驚くほど簡単です</h2>
      <p class="muted">1. メーカーを選ぶ 2. 車検証から型式を入力 3. ミッション選択。この3ステップで、ラジエーター＆コンデンサーを特定します。</p>
    </article>
    <article class="card">
      <h2>業者さま向け</h2>
      <p class="muted">自動車整備・販売業者さま向けの特別会員導線も、今後この新基盤へ統合していきます。</p>
    </article>
  </div>
</section>

<section class="content-grid">
  <article class="card">
    <h2>新着情報</h2>
    <div class="stack muted">
      <?php foreach ($newsItems as $item): ?>
        <div>
          <strong><?= e($item['date']) ?></strong><br>
          <?= e($item['title']) ?>
        </div>
      <?php endforeach; ?>
    </div>
  </article>
  <article class="card">
    <h2>ご案内</h2>
    <div class="stack muted">
      <div>資料請求ページ、英語ページ、PayPal導線などの既存役割も順次整理して統合します。</div>
      <div>文言は基本維持し、見やすさと操作性のみを大きく改善していきます。</div>
    </div>
  </article>
</section>

<section class="section-stack page-block">
  <div class="page-header">
    <p class="hero__kicker">選ばれる5つの理由</p>
    <h2 class="section-heading">当社のラジエーターが選ばれる理由</h2>
    <p class="lead">旧トップページの訴求内容を、読みやすいレイアウトへ整理しています。</p>
  </div>
  <div class="reason-grid">
    <?php foreach ($reasons as $reason): ?>
      <article class="card">
        <h2><?= e($reason['title']) ?></h2>
        <p class="muted"><?= e($reason['body']) ?></p>
      </article>
    <?php endforeach; ?>
  </div>
</section>
