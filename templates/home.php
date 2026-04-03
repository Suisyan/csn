<?php declare(strict_types=1); ?>
<section class="hero hero--storefront">
  <div class="hero__legacy-grid">
    <div class="hero__panel hero__panel--legacy-search">
      <div class="tab-row">
        <span class="tab-row__item tab-row__item--active">日本車検索</span>
        <span class="tab-row__item">輸入車検索</span>
        <span class="tab-row__item">純正品番検索</span>
      </div>
      <span class="hero__kicker">Cooling Shop.net</span>
      <h1>車検証の情報から<br>ラジエーターを簡単検索</h1>
      <p class="hero__lead hero__lead--compact">
        メーカーを選び、型式とミッションを入力するだけで、
        ラジエーター検索をスムーズに進められます。
      </p>
      <form class="hero-search hero-search--legacy" action="/search" method="get">
        <div class="hero-search__grid">
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
        <div class="action-row action-row--hero">
          <button type="submit" class="action-row__priority">商品検索へ進む</button>
          <a class="button button--ghost" href="<?= e((string) ($specialEntryUrl ?? '/special-member/register')) ?>">特別会員受付</a>
        </div>
        <input type="hidden" name="mode" value="2">
      </form>
    </div>

    <div class="hero__panel hero__panel--legacy-copy">
      <span class="hero__kicker">全品送料無料</span>
      <h2 class="hero__subheading">ラジエーター交換・修理・水漏れの際は<br>クーリングショップの検索導線へ</h2>
      <div class="stack muted">
        <div>1. メーカーを選ぶ</div>
        <div>2. 車検証から型式を入力</div>
        <div>3. ミッションを選択</div>
      </div>
      <p class="muted">
        旧サイトのトップで使われていた「3ステップで特定」の考え方を、
        現在の検索導線に合わせて引き継いでいます。余計な入力を増やさず、
        安心して商品を探せる構成です。
      </p>
      <div class="action-row action-row--hero">
        <a class="button button--primary" href="/search">商品を探す</a>
        <a class="button button--ghost" href="/inquiry">お問い合わせ</a>
      </div>
    </div>
  </div>
</section>

<section class="home-legacy-content">
  <div class="home-legacy-content__grid">
    <aside class="home-legacy-side card">
      <h2 class="home-legacy-side__title">ご案内</h2>
      <div class="stack muted">
        <?php foreach ($newsItems as $item): ?>
          <div class="home-topics__item home-topics__item--block">
            <strong><?= e($item['date']) ?></strong>
            <span><?= e($item['title']) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="stack muted">
        <a href="/inquiry">お問い合わせ</a>
        <a href="/login">ログイン</a>
        <a href="/cart">カートを見る</a>
        <a href="<?= e((string) ($specialEntryUrl ?? '/special-member/register')) ?>">特別会員受付</a>
      </div>
    </aside>

    <div class="home-legacy-main">
      <div class="page-header page-header--centered">
        <p class="hero__kicker">Reasons</p>
        <h2 class="section-heading">当社のラジエーターが選ばれる理由</h2>
        <p class="lead">旧サイトの訴求内容をもとに、現在の導線で使いやすく再構成しています。</p>
      </div>
      <div class="reason-grid reason-grid--stacked">
        <article class="card card--feature card--legacy-reason">
          <h2>最長18ヶ月の長期保証</h2>
          <p class="muted">通常使用による水漏れや破損に対して、安心してご利用いただける保証を案内できる構成にしています。</p>
        </article>
        <article class="card card--feature card--legacy-reason">
          <h2>社外製新品を中心にご案内</h2>
          <p class="muted">品質安定と取付後の安心感を重視し、検索後も商品詳細で確認しやすい導線を保っています。</p>
        </article>
        <article class="card card--feature card--legacy-reason">
          <h2>車検証情報で検索しやすい</h2>
          <p class="muted">型式とミッションを中心に、誤検索や選定ミスを減らしやすい旧サイトの考え方を反映しています。</p>
        </article>
        <article class="card card--feature card--legacy-reason">
          <h2>国内在庫とスムーズな発送導線</h2>
          <p class="muted">在庫確認、価格確認、カート投入までを一連の流れで追えるように整えています。</p>
        </article>
        <article class="card card--feature card--legacy-reason">
          <h2>個人情報と問い合わせ導線に配慮</h2>
          <p class="muted">旧サイト同様に安心感を重視し、検索で見つからない場合も問い合わせへ自然に進める構成です。</p>
        </article>
      </div>
    </div>
  </div>
</section>

<section class="home-cta">
  <div class="home-cta__inner">
    <h2>お探しの商品が見つからない場合</h2>
    <p class="lead">掲載外の商品もお取り寄せ可能です。旧サイトと同様に、型式や車種情報をもとに適合確認とご案内へ進めます。</p>
    <div class="action-row action-row--hero action-row--hero-centered">
      <a class="button button--primary" href="/inquiry">お問い合わせフォームへ</a>
      <a class="button button--ghost" href="<?= e((string) ($specialEntryUrl ?? '/special-member/register')) ?>">特別会員受付フォーム</a>
    </div>
  </div>
</section>
