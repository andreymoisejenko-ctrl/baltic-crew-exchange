<?php
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';

$crewStmt = $pdo->query("SELECT * FROM listings WHERE type='crew' AND status='published' ORDER BY is_demo ASC, published_at DESC");
$crews = $crewStmt->fetchAll();
$projectStmt = $pdo->query("SELECT * FROM listings WHERE type='project' AND status='published' ORDER BY is_demo ASC, published_at DESC");
$projects = $projectStmt->fetchAll();
$message = (string)($_GET['message'] ?? '');
$formError = (string)($_SESSION['form_error'] ?? '');
unset($_SESSION['form_error']);

function tags(string $value): array
{
    return array_values(array_filter(array_map('trim', preg_split('/[,;\n]+/', $value) ?: [])));
}

function display_date(?string $date): string
{
    return $date ? date('j M Y', strtotime($date)) : 'On request';
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Baltic Crew Exchange — Industrial crew marketplace</title>
  <meta name="description" content="Discover available Baltic contractor crews and open European industrial project requirements. Direct B2B cooperation between legal entities.">
  <meta name="theme-color" content="#08263b">
  <meta property="og:title" content="Baltic Crew Exchange">
  <meta property="og:description" content="Available industrial crews and open European project requirements.">
  <meta property="og:type" content="website">
  <link rel="stylesheet" href="assets/styles.css">
  <script src="assets/app.js" defer></script>
</head>
<body>
<header class="site-header">
  <div class="container nav">
    <a class="brand" href="#top" aria-label="Baltic Crew Exchange home"><span class="mark">BC</span><span>Baltic Crew<small>EXCHANGE</small></span></a>
    <nav class="nav-links" id="navLinks"><a href="#crews">Available crews</a><a href="#projects">Open projects</a><a href="#how">How it works</a><a href="#trust">Trust</a></nav>
    <div class="nav-actions"><button class="btn secondary" data-open-form="crew">List a crew</button><button class="btn primary" data-open-form="project">Request a crew</button><button class="menu" id="menuButton" aria-label="Open menu">☰</button></div>
  </div>
</header>

<main id="top">
<?php if ($message === 'submission-received'): ?><div class="toast success">Thank you. Your submission is now waiting for review.</div><?php elseif ($message === 'interest-received'): ?><div class="toast success">Your introduction request has been received.</div><?php endif; ?>
<?php if ($formError): ?><div class="toast error"><?= e($formError) ?></div><?php endif; ?>

<section class="hero">
  <div class="container hero-grid">
    <div>
      <span class="eyebrow"><span></span> Founding pilot · Managed B2B introductions</span>
      <h1>Available crews.<br>Open projects.<br><em>One direct connection.</em></h1>
      <p class="hero-copy">A managed marketplace connecting Baltic company-owned industrial crews with European project requirements. Companies contract directly; we qualify the opportunity and open the introduction.</p>
      <div class="button-row"><button class="btn primary large" data-open-form="crew">List available crew →</button><button class="btn light large" data-open-form="project">Submit project requirement</button></div>
      <p class="microcopy">No individual recruitment · No payroll intermediary · Contact details stay private until mutual interest</p>
    </div>
    <aside class="hero-panel">
      <div class="live-row"><span class="pulse"></span><strong>Marketplace pilot is open</strong></div>
      <h2>Founding Pilot Offer</h2>
      <p>The first 10 approved crew providers receive free profile creation, publication and introductions during the initial 90-day pilot.</p>
      <ul><li>No subscription during the pilot</li><li>No commission during the pilot</li><li>Future pricing communicated in advance</li></ul>
      <span class="fine">Submissions are reviewed. Submission does not guarantee publication or introduction.</span>
    </aside>
  </div>
</section>

<section class="market-nav" aria-label="Marketplace summary">
  <div class="container market-tabs"><a href="#crews"><strong><?= count($crews) ?></strong><span>published crew profiles</span></a><a href="#projects"><strong><?= count($projects) ?></strong><span>open project requirements</span></a><div><strong>4</strong><span>industries covered</span></div><div><strong>B2B</strong><span>legal entities only</span></div></div>
</section>

<section id="crews" class="market-section">
  <div class="container">
    <div class="section-head"><div><p class="kicker">Supply marketplace</p><h2>Available industrial crews</h2><p>Anonymous profiles submitted by contractor companies and reviewed before publication.</p></div><button class="btn primary" data-open-form="crew">+ List available crew</button></div>
    <div class="filter-bar"><input type="search" placeholder="Search skills or certifications" data-filter-search="crew"><select data-filter-industry="crew"><option value="">All industries</option><option>Construction & Infrastructure</option><option>Industrial & Energy</option><option>Marine & Shipyard</option><option>NDT & Technical Inspection</option></select><select data-filter-country="crew"><option value="">All countries</option><option>Latvia</option><option>Lithuania</option><option>Estonia</option></select></div>
    <div class="listing-grid" data-listing-grid="crew">
      <?php if (!$crews): ?>
        <article class="empty-card"><span class="example-badge">Marketplace opening</span><h3>Founding crew profiles are being reviewed</h3><p>Be among the first Baltic providers to publish available industrial capacity during the free founding pilot.</p><button class="btn primary" data-open-form="crew">Submit crew profile</button></article>
      <?php endif; ?>
      <?php foreach ($crews as $crew): ?>
        <article class="listing-card" data-industry="<?= e(strtolower($crew['industry'])) ?>" data-country="<?= e(strtolower($crew['country'])) ?>" data-search="<?= e(strtolower($crew['title'].' '.$crew['specialisations'].' '.$crew['certifications'])) ?>">
          <div class="card-top"><div><span class="listing-id"><?= e($crew['public_id']) ?></span><?php if ($crew['is_demo']): ?><span class="example-badge">Example profile</span><?php else: ?><span class="verified-badge">Provider submitted</span><?php endif; ?></div><span class="availability">Availability to confirm</span></div>
          <h3><?= e($crew['title']) ?></h3><p class="location"><?= e($crew['country']) ?><?= $crew['city'] ? ' · '.e($crew['city']) : '' ?> · <?= e($crew['industry']) ?></p>
          <div class="card-facts"><div><span>Crew size</span><strong><?= e((string)($crew['people_count'] ?: 'On request')) ?></strong></div><div><span>Available from</span><strong><?= e(display_date($crew['available_from'])) ?></strong></div><div><span>Duration</span><strong><?= e($crew['duration'] ?: 'Flexible') ?></strong></div></div>
          <div class="tag-row"><?php foreach (array_slice(tags($crew['specialisations']), 0, 5) as $tag): ?><span><?= e($tag) ?></span><?php endforeach; ?></div>
          <?php if ($crew['certifications']): ?><p class="card-note"><strong>Certificates:</strong> <?= e($crew['certifications']) ?></p><?php endif; ?>
          <button class="btn card-button" data-interest-id="<?= (int)$crew['id'] ?>" data-interest-code="<?= e($crew['public_id']) ?>" data-interest-type="crew">Request introduction →</button>
        </article>
      <?php endforeach; ?>
    </div>
    <p class="no-match" data-no-match="crew">No profiles match these filters.</p>
  </div>
</section>

<section id="projects" class="market-section alt-bg">
  <div class="container">
    <div class="section-head"><div><p class="kicker">Demand marketplace</p><h2>Open project requirements</h2><p>Anonymous workforce requirements from project companies looking for qualified contractor capacity.</p></div><button class="btn primary" data-open-form="project">+ Submit project requirement</button></div>
    <div class="filter-bar"><input type="search" placeholder="Search location or specialisation" data-filter-search="project"><select data-filter-industry="project"><option value="">All industries</option><option>Construction & Infrastructure</option><option>Industrial & Energy</option><option>Marine & Shipyard</option><option>NDT & Technical Inspection</option></select><select data-filter-country="project"><option value="">All countries</option><option>Germany</option><option>Sweden</option><option>Finland</option><option>Norway</option><option>Denmark</option><option>Netherlands</option><option>Belgium</option></select></div>
    <div class="listing-grid" data-listing-grid="project">
      <?php if (!$projects): ?>
        <article class="listing-card example" data-industry="industrial & energy" data-country="germany" data-search="welders germany leipzig industrial installation iso 9606">
          <div class="card-top"><div><span class="listing-id">BCE-R-EXAMPLE</span><span class="example-badge">Example request</span></div><span class="availability open">Open example</span></div>
          <h3>3 certified welders required</h3><p class="location">Germany · Leipzig · Industrial & Energy</p>
          <div class="card-facts"><div><span>Team size</span><strong>3</strong></div><div><span>Planned start</span><strong>Example date</strong></div><div><span>Duration</span><strong>3 weeks</strong></div></div>
          <div class="tag-row"><span>Welders</span><span>ISO 9606</span><span>Industrial installation</span></div>
          <p class="card-note">Illustrative format only — this is not a real project requirement.</p><button class="btn card-button" disabled>Example only</button>
        </article>
        <article class="empty-card"><span class="example-badge">Marketplace opening</span><h3>Have an active workforce requirement?</h3><p>Publish an anonymous project request and receive relevant offers from Baltic contractor companies.</p><button class="btn primary" data-open-form="project">Submit project requirement</button></article>
      <?php endif; ?>
      <?php foreach ($projects as $project): ?>
        <article class="listing-card" data-industry="<?= e(strtolower($project['industry'])) ?>" data-country="<?= e(strtolower($project['country'])) ?>" data-search="<?= e(strtolower($project['title'].' '.$project['specialisations'].' '.$project['city'].' '.$project['certifications'])) ?>">
          <div class="card-top"><div><span class="listing-id"><?= e($project['public_id']) ?></span><?php if ($project['is_demo']): ?><span class="example-badge">Example request</span><?php else: ?><span class="verified-badge">Buyer submitted</span><?php endif; ?></div><span class="availability open">Open request</span></div>
          <h3><?= e($project['title']) ?></h3><p class="location"><?= e($project['country']) ?><?= $project['city'] ? ' · '.e($project['city']) : '' ?> · <?= e($project['industry']) ?></p>
          <div class="card-facts"><div><span>People required</span><strong><?= e((string)($project['people_count'] ?: 'On request')) ?></strong></div><div><span>Planned start</span><strong><?= e(display_date($project['available_from'])) ?></strong></div><div><span>Duration</span><strong><?= e($project['duration'] ?: 'To confirm') ?></strong></div></div>
          <div class="tag-row"><?php foreach (array_slice(tags($project['specialisations']), 0, 5) as $tag): ?><span><?= e($tag) ?></span><?php endforeach; ?></div>
          <?php if ($project['accommodation']): ?><p class="card-note"><strong>Travel / accommodation:</strong> <?= e($project['accommodation']) ?></p><?php endif; ?>
          <button class="btn card-button" data-interest-id="<?= (int)$project['id'] ?>" data-interest-code="<?= e($project['public_id']) ?>" data-interest-type="project">Offer your crew →</button>
        </article>
      <?php endforeach; ?>
    </div>
    <p class="no-match" data-no-match="project">No requests match these filters.</p>
  </div>
</section>

<section id="how" class="how-section"><div class="container"><div class="center-head"><p class="kicker">Managed matching</p><h2>Simple outside. Carefully managed inside.</h2><p>Technology structures every submission; a human review protects both parties before details are disclosed.</p></div><div class="steps-grid"><article><span>01</span><h3>Submit capacity or demand</h3><p>Complete one structured form. The system creates an anonymous draft automatically.</p></article><article><span>02</span><h3>Review and publication</h3><p>We check the information, standardise the profile and publish approved listings.</p></article><article><span>03</span><h3>Mutual interest</h3><p>Companies request an introduction without exposing contact details publicly.</p></article><article><span>04</span><h3>Direct B2B agreement</h3><p>After both sides agree, contacts are disclosed and the companies contract directly.</p></article></div></div></section>

<section id="trust" class="trust-section"><div class="container trust-grid"><div><p class="kicker">Trust and compliance</p><h2>Designed around company-to-company cooperation.</h2><p>Baltic Crew Exchange facilitates qualified introductions. It does not employ, payroll or dispatch individual workers.</p></div><ul><li><strong>Legal entities only</strong><span>The provider remains responsible for its employees and obligations.</span></li><li><strong>Private by default</strong><span>Company identity and contacts are disclosed only after mutual interest.</span></li><li><strong>Evidence-based review</strong><span>Certificates, insurance and company documents may be requested.</span></li><li><strong>Direct contract</strong><span>Commercial and project terms are agreed directly between both companies.</span></li></ul></div></section>

<section class="final-cta"><div class="container final-box"><div><p class="kicker">Founding pilot</p><h2>Turn idle capacity into the next project.</h2><p>Publish available company-owned crew capacity or tell us what your project needs.</p></div><div class="button-row"><button class="btn cyan large" data-open-form="crew">List available crew</button><button class="btn light large" data-open-form="project">Request a crew</button></div></div></section>
</main>

<footer><div class="container footer-grid"><div class="brand"><span class="mark">BC</span><span>Baltic Crew<small>EXCHANGE</small></span></div><p>Baltic Crew Exchange is an early-stage managed B2B marketplace. Participation does not create a contractual obligation.</p><a href="mailto:info@balticcrewexchange.eu">info@balticcrewexchange.eu</a></div></footer>

<dialog class="modal" id="listingModal"><form method="dialog" class="modal-shell"><button class="modal-close" aria-label="Close">×</button><div class="modal-intro"><p class="kicker" id="formKicker">Provider submission</p><h2 id="formTitle">List available crew</h2><p id="formDescription">Required information becomes an anonymous draft profile. Nothing is published before review.</p></div></form><form method="post" action="api/submit-listing.php" class="submission-form" id="listingForm"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="type" id="listingType" value="crew"><input class="honeypot" name="website" tabindex="-1" autocomplete="off"><div class="form-grid"><label>Country*<input name="country" required placeholder="e.g. Latvia"></label><label>City / project location<input name="city" placeholder="Optional"></label><label>Industry*<select name="industry" required><option value="">Select industry</option><option>Construction & Infrastructure</option><option>Industrial & Energy</option><option>Marine & Shipyard</option><option>NDT & Technical Inspection</option></select></label><label><span id="countLabel">Available people*</span><input type="number" min="1" max="500" name="people_count" required></label><label class="wide"><span id="skillsLabel">Specialisations*</span><textarea name="specialisations" required placeholder="Welders, pipefitters, mechanical fitters"></textarea></label><label><span id="fromLabel">Available from*</span><input type="date" name="available_from" required></label><label><span id="untilLabel">Available until</span><input type="date" name="available_until"></label><label>Minimum / expected duration<input name="duration" placeholder="e.g. 3 weeks or 1–2 months"></label><label>Languages<input name="languages" placeholder="English, Latvian, Russian"></label><label class="wide"><span id="mobilityLabel">Countries the crew can work in</span><input name="mobility" placeholder="Germany, Sweden, Finland, Baltics"></label><label class="wide">Certificates and approvals<textarea name="certifications" placeholder="ISO 9606-1, ISO 9712 Level II, SCC, etc."></textarea></label><label class="wide"><span id="experienceLabel">Relevant experience</span><textarea name="experience" placeholder="Industries, projects, equipment or work scopes"></textarea></label><label class="wide">Rate / commercial model<input name="rate_info" placeholder="Optional. Hourly rate, monthly price or on request"></label><label class="wide">Travel and accommodation<input name="accommodation" placeholder="Included, provided by client, negotiable"></label><label class="wide">Additional details<textarea name="description" rows="4"></textarea></label></div><fieldset><legend>Private company information</legend><p>These details are never shown on the public card.</p><div class="form-grid"><label>Legal company name*<input name="company_name" required></label><label>Registration number<input name="registration_number"></label><label>Contact person*<input name="contact_name" required></label><label>Business email*<input type="email" name="contact_email" required></label><label>Phone<input name="contact_phone"></label></div></fieldset><label class="consent"><input type="checkbox" name="consent" value="1" required><span>I confirm that I represent a legal entity, the information is accurate, and Baltic Crew Exchange may process this submission and publish an anonymous profile after review.</span></label><button class="btn primary large full" type="submit">Submit for review →</button><p class="form-fine">Submission does not guarantee publication. We may request documents or clarification before approving a listing.</p></form></dialog>

<dialog class="modal small" id="interestModal"><form method="dialog"><button class="modal-close" aria-label="Close">×</button></form><div class="modal-intro"><p class="kicker">Private introduction request</p><h2 id="interestTitle">Request introduction</h2><p>Your details will not be published. We will verify mutual interest before disclosing contacts.</p></div><form method="post" action="api/submit-interest.php" class="submission-form"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="listing_id" id="interestListingId"><input class="honeypot" name="website" tabindex="-1" autocomplete="off"><label>Company name*<input name="company_name" required></label><label>Your name*<input name="contact_name" required></label><label>Business email*<input type="email" name="contact_email" required></label><label>Phone<input name="contact_phone"></label><label>Message<textarea name="message" rows="4" placeholder="Briefly describe your interest and relevant requirements"></textarea></label><label class="consent"><input type="checkbox" name="consent" value="1" required><span>I agree that Baltic Crew Exchange may process these details to qualify and arrange a B2B introduction.</span></label><button class="btn primary large full">Send private request →</button></form></dialog>
</body></html>
