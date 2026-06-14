<?php session_start(); ?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>HouseCentral — Find Your Boarding House</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --charcoal: #2C2B2B;
      --sage: #7BAF8A;
      --sage-light: #D4E8D9;
      --cream: #F7F5F2;
      --stone: #E8E4DF;
      --muted: #8A7B6F;
      --white: #FAFAFA;
    }

    html { scroll-behavior: smooth; }

    body {
      font-family: 'Inter', sans-serif;
      background-color: var(--cream);
      color: var(--charcoal);
      overflow-x: hidden;
    }

    /* ── NAV ── */
    nav {
      position: fixed;
      top: 0; left: 0; right: 0;
      z-index: 100;
      background: var(--charcoal);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 5vw;
      height: 62px;
    }

    .nav-brand {
      font-family: 'Playfair Display', serif;
      font-size: 1.25rem;
      color: var(--white);
      text-decoration: none;
      letter-spacing: 0.01em;
    }

    .nav-brand span {
      color: var(--sage);
    }

    .nav-links {
      display: flex;
      gap: 2rem;
      list-style: none;
    }

    .nav-links a {
      color: #ccc;
      text-decoration: none;
      font-size: 0.85rem;
      font-weight: 400;
      letter-spacing: 0.03em;
      transition: color 0.2s;
    }

    .nav-links a:hover { color: var(--white); }

    .nav-cta {
      background: var(--sage);
      color: var(--charcoal) !important;
      padding: 0.45rem 1.1rem;
      border-radius: 4px;
      font-weight: 600 !important;
    }

    /* ── HERO ── */
    .hero {
      min-height: 100vh;
      padding-top: 62px;
      display: grid;
      grid-template-columns: 1fr 1fr;
      align-items: center;
      gap: 0;
    }

    .hero-left {
      padding: 6vw 4vw 6vw 8vw;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .hero-eyebrow {
      font-size: 0.72rem;
      font-weight: 600;
      letter-spacing: 0.15em;
      text-transform: uppercase;
      color: var(--sage);
      margin-bottom: 1.4rem;
    }

    .hero-headline {
      font-family: 'Playfair Display', serif;
      font-size: clamp(2.4rem, 4.5vw, 4rem);
      line-height: 1.13;
      font-weight: 700;
      color: var(--charcoal);
      margin-bottom: 1.4rem;
    }

    .hero-headline em {
      font-style: italic;
      color: var(--sage);
    }

    .hero-sub {
      font-size: 1rem;
      line-height: 1.75;
      color: var(--muted);
      max-width: 420px;
      margin-bottom: 2.4rem;
    }

    .hero-actions {
      display: flex;
      gap: 1rem;
      flex-wrap: wrap;
    }

    .btn-primary {
      background: var(--charcoal);
      color: var(--white);
      padding: 0.8rem 1.8rem;
      border-radius: 4px;
      text-decoration: none;
      font-weight: 500;
      font-size: 0.9rem;
      letter-spacing: 0.02em;
      transition: background 0.2s;
    }

    .btn-primary:hover { background: #3f3e3e; }

    .btn-outline {
      background: transparent;
      color: var(--charcoal);
      padding: 0.8rem 1.8rem;
      border-radius: 4px;
      border: 1.5px solid var(--charcoal);
      text-decoration: none;
      font-weight: 500;
      font-size: 0.9rem;
      letter-spacing: 0.02em;
      transition: background 0.2s, color 0.2s;
    }

    .btn-outline:hover {
      background: var(--charcoal);
      color: var(--white);
    }

    /* ── HERO RIGHT: FLOATING CARD ── */
    .hero-right {
      background: var(--charcoal);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 4vw 3vw;
      position: relative;
      overflow: hidden;
    }

    .hero-right::before {
      content: '';
      position: absolute;
      inset: 0;
      background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%237BAF8A' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }

    .listing-card {
      background: var(--cream);
      border-radius: 12px;
      width: 100%;
      max-width: 360px;
      overflow: hidden;
      box-shadow: 0 24px 60px rgba(0,0,0,0.4);
      position: relative;
      z-index: 1;
    }

    .card-img-placeholder {
      height: 210px;
      background: linear-gradient(135deg, #5a8a6a 0%, #3a6a4a 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      overflow: hidden;
    }

    .card-img-placeholder::after {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(to bottom, transparent 40%, rgba(0,0,0,0.3));
    }

    .house-icon {
      font-size: 5rem;
      opacity: 0.3;
    }

    .card-badge {
      position: absolute;
      top: 1rem;
      right: 1rem;
      background: var(--sage);
      color: var(--charcoal);
      font-size: 0.7rem;
      font-weight: 700;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      padding: 0.3rem 0.7rem;
      border-radius: 20px;
      z-index: 2;
    }

    .card-body {
      padding: 1.4rem;
    }

    .card-title {
      font-family: 'Playfair Display', serif;
      font-size: 1.15rem;
      font-weight: 700;
      margin-bottom: 0.35rem;
    }

    .card-meta {
      font-size: 0.8rem;
      color: var(--muted);
      margin-bottom: 0.2rem;
    }

    .card-footer-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-top: 1rem;
      padding-top: 1rem;
      border-top: 1px solid var(--stone);
    }

    .card-price {
      font-family: 'Playfair Display', serif;
      font-size: 1.4rem;
      font-weight: 700;
      color: var(--charcoal);
    }

    .card-price small {
      font-family: 'Inter', sans-serif;
      font-size: 0.75rem;
      color: var(--muted);
      font-weight: 400;
    }

    .card-rent-btn {
      background: var(--charcoal);
      color: var(--white);
      border: none;
      padding: 0.5rem 1.2rem;
      border-radius: 4px;
      font-size: 0.8rem;
      font-weight: 500;
      cursor: pointer;
    }

    /* stat pills */
    .hero-stats {
      display: flex;
      gap: 0.7rem;
      margin-top: 2.5rem;
      flex-wrap: wrap;
    }

    .stat-pill {
      background: rgba(123,175,138,0.12);
      border: 1px solid rgba(123,175,138,0.25);
      padding: 0.5rem 1rem;
      border-radius: 30px;
      font-size: 0.78rem;
      color: var(--sage);
      font-weight: 500;
    }

    /* ── HOW IT WORKS ── */
    .section {
      padding: 7rem 8vw;
    }

    .section-eyebrow {
      font-size: 0.72rem;
      font-weight: 600;
      letter-spacing: 0.15em;
      text-transform: uppercase;
      color: var(--sage);
      margin-bottom: 0.8rem;
    }

    .section-heading {
      font-family: 'Playfair Display', serif;
      font-size: clamp(1.8rem, 3vw, 2.6rem);
      font-weight: 700;
      margin-bottom: 3.5rem;
      max-width: 500px;
      line-height: 1.25;
    }

    .steps-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 3rem;
    }

    .step {
      position: relative;
    }

    .step-connector {
      position: absolute;
      top: 2rem;
      left: calc(100% - 1.5rem);
      width: 3rem;
      height: 1px;
      background: var(--stone);
    }

    .step:last-child .step-connector { display: none; }

    .step-icon {
      width: 52px;
      height: 52px;
      background: var(--charcoal);
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 1.2rem;
      font-size: 1.4rem;
    }

    .step-title {
      font-family: 'Playfair Display', serif;
      font-size: 1.15rem;
      font-weight: 700;
      margin-bottom: 0.6rem;
    }

    .step-desc {
      font-size: 0.88rem;
      line-height: 1.75;
      color: var(--muted);
    }

    /* ── FEATURE STRIP ── */
    .feature-strip {
      background: var(--charcoal);
      padding: 6rem 8vw;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 5vw;
      align-items: center;
    }

    .feature-strip .section-eyebrow { color: var(--sage); }

    .feature-strip .section-heading {
      color: var(--white);
      margin-bottom: 1.5rem;
    }

    .feature-strip p {
      color: #aaa;
      font-size: 0.95rem;
      line-height: 1.8;
      margin-bottom: 2rem;
    }

    .feature-list {
      list-style: none;
      display: flex;
      flex-direction: column;
      gap: 0.9rem;
    }

    .feature-list li {
      display: flex;
      align-items: flex-start;
      gap: 0.75rem;
      font-size: 0.88rem;
      color: #ccc;
      line-height: 1.6;
    }

    .feature-list li::before {
      content: '✓';
      color: var(--sage);
      font-weight: 700;
      flex-shrink: 0;
      margin-top: 0.05rem;
    }

    /* mini card grid */
    .mini-cards {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
    }

    .mini-card {
      background: #3a3939;
      border-radius: 10px;
      overflow: hidden;
    }

    .mini-card-img {
      height: 90px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2.2rem;
    }

    .mini-card:nth-child(1) .mini-card-img { background: linear-gradient(135deg, #4a7a5a, #2a5a3a); }
    .mini-card:nth-child(2) .mini-card-img { background: linear-gradient(135deg, #5a6a8a, #3a4a6a); }
    .mini-card:nth-child(3) .mini-card-img { background: linear-gradient(135deg, #8a6a4a, #6a4a2a); }
    .mini-card:nth-child(4) .mini-card-img { background: linear-gradient(135deg, #7a5a8a, #5a3a6a); }

    .mini-card-info {
      padding: 0.8rem;
    }

    .mini-card-name {
      font-size: 0.8rem;
      font-weight: 600;
      color: var(--white);
      margin-bottom: 0.15rem;
    }

    .mini-card-city {
      font-size: 0.7rem;
      color: #888;
    }

    .mini-card-price {
      font-size: 0.75rem;
      font-weight: 600;
      color: var(--sage);
      margin-top: 0.3rem;
    }

    /* ── WHO IS IT FOR ── */
    .audience-section {
      padding: 7rem 8vw;
      background: var(--stone);
    }

    .audience-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 2rem;
      margin-top: 3.5rem;
    }

    .audience-card {
      background: var(--cream);
      border-radius: 12px;
      padding: 2.5rem;
      border-left: 4px solid var(--sage);
    }

    .audience-tag {
      font-size: 0.7rem;
      font-weight: 700;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: var(--sage);
      margin-bottom: 0.8rem;
    }

    .audience-title {
      font-family: 'Playfair Display', serif;
      font-size: 1.35rem;
      font-weight: 700;
      margin-bottom: 0.8rem;
    }

    .audience-desc {
      font-size: 0.88rem;
      line-height: 1.8;
      color: var(--muted);
      margin-bottom: 1.4rem;
    }

    .audience-link {
      font-size: 0.85rem;
      font-weight: 600;
      color: var(--charcoal);
      text-decoration: none;
      border-bottom: 2px solid var(--sage);
      padding-bottom: 1px;
      transition: color 0.2s;
    }

    .audience-link:hover { color: var(--sage); }

    /* ── CTA ── */
    .cta-section {
      padding: 8rem 8vw;
      text-align: center;
      background: var(--cream);
    }

    .cta-section .section-heading {
      margin: 0 auto 1.2rem;
      max-width: 600px;
    }

    .cta-section p {
      color: var(--muted);
      font-size: 1rem;
      line-height: 1.75;
      max-width: 480px;
      margin: 0 auto 2.4rem;
    }

    .cta-section .cta-actions {
      display: flex;
      gap: 1rem;
      justify-content: center;
      flex-wrap: wrap;
    }

    /* ── FOOTER ── */
    footer {
      background: var(--charcoal);
      padding: 2.5rem 8vw;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 1rem;
    }

    .footer-brand {
      font-family: 'Playfair Display', serif;
      color: var(--white);
      font-size: 1rem;
    }

    .footer-brand span { color: var(--sage); }

    footer p {
      font-size: 0.78rem;
      color: #666;
    }

    footer nav {
      position: static;
      background: transparent;
      height: auto;
      padding: 0;
      gap: 1.5rem;
    }

    footer nav a {
      font-size: 0.78rem;
      color: #888;
      text-decoration: none;
      transition: color 0.2s;
    }

    footer nav a:hover { color: var(--white); }

    /* ── RESPONSIVE ── */
    @media (max-width: 900px) {
      .hero {
        grid-template-columns: 1fr;
        min-height: auto;
      }

      .hero-left {
        padding: 4rem 6vw 3rem;
      }

      .hero-right {
        min-height: 50vh;
        padding: 3rem 6vw;
      }

      .steps-grid {
        grid-template-columns: 1fr;
        gap: 2rem;
      }

      .step-connector { display: none; }

      .feature-strip {
        grid-template-columns: 1fr;
      }

      .audience-grid {
        grid-template-columns: 1fr;
      }

      .section { padding: 4rem 6vw; }

      footer {
        flex-direction: column;
        text-align: center;
      }

      footer nav {
        flex-wrap: wrap;
        justify-content: center;
      }
    }

    @media (max-width: 600px) {
      .nav-links { display: none; }
    }

    @media (prefers-reduced-motion: no-preference) {
      .listing-card {
        animation: floatCard 4s ease-in-out infinite;
      }
    }

    @keyframes floatCard {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-10px); }
    }
  </style>
</head>

<body>

  <!-- NAV -->
  <nav>
    <a href="index.php" class="nav-brand">House<span>Central</span></a>
    <ul class="nav-links">
      <li><a href="index.php">Catalog</a></li>
      <li><a href="manage_listings.php">Account</a></li>
      <?php if (!isset($_SESSION['user_id'])): ?>
    <li><a href="login.php" class="nav-cta">Login</a></li>
<?php else: ?>
    <li><a href="logout.php" class="nav-cta">Logout</a></li>
<?php endif; ?>
    </ul>
  </nav>

  <!-- HERO -->
  <section class="hero">
    <div class="hero-left">
      <p class="hero-eyebrow">Boarding house rentals, simplified</p>
      <h1 class="hero-headline">
        Find a place<br>that feels like <em>home</em>
      </h1>
      <p class="hero-sub">
        Browse verified boarding houses across the Philippines. See real prices, real availability — and move in with confidence.
      </p>
      <div class="hero-actions">
        <a href="index.php" class="btn-primary">Browse Listings</a>
        <a href="register.php" class="btn-outline">List Your Property</a>
      </div>
      <div class="hero-stats">
        <span class="stat-pill">🏠 Available now</span>
        <span class="stat-pill">📍 Multiple cities</span>
        <span class="stat-pill">✅ Verified owners</span>
      </div>
    </div>

    <div class="hero-right">
      <div class="listing-card">
        <div class="card-img-placeholder">
          <span class="house-icon">🏡</span>
          <span class="card-badge">Available</span>
        </div>
        <div class="card-body">
          <h3 class="card-title">Rosario Boarding House</h3>
          <p class="card-meta">📍 Calamba, Laguna</p>
          <p class="card-meta">👥 Capacity: 8 persons</p>
          <div class="card-footer-row">
            <div class="card-price">
              ₱3,500<small>/mo</small>
            </div>
            <button class="card-rent-btn">Rent Now</button>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- HOW IT WORKS -->
  <section class="section">
    <p class="section-eyebrow">The process</p>
    <h2 class="section-heading">Renting a boarding house in three steps</h2>

    <div class="steps-grid">
      <div class="step">
        <div class="step-connector"></div>
        <div class="step-icon">🔍</div>
        <h3 class="step-title">Search the catalog</h3>
        <p class="step-desc">Filter by city, name, or availability. Every listing shows real capacity, price per month, and current status — no surprises.</p>
      </div>

      <div class="step">
        <div class="step-connector"></div>
        <div class="step-icon">📋</div>
        <h3 class="step-title">Choose your boarding house</h3>
        <p class="step-desc">Compare properties side by side. When you find the right fit, create a free account and submit your rental intent in one click.</p>
      </div>

      <div class="step">
        <div class="step-icon">🏠</div>
        <h3 class="step-title">Move in with confidence</h3>
        <p class="step-desc">Connect directly with the listed owner. No middlemen, no hidden fees. Just you and your new home.</p>
      </div>
    </div>
  </section>

  <!-- FEATURE STRIP -->
  <section class="feature-strip">
    <div>
      <p class="section-eyebrow">For property owners</p>
      <h2 class="section-heading">Your listing, your terms</h2>
      <p>Managing a boarding house doesn't have to mean chasing inquiries across different platforms. HouseCentral gives you a single place to list, update, and manage everything.</p>
      <ul class="feature-list">
        <li>Post a new property in under two minutes</li>
        <li>Upload photos and set your own monthly price</li>
        <li>Toggle availability whenever your status changes</li>
        <li>Edit or remove listings anytime from your account</li>
        <li>Reach renters actively looking in your city</li>
      </ul>
    </div>

    <div>
      <div class="mini-cards">
        <div class="mini-card">
          <div class="mini-card-img">🏠</div>
          <div class="mini-card-info">
            <p class="mini-card-name">Santillan BH</p>
            <p class="mini-card-city">Santa Rosa, Laguna</p>
            <p class="mini-card-price">₱2,800/mo</p>
          </div>
        </div>
        <div class="mini-card">
          <div class="mini-card-img">🏡</div>
          <div class="mini-card-info">
            <p class="mini-card-name">Blue Haven</p>
            <p class="mini-card-city">Batangas City</p>
            <p class="mini-card-price">₱3,200/mo</p>
          </div>
        </div>
        <div class="mini-card">
          <div class="mini-card-img">🏘</div>
          <div class="mini-card-info">
            <p class="mini-card-name">Dela Cruz Lodge</p>
            <p class="mini-card-city">Lipa, Batangas</p>
            <p class="mini-card-price">₱2,500/mo</p>
          </div>
        </div>
        <div class="mini-card">
          <div class="mini-card-img">🏰</div>
          <div class="mini-card-info">
            <p class="mini-card-name">Sunrise BH</p>
            <p class="mini-card-city">Lucena, Quezon</p>
            <p class="mini-card-price">₱3,000/mo</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- AUDIENCE -->
  <section class="audience-section">
    <p class="section-eyebrow">Who it's for</p>
    <h2 class="section-heading">Built for renters and owners alike</h2>

    <div class="audience-grid">
      <div class="audience-card">
        <p class="audience-tag">For renters</p>
        <h3 class="audience-title">Students, workers, and relocators</h3>
        <p class="audience-desc">Whether you're starting college, beginning a new job, or moving cities — browse available boarding houses near you with transparent pricing and no runaround.</p>
        <a href="index.php" class="audience-link">Browse listings →</a>
      </div>

      <div class="audience-card">
        <p class="audience-tag">For owners</p>
        <h3 class="audience-title">Landlords and property managers</h3>
        <p class="audience-desc">Got a vacant room or an entire boarding house? Register as an owner, upload your property, and start getting discovered by renters in your city today.</p>
        <a href="register.php" class="audience-link">List a property →</a>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="cta-section">
    <p class="section-eyebrow">Ready to begin?</p>
    <h2 class="section-heading">Your next home is already listed</h2>
    <p>Join HouseCentral and connect with boarding house owners across the Philippines — or list your own property and start earning.</p>
    <div class="cta-actions">
      <a href="index.php" class="btn-primary">View the Catalog</a>
<?php if (!isset($_SESSION['user_id'])): ?>
    <a href="register.php" class="btn-outline">Create an Account</a>
<?php endif; ?>
    </div>
  </section>

  <!-- FOOTER -->
  <footer>
    <div class="footer-brand">House<span>Central</span></div>
    <nav style="display:flex; gap:1.5rem; background:transparent; position:static; height:auto; padding:0;">
      <a href="index.php">Catalog</a>
      <a href="login.php">Login</a>
      <a href="register.php">Register</a>
    </nav>
    <p>© 2026 HouseCentral. All rights reserved.</p>
  </footer>

</body>
</html>