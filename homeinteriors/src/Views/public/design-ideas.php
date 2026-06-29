<?php
require __DIR__ . '/../partials/header.php';
$alias = $alias ?? null;
$globalAlias = is_array($globalAlias ?? null) ? $globalAlias : null;
$ideas = is_array($ideas ?? null) ? $ideas : [];
$sections = is_array($sections ?? null) ? $sections : [];
$filterOptions = is_array($filterOptions ?? null) ? $filterOptions : [];
$activeFilters = is_array($activeFilters ?? null) ? $activeFilters : [];
$titleText = (string)($alias['title'] ?? 'Interior Design Ideas');
$subtitleText = (string)($alias['subtitle'] ?? 'Browse interior design ideas by room, colour, city, style, layout, dimensions, and budget.');
$heroImage = (string)($alias['hero_image'] ?? ($ideas[0]['image_url'] ?? 'https://images.unsplash.com/photo-1600607687920-4e2a09cf159d?auto=format&fit=crop&w=1600&q=85'));
if (!empty($globalAlias['h1'])) {
  $titleText = (string)$globalAlias['h1'];
}
if (!empty($globalAlias['meta_description']) && !$alias) {
  $subtitleText = (string)$globalAlias['meta_description'];
}
if (!empty($globalAlias['image_url'])) {
  $heroImage = (string)$globalAlias['image_url'];
}
$globalAliasContent = trim((string)($globalAlias['content_html'] ?? ''));
$aliasType = trim((string)($alias['filter_type'] ?? ''));
$currentPath = parse_url((string)($_SERVER['REQUEST_URI'] ?? '/design-ideas'), PHP_URL_PATH) ?: '/design-ideas';
$sectionMap = [];
foreach ($sections as $section) {
  $sectionMap[(string)$section['section_key']] = $section;
}
$sectionItems = static fn(string $key): array => is_array($sectionMap[$key]['items_json'] ?? null) ? $sectionMap[$key]['items_json'] : [];
$money = static function (mixed $amount): string {
  $amount = (float)$amount;
  if ($amount <= 0) return 'On request';
  if ($amount >= 100000) return '₹' . rtrim(rtrim(number_format($amount / 100000, 2), '0'), '.') . ' L';
  return '₹' . number_format($amount, 0);
};
$renderTileSection = static function (array $section, string $className = ''): void {
  $items = is_array($section['items_json'] ?? null) ? $section['items_json'] : [];
  if (!$items) return;
  ?>
  <section class="design-browse-section <?= htmlspecialchars($className, ENT_QUOTES, 'UTF-8') ?>">
    <div class="container">
      <div class="design-section-head"><h2><?= htmlspecialchars((string)$section['title'], ENT_QUOTES, 'UTF-8') ?></h2><?php if (!empty($section['subtitle'])): ?><p><?= htmlspecialchars((string)$section['subtitle'], ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?></div>
      <div class="design-tile-grid">
        <?php foreach ($items as $item): ?>
          <a class="design-discovery-tile" href="<?= htmlspecialchars((string)($item['href'] ?? '/design-ideas'), ENT_QUOTES, 'UTF-8') ?>">
            <img src="<?= htmlspecialchars((string)($item['image'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($item['title'] ?? 'Design idea'), ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
            <span><?= htmlspecialchars((string)($item['label'] ?? 'Know more'), ENT_QUOTES, 'UTF-8') ?></span>
            <strong><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php
};
?>
<main class="design-idea-page">
  <section class="design-idea-hero design-reference-hero" style="--idea-hero:url('<?= htmlspecialchars($heroImage, ENT_QUOTES, 'UTF-8') ?>')">
    <div class="container">
      <nav class="property-breadcrumb"><a href="/">Home</a><span>/</span><a href="/design-ideas">Design Ideas</a><?php if ($alias): ?><span>/</span><span><?= htmlspecialchars($titleText, ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?></nav>
      <div class="design-hero-copy">
        <p class="eyebrow">Interior inspiration</p>
        <h1><?= htmlspecialchars($alias ? $titleText : 'Discover design ideas to express your style', ENT_QUOTES, 'UTF-8') ?></h1>
        <p><?= htmlspecialchars($subtitleText, ENT_QUOTES, 'UTF-8') ?></p>
        <button type="button" class="btn-primary quote-trigger" data-title="Get started with your interior design journey" data-requirement="Design ideas page consultation">Book free consultation</button>
      </div>
    </div>
  </section>

  <?php if (!empty($alias['intro_content']) || $globalAliasContent !== ''): ?><section class="design-intro-band"><div class="container"><div class="design-alias-copy"><?php if ($globalAliasContent !== ''): ?><?= $globalAliasContent ?><?php elseif (!empty($alias['intro_content'])): ?><?= nl2br(htmlspecialchars((string)$alias['intro_content'], ENT_QUOTES, 'UTF-8')) ?><?php endif; ?></div></div></section><?php endif; ?>

  <?php if ($alias): ?>
    <section class="design-alias-category-nav">
      <div class="container">
        <nav>
          <a href="/design-ideas">All</a>
          <?php foreach ($sectionItems('browse_by_rooms') as $item): ?>
            <?php $activeRoom = mb_strtolower((string)($item['title'] ?? '')) === mb_strtolower($aliasType); ?>
            <a class="<?= $activeRoom ? 'active' : '' ?>" href="<?= htmlspecialchars((string)($item['href'] ?? '/design-ideas'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></a>
          <?php endforeach; ?>
          <?php foreach ($sectionItems('browse_by_unit') as $item): ?>
            <a href="<?= htmlspecialchars((string)($item['href'] ?? '/design-ideas'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></a>
          <?php endforeach; ?>
        </nav>
      </div>
    </section>

    <?php
      $aliasTools = [
        ['title' => $aliasType === 'Kitchen' ? 'Kitchen calculator' : $titleText . ' budget guide', 'subtitle' => $aliasType === 'Kitchen' ? 'We have a modular kitchen solution for every style, need and budget.' : 'Estimate budget before shortlisting finishes and layouts.', 'href' => '/cost-calculator', 'cta' => 'Calculate now', 'image' => $heroImage],
        ['title' => 'Interior budget calculator', 'subtitle' => 'Know cost for your full home interiors.', 'href' => '/cost-calculator', 'cta' => 'Calculate now', 'image' => 'https://images.unsplash.com/photo-1600210491892-03d54c0aaf87?auto=format&fit=crop&w=900&q=85'],
        ['title' => 'Discover your design style', 'subtitle' => 'Get references customised around your room, colour and style preference.', 'href' => $currentPath . '#ideas', 'cta' => 'Explore ideas', 'image' => 'https://images.unsplash.com/photo-1618220179428-22790b461013?auto=format&fit=crop&w=900&q=85'],
      ];
    ?>
    <section class="design-alias-tools">
      <div class="container design-alias-tool-grid">
        <?php foreach ($aliasTools as $tool): ?>
          <a class="design-alias-tool-card" href="<?= htmlspecialchars((string)$tool['href'], ENT_QUOTES, 'UTF-8') ?>">
            <img src="<?= htmlspecialchars((string)$tool['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)$tool['title'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
            <div><h2><?= htmlspecialchars((string)$tool['title'], ENT_QUOTES, 'UTF-8') ?></h2><p><?= htmlspecialchars((string)$tool['subtitle'], ENT_QUOTES, 'UTF-8') ?></p><strong><?= htmlspecialchars((string)$tool['cta'], ENT_QUOTES, 'UTF-8') ?></strong></div>
          </a>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

  <?php if (!$alias): ?>
  <?php
    $renderTileSection($sectionMap['browse_by_rooms'] ?? [], 'design-room-section');
    $renderTileSection($sectionMap['browse_by_colours'] ?? [], 'design-colour-section');
    $renderTileSection($sectionMap['browse_by_style'] ?? [], 'design-style-section');
    $renderTileSection($sectionMap['browse_by_unit'] ?? [], 'design-unit-section');
  ?>

  <?php $trending = $sectionMap['trending_ideas'] ?? null; if ($trending && !empty($trending['items_json'])): ?>
    <section class="design-trending-section">
      <div class="container">
        <div class="design-section-head"><h2><?= htmlspecialchars((string)$trending['title'], ENT_QUOTES, 'UTF-8') ?></h2><?php if (!empty($trending['subtitle'])): ?><p><?= htmlspecialchars((string)$trending['subtitle'], ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?></div>
        <div class="design-trending-grid">
          <?php foreach ((array)$trending['items_json'] as $index => $item): ?>
            <article class="design-trending-card <?= $index === 0 ? 'featured' : '' ?>">
              <a href="<?= htmlspecialchars((string)($item['href'] ?? '/design-ideas'), ENT_QUOTES, 'UTF-8') ?>"><img src="<?= htmlspecialchars((string)($item['image'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($item['title'] ?? 'Trending design idea'), ENT_QUOTES, 'UTF-8') ?>" loading="lazy"></a>
              <div><h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3><button type="button" class="btn-link quote-trigger" data-title="Book free site visit" data-requirement="Book free site visit for <?= htmlspecialchars((string)($item['title'] ?? 'design idea'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)($item['cta'] ?? 'Book free site visit'), ENT_QUOTES, 'UTF-8') ?></button></div>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <section class="design-consult-section">
    <div class="container design-consult-shell">
      <div><p class="eyebrow">Get started</p><h2>Get started with HomeInteriors360</h2><p>Drop your details and our team will call you to book a preferred consultation slot with a suitable interior professional.</p></div>
      <form class="design-inline-lead" data-design-inline-form>
        <input type="hidden" name="requirement" value="Design ideas free consultation">
        <input type="hidden" name="source" value="design_ideas_inline">
        <label><span>Full Name</span><input name="name" required placeholder="Enter full name"></label>
        <label><span>Mobile Number</span><input name="phone" type="tel" required placeholder="+91"></label>
        <label><span>Email</span><input name="email" type="email" placeholder="Enter email address"></label>
        <label><span>City</span><input name="city" placeholder="City"></label>
        <label class="property-consent"><input type="checkbox" name="consent" value="1" required><span>I agree to the Privacy Policy and Terms & Conditions and consent to be contacted by phone, SMS, email, WhatsApp or RCS.</span></label>
        <button class="btn-primary" type="submit">Submit</button>
        <p class="form-message"></p>
      </form>
    </div>
  </section>

  <?php $tools = $sectionMap['planning_tools'] ?? null; if ($tools && !empty($tools['items_json'])): ?>
    <section class="design-tools-section">
      <div class="container">
        <div class="design-section-head"><h2><?= htmlspecialchars((string)$tools['title'], ENT_QUOTES, 'UTF-8') ?></h2><?php if (!empty($tools['subtitle'])): ?><p><?= htmlspecialchars((string)$tools['subtitle'], ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?></div>
        <div class="design-tool-grid">
          <?php foreach ((array)$tools['items_json'] as $item): ?>
            <a class="design-tool-card" href="<?= htmlspecialchars((string)($item['href'] ?? '/design-ideas'), ENT_QUOTES, 'UTF-8') ?>">
              <div><span><?= htmlspecialchars((string)($item['badge'] ?? 'Loved by homeowners'), ENT_QUOTES, 'UTF-8') ?></span><h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3><p><?= htmlspecialchars((string)($item['subtitle'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p><strong><?= htmlspecialchars((string)($item['cta'] ?? 'Get started'), ENT_QUOTES, 'UTF-8') ?></strong></div>
              <img src="<?= htmlspecialchars((string)($item['image'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($item['title'] ?? 'Planning tool'), ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>
  <?php endif; ?>

  <section class="section design-idea-filter-section" id="ideas">
    <div class="container">
      <div class="design-section-head"><h2><?= htmlspecialchars($alias ? 'Explore ' . mb_strtolower($titleText) : 'Explore all design ideas', ENT_QUOTES, 'UTF-8') ?></h2><p><?= htmlspecialchars($alias ? 'Filter ' . mb_strtolower($titleText) . ' by colour, city, style, and layout.' : 'Filter by room, colour, city, style, and layout.', ENT_QUOTES, 'UTF-8') ?></p></div>
      <form class="design-idea-filters" method="get">
        <?php if ($alias && $aliasType !== ''): ?><input type="hidden" name="type" value="<?= htmlspecialchars($aliasType, ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?>
        <label><span>Search</span><input name="q" value="<?= htmlspecialchars((string)($activeFilters['q'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="Search design, location, colour"></label>
        <?php foreach ([['type','Type','types'], ['color','Colour','colors'], ['city','City','cities'], ['style','Style','styles'], ['layout','Layout','layouts']] as $filter): ?>
          <?php if ($alias && $filter[0] === 'type') continue; ?>
          <label><span><?= $filter[1] ?></span><select name="<?= $filter[0] ?>"><option value="">All <?= strtolower($filter[1]) ?></option><?php foreach (($filterOptions[$filter[2]] ?? []) as $option): ?><option value="<?= htmlspecialchars((string)$option, ENT_QUOTES, 'UTF-8') ?>" <?= ($activeFilters[$filter[0]] ?? '') === $option ? 'selected' : '' ?>><?= htmlspecialchars((string)$option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
        <?php endforeach; ?>
        <button class="btn-primary" type="submit">Apply filters</button>
        <a class="btn-link" href="<?= htmlspecialchars(parse_url((string)($_SERVER['REQUEST_URI'] ?? '/design-ideas'), PHP_URL_PATH) ?: '/design-ideas', ENT_QUOTES, 'UTF-8') ?>">Clear</a>
      </form>

      <div class="design-idea-layout">
        <aside class="design-idea-alias-panel">
          <h2>Browse by room</h2>
          <nav><?php foreach (($aliases ?? []) as $item): ?><a href="/design-ideas/<?= htmlspecialchars((string)$item['slug'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$item['title'], ENT_QUOTES, 'UTF-8') ?></a><?php endforeach; ?></nav>
          <h2>Favourites</h2>
          <p id="designFavoriteCount">0 saved ideas</p>
        </aside>
        <div>
          <div class="design-idea-result-head"><p class="eyebrow eyebrow-dark"><?= count($ideas) ?> ideas</p><h2>Shortlist references and request a quote.</h2></div>
          <?php if (!$ideas): ?><div class="property-empty"><h3>No design ideas match these filters yet.</h3><p>Try a broader room type, colour, city, or style.</p></div><?php endif; ?>
          <div class="design-idea-grid">
            <?php foreach ($ideas as $idea): ?>
              <article class="design-idea-card" data-idea-id="<?= (int)$idea['id'] ?>" data-detail-url="/design-ideas/idea/<?= htmlspecialchars((string)$idea['slug'], ENT_QUOTES, 'UTF-8') ?>">
                <a href="/design-ideas/idea/<?= htmlspecialchars((string)$idea['slug'], ENT_QUOTES, 'UTF-8') ?>" class="design-idea-card-link" aria-label="View <?= htmlspecialchars((string)$idea['name'], ENT_QUOTES, 'UTF-8') ?>"></a>
                <div class="design-idea-card-image"><img src="<?= htmlspecialchars((string)$idea['image_url'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)$idea['name'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy"><span><?= htmlspecialchars((string)$idea['type'], ENT_QUOTES, 'UTF-8') ?></span></div>
                <div>
                  <button type="button" class="design-fav-btn quote-trigger" data-idea-id="<?= (int)$idea['id'] ?>" data-title="<?= htmlspecialchars((string)$idea['name'], ENT_QUOTES, 'UTF-8') ?>" data-requirement="Favourite design idea: <?= htmlspecialchars((string)$idea['name'], ENT_QUOTES, 'UTF-8') ?>" aria-label="Add to favourite">♡</button>
                  <h3><a href="/design-ideas/idea/<?= htmlspecialchars((string)$idea['slug'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$idea['name'], ENT_QUOTES, 'UTF-8') ?></a></h3>
                  <p><?= htmlspecialchars(trim((string)$idea['location'] . ', ' . (string)$idea['city'], ', '), ENT_QUOTES, 'UTF-8') ?></p>
                  <div class="design-card-meta"><span><?= htmlspecialchars((string)$idea['color'], ENT_QUOTES, 'UTF-8') ?></span><span><?= htmlspecialchars((string)$idea['style'], ENT_QUOTES, 'UTF-8') ?></span><span><?= (float)$idea['length_ft'] ?> x <?= (float)$idea['breadth_ft'] ?> x <?= (float)$idea['height_ft'] ?> ft</span></div>
                  <p><?= htmlspecialchars((string)$idea['short_description'], ENT_QUOTES, 'UTF-8') ?></p>
                  <div class="design-card-actions"><button type="button" class="btn-primary quote-trigger" data-idea-id="<?= (int)$idea['id'] ?>" data-title="<?= htmlspecialchars((string)$idea['name'], ENT_QUOTES, 'UTF-8') ?>" data-requirement="Quote for design idea: <?= htmlspecialchars((string)$idea['name'], ENT_QUOTES, 'UTF-8') ?>">Get quote</button><button type="button" class="btn-link quote-trigger" data-idea-id="<?= (int)$idea['id'] ?>" data-title="Free design consultation" data-requirement="Free design consultation for <?= htmlspecialchars((string)$idea['name'], ENT_QUOTES, 'UTF-8') ?>">Free design consultation</button></div>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
          <?php if (!empty($alias['outro_content'])): ?><div class="design-alias-copy design-alias-outro"><?= nl2br(htmlspecialchars((string)$alias['outro_content'], ENT_QUOTES, 'UTF-8')) ?></div><?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <?php if ($alias): ?>
    <section class="design-specific-section">
      <div class="container">
        <div class="design-section-head"><h2>Looking for something specific?</h2><p>Explore focused <?= htmlspecialchars(mb_strtolower($aliasType ?: 'design'), ENT_QUOTES, 'UTF-8') ?> references by layout, style and colour.</p></div>
        <div class="design-chip-cloud">
          <?php
            $chipGroups = [
              'layout' => array_slice((array)($filterOptions['layouts'] ?? []), 0, 8),
              'style' => array_slice((array)($filterOptions['styles'] ?? []), 0, 8),
              'color' => array_slice((array)($filterOptions['colors'] ?? []), 0, 8),
            ];
            foreach ($chipGroups as $chipKey => $values):
              foreach ($values as $value):
                $query = http_build_query([$chipKey => $value]);
          ?>
            <a href="<?= htmlspecialchars($currentPath . '?' . $query, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') ?></a>
          <?php endforeach; endforeach; ?>
        </div>
      </div>
    </section>

    <section class="design-consult-section">
      <div class="container design-consult-shell">
        <div><p class="eyebrow">Get started</p><h2>Plan your <?= htmlspecialchars(mb_strtolower($aliasType ?: 'interior'), ENT_QUOTES, 'UTF-8') ?> with HomeInteriors360</h2><p>Share your details and we will connect you with a suitable professional for this room requirement.</p></div>
        <form class="design-inline-lead" data-design-inline-form>
          <input type="hidden" name="requirement" value="<?= htmlspecialchars($titleText . ' consultation', ENT_QUOTES, 'UTF-8') ?>">
          <input type="hidden" name="source" value="design_ideas_alias_inline">
          <?php if ($alias): ?><input type="hidden" name="alias_id" value="<?= (int)$alias['id'] ?>"><?php endif; ?>
          <label><span>Full Name</span><input name="name" required placeholder="Enter full name"></label>
          <label><span>Mobile Number</span><input name="phone" type="tel" required placeholder="+91"></label>
          <label><span>Email</span><input name="email" type="email" placeholder="Enter email address"></label>
          <label><span>City</span><input name="city" placeholder="City"></label>
          <label class="property-consent"><input type="checkbox" name="consent" value="1" required><span>I agree to the Privacy Policy and Terms & Conditions and consent to be contacted by phone, SMS, email, WhatsApp or RCS.</span></label>
          <button class="btn-primary" type="submit">Submit</button>
          <p class="form-message"></p>
        </form>
      </div>
    </section>
  <?php endif; ?>
</main>

<div class="design-quote-modal" id="designQuoteModal" hidden>
  <div class="design-quote-dialog">
    <button type="button" class="design-quote-close" aria-label="Close">×</button>
    <p class="eyebrow eyebrow-dark">Get quote</p>
    <h2 id="designQuoteTitle">Request design quote</h2>
    <form id="designQuoteForm">
      <input type="hidden" name="design_idea_id" value="">
      <?php if ($alias): ?><input type="hidden" name="alias_id" value="<?= (int)$alias['id'] ?>"><?php endif; ?>
      <input type="hidden" name="requirement" value="<?= htmlspecialchars($titleText, ENT_QUOTES, 'UTF-8') ?>">
      <input type="hidden" name="source" value="design_ideas">
      <label><span>Name</span><input name="name" required></label>
      <label><span>Phone</span><input name="phone" type="tel" required></label>
      <label><span>Email</span><input name="email" type="email"></label>
      <label><span>City</span><input name="city" value="<?= htmlspecialchars((string)($activeFilters['city'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></label>
      <label><span>Budget</span><select name="budget"><option value="">Select budget</option><?php require __DIR__ . '/../partials/budget-options.php'; ?></select></label>
      <label><span>Message</span><textarea name="message" rows="3" placeholder="Tell us what you liked and what you want to build"></textarea></label>
      <label class="property-consent"><input type="checkbox" name="consent" value="1" required><span>I agree to the Privacy Policy and Terms & Conditions and consent to be contacted by phone, SMS, email, WhatsApp or RCS.</span></label>
      <button class="btn-primary" type="submit">Submit quote request</button>
      <p class="form-message" id="designQuoteMessage"></p>
    </form>
  </div>
</div>
<script>
(() => {
  const favKey = 'hi360-design-favourites';
  const readFavs = () => JSON.parse(localStorage.getItem(favKey) || '[]');
  const writeFavs = (ids) => localStorage.setItem(favKey, JSON.stringify([...new Set(ids.map(String))]));
  const renderFavs = () => {
    const ids = readFavs();
    document.getElementById('designFavoriteCount').textContent = `${ids.length} saved idea${ids.length === 1 ? '' : 's'}`;
    document.querySelectorAll('.design-fav-btn').forEach((btn) => btn.textContent = ids.includes(String(btn.dataset.ideaId)) ? '♥' : '♡');
  };
  document.querySelectorAll('.design-fav-btn').forEach((btn) => btn.addEventListener('click', () => {
    const ids = readFavs();
    const id = String(btn.dataset.ideaId);
    writeFavs(ids.includes(id) ? ids.filter((item) => item !== id) : [...ids, id]);
    renderFavs();
  }));
  renderFavs();

  const modal = document.getElementById('designQuoteModal');
  const form = document.getElementById('designQuoteForm');
  document.querySelectorAll('.quote-trigger').forEach((btn) => btn.addEventListener('click', () => {
    form.elements.design_idea_id.value = btn.dataset.ideaId || '';
    form.elements.requirement.value = btn.dataset.requirement || btn.dataset.title || 'Design idea quote';
    document.getElementById('designQuoteTitle').textContent = btn.dataset.title || 'Request design quote';
    modal.hidden = false;
  }));
  document.querySelector('.design-quote-close')?.addEventListener('click', () => modal.hidden = true);
  modal?.addEventListener('click', (event) => { if (event.target === modal) modal.hidden = true; });
  form?.addEventListener('submit', async (event) => {
    event.preventDefault();
    const message = document.getElementById('designQuoteMessage');
    const response = await fetch('/api/design-idea-leads', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(Object.fromEntries(new FormData(form).entries())) });
    const data = await response.json();
    message.className = `form-message ${response.ok ? 'ok' : 'error'}`;
    message.textContent = response.ok ? 'Thank you. Our design team will contact you.' : (data.error || 'Could not submit quote request.');
    if (response.ok) setTimeout(() => { modal.hidden = true; form.reset(); }, 900);
  });
  document.querySelectorAll('[data-design-inline-form]').forEach((inlineForm) => {
    inlineForm.addEventListener('submit', async (event) => {
      event.preventDefault();
      const message = inlineForm.querySelector('.form-message');
      const response = await fetch('/api/design-idea-leads', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(Object.fromEntries(new FormData(inlineForm).entries())) });
      const data = await response.json();
      message.className = `form-message ${response.ok ? 'ok' : 'error'}`;
      message.textContent = response.ok ? 'Thank you. Our team will contact you for further details.' : (data.error || 'Could not submit details.');
      if (response.ok) inlineForm.reset();
    });
  });
})();
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
