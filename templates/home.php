<?php declare(strict_types=1); ?>
<section class="hero">
  <div class="hero__panel">
    <span class="hero__kicker">Sample Test Site</span>
    <h1>Test top page for unified search, pricing, cart, and special member flow.</h1>
    <p>
      This page is for checking guest, member, and special member behavior.
      Special member registration is accepted even before login or purchase.
    </p>
    <div class="hero__signals" aria-label="Test points">
      <span class="signal-pill">Role-based pricing</span>
      <span class="signal-pill">Shared cart flow</span>
      <span class="signal-pill">Special member registration</span>
    </div>
    <div class="action-row">
      <a class="button button--primary" href="<?= e((string) ($specialEntryUrl ?? '/special-member/register')) ?>">特別会員受付フォーム</a>
      <a class="button button--ghost" href="/login">会員ログイン</a>
    </div>

    <form class="hero-search" action="/search" method="get">
      <div class="tab-row">
        <span class="tab-row__item tab-row__item--active">Domestic</span>
        <span class="tab-row__item">Import</span>
        <span class="tab-row__item">Part check</span>
      </div>
      <div class="hero-search__grid">
        <label>
          Make
          <select name="make">
            <option value="">Select</option>
            <?php foreach ($makes as $make): ?>
              <option value="<?= e($make) ?>"><?= e($make) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label>
          Model code
          <input type="text" name="katasiki" placeholder="ex: 6AA-MXPH15" data-auto-focus>
        </label>
        <label>
          Mission
          <select name="toc">
            <option value="">Select</option>
            <option value="A/T">A/T</option>
            <option value="M/T">M/T</option>
            <option value="CVT">CVT</option>
          </select>
        </label>
      </div>
      <div>
        <button type="submit">Search</button>
      </div>
      <input type="hidden" name="mode" value="2">
    </form>
  </div>

  <div class="hero__aside">
    <article class="card">
      <h2>Current target</h2>
      <p class="muted">Search result, product detail, role-based pricing, and shared cart behavior.</p>
    </article>
    <article class="card">
      <h2>Registration flow</h2>
      <p class="muted">Register first, receive mail guidance, log in, then upload the business card image.</p>
    </article>
    <article class="card">
      <h2>特別会員</h2>
      <p class="muted">申請後は書類アップロード待ち、アップロード後は承認待ち、承認後に特別会員価格へ切り替わります。</p>
    </article>
  </div>
</section>

<section class="content-grid">
  <article class="card">
    <h2>Notes</h2>
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
    <h2>Focus</h2>
    <div class="stack muted">
      <div>Only one price should be visible per role.</div>
      <div>Search and product pages should both add to the same cart.</div>
      <div>Top page can start the special member registration flow.</div>
    </div>
  </article>
</section>

<section class="section-stack page-block">
  <div class="page-header">
    <p class="hero__kicker">Checks</p>
    <h2 class="section-heading">Main things to verify now</h2>
    <p class="lead">This top page is simplified so we can focus on routing and behavior first.</p>
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
