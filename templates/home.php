<?php declare(strict_types=1); ?>
<section class="relative overflow-hidden bg-primary py-24 lg:py-32">
  <div class="mx-auto max-w-7xl px-4 lg:px-8">
    <div class="flex flex-col items-center text-center">
      <p class="text-sm uppercase tracking-widest text-primary-foreground-70">Radiator Shop</p>
      <h1 class="mt-4 max-w-4xl text-balance text-4xl font-bold tracking-tight text-primary-foreground md:text-5xl lg:text-6xl">高品質な自動車用<br>ラジエーターを幅広く取り揃え</h1>
      <p class="mt-6 max-w-2xl text-pretty text-lg text-primary-foreground-80">
        国内外メーカー対応、迅速発送。プロから一般ユーザーまで、
        お車の冷却システムに最適なラジエーターをお届けします。
      </p>
      <div class="mt-10 flex flex-col gap-4 sm:flex-row">
        <a href="/search" class="v0-button v0-button--light">商品を探す <span class="ml-2">→</span></a>
        <a href="/inquiry" class="v0-button v0-button--outline-light">お問い合わせ</a>
        <a href="<?= e((string) ($specialEntryUrl ?? '/special-member/register')) ?>" class="v0-button v0-button--outline-light">特別会員受付</a>
      </div>
      <form class="v0-search-panel" action="/search" method="get">
        <div class="v0-search-tabs">
          <span class="v0-search-tab v0-search-tab--active">日本車検索</span>
          <span class="v0-search-tab">輸入車検索</span>
          <span class="v0-search-tab">適合確認</span>
        </div>
        <div class="v0-search-grid">
          <label>
            メーカー
            <select name="make">
              <option value="">メーカーを選択してください</option>
              <?php foreach ($makes as $make): ?>
                <option value="<?= e($make) ?>"><?= e($make) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label>
            型式
            <input type="text" name="katasiki" placeholder="例: E-RA1 / 6AA-MXPH15" data-auto-focus>
          </label>
          <label>
            ミッション
            <select name="toc">
              <option value="A/T">A/T</option>
              <option value="M/T">M/T</option>
              <option value="CVT">CVT</option>
            </select>
          </label>
        </div>
        <div class="mt-6 flex justify-center">
          <button type="submit" class="v0-button">商品検索へ進む</button>
        </div>
        <input type="hidden" name="mode" value="2">
      </form>
    </div>
  </div>
</section>

<section class="border-b border-border bg-card py-16">
  <div class="mx-auto max-w-7xl px-4 lg:px-8">
    <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
      <div class="flex flex-col items-center text-center">
        <div class="v0-feature-icon">T</div>
        <h3 class="mt-4 text-lg font-semibold">迅速発送</h3>
        <p class="mt-2 text-sm text-muted-foreground">検索から商品詳細、カートまで同じ流れで進められ、必要な商品をスムーズに確認できます。</p>
      </div>
      <div class="flex flex-col items-center text-center">
        <div class="v0-feature-icon">S</div>
        <h3 class="mt-4 text-lg font-semibold">品質保証</h3>
        <p class="mt-2 text-sm text-muted-foreground">旧サイトの安心感を引き継ぎつつ、現在の会員別価格や業務導線をそのまま保っています。</p>
      </div>
      <div class="flex flex-col items-center text-center">
        <div class="v0-feature-icon">P</div>
        <h3 class="mt-4 text-lg font-semibold">専門スタッフ対応</h3>
        <p class="mt-2 text-sm text-muted-foreground">型式や適合確認で迷った場合も、お問い合わせや特別会員受付へすぐに進める構成です。</p>
      </div>
    </div>
  </div>
</section>

<section class="py-16 lg:py-24">
  <div class="mx-auto max-w-7xl px-4 lg:px-8">
    <div class="flex flex-col items-center text-center">
      <p class="text-sm uppercase tracking-widest text-muted-foreground">Featured Products</p>
      <h2 class="mt-2 text-3xl font-bold tracking-tight">おすすめのご案内</h2>
      <p class="mt-4 max-w-2xl text-muted-foreground">検索導線と購入前に確認しやすいポイントを、v0 のカード構成に合わせて整理しています。</p>
    </div>
    <div class="mt-12 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
      <?php foreach ($reasons as $reason): ?>
        <article class="v0-card">
          <div class="v0-card__body">
            <p class="mb-1 text-xs text-muted-foreground">Guide</p>
            <h3 class="font-semibold leading-tight text-foreground"><?= e($reason['title']) ?></h3>
            <p class="mt-3 text-sm text-muted-foreground"><?= e($reason['body']) ?></p>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
    <div class="mt-12 flex justify-center">
      <a href="/search" class="v0-button v0-button--outline">すべての商品を見る <span class="ml-2">→</span></a>
    </div>
    <div class="home-topics mt-12">
      <?php foreach ($newsItems as $item): ?>
        <div class="home-topics__item">
          <strong><?= e($item['date']) ?></strong>
          <span><?= e($item['title']) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="bg-secondary py-16">
  <div class="mx-auto max-w-7xl px-4 lg:px-8">
    <div class="flex flex-col items-center text-center">
      <h2 class="text-2xl font-bold tracking-tight md:text-3xl">お探しの商品が見つからない場合</h2>
      <p class="mt-4 max-w-2xl text-muted-foreground">掲載外の商品もお取り寄せ可能です。品番や車種情報をお知らせください。専門スタッフが最適な商品をご提案いたします。</p>
      <div class="mt-8 flex flex-col gap-4 sm:flex-row">
        <a href="/inquiry" class="v0-button">お問い合わせフォームへ <span class="ml-2">→</span></a>
        <a href="/cart" class="v0-button v0-button--outline">カートを見る</a>
        <a href="<?= e((string) ($specialEntryUrl ?? '/special-member/register')) ?>" class="v0-button v0-button--outline">特別会員受付</a>
      </div>
    </div>
  </div>
</section>
