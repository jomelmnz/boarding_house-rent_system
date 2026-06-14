<?php
session_start();
include_once 'includes/database.php';

$object = new Dbh();
$conn = $object->connect();

if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_user'])) {
    $sql = "SELECT u.*, r.user_role 
            FROM tbl_user u
            JOIN tblink_user_role link ON u.user_ID = link.user_ID
            JOIN tb_role r ON link.role_id = r.role_id
            WHERE u.user_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$_COOKIE['remember_user']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $_SESSION['user_id'] = $user['user_ID'];
        $_SESSION['user_fname'] = $user['userFname'];
        $_SESSION['role'] = $user['user_role'];
    }
}

$search = isset($_GET['search']) ? $_GET['search'] : '';

if ($search != "") {
    $sql = "SELECT * FROM tb_boardhouse 
            WHERE house_name LIKE :search
            OR city LIKE :search
            OR bh_status LIKE :search";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':search', "%$search%");
} else {
    $sql = "SELECT * FROM tb_boardhouse";
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$houses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Catalog — HouseCentral</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --charcoal:   #2C2B2B;
            --sage:       #7BAF8A;
            --sage-light: #D4E8D9;
            --cream:      #F7F5F2;
            --stone:      #E8E4DF;
            --muted:      #8A7B6F;
            --white:      #FAFAFA;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--cream);
            color: var(--charcoal);
            min-height: 100vh;
        }

        /* ── NAV ── */
        nav {
            background: var(--charcoal);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 5vw;
            height: 62px;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-brand {
            font-family: 'Playfair Display', serif;
            font-size: 1.2rem;
            color: var(--white);
            text-decoration: none;
        }

        .nav-brand span { color: var(--sage); }

        .nav-links {
            display: flex;
            gap: 2rem;
            list-style: none;
            align-items: center;
        }

        .nav-links a {
            color: #ccc;
            text-decoration: none;
            font-size: 0.85rem;
            transition: color 0.2s;
        }

        .nav-links a:hover { color: var(--white); }
        .nav-links a.active { color: var(--white); }

        .nav-cta {
            background: var(--sage);
            color: var(--charcoal) !important;
            padding: 0.4rem 1rem;
            border-radius: 4px;
            font-weight: 600 !important;
        }

        /* ── HERO STRIP ── */
        .catalog-hero {
            background: var(--charcoal);
            padding: 3.5rem 5vw 0;
            position: relative;
            overflow: hidden;
        }

        .catalog-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%237BAF8A' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            pointer-events: none;
        }

        .hero-inner {
            position: relative;
            z-index: 1;
            max-width: 1100px;
            margin: 0 auto;
        }

        .hero-eyebrow {
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: var(--sage);
            margin-bottom: 0.6rem;
        }

        .hero-heading {
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.8rem, 3.5vw, 2.8rem);
            font-weight: 700;
            color: var(--white);
            line-height: 1.2;
            margin-bottom: 0.75rem;
        }

        .hero-heading em {
            font-style: italic;
            color: var(--sage);
        }

        .hero-sub {
            font-size: 0.9rem;
            color: #aaa;
            line-height: 1.7;
            max-width: 480px;
            margin-bottom: 2rem;
        }

        /* ── SEARCH BAR (inside hero, flush bottom) ── */
        .search-form {
            display: flex;
            max-width: 560px;
            background: var(--white);
            border-radius: 8px 8px 0 0;
            overflow: hidden;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.15);
        }

        .search-form input {
            flex: 1;
            padding: 0.85rem 1.2rem;
            border: none;
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            color: var(--charcoal);
            background: transparent;
            outline: none;
        }

        .search-form input::placeholder { color: #bbb; }

        .search-form button {
            background: var(--charcoal);
            color: var(--white);
            border: none;
            padding: 0 1.4rem;
            font-family: 'Inter', sans-serif;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            white-space: nowrap;
        }

        .search-form button:hover { background: #3f3e3e; }

        /* ── MAIN CONTENT ── */
        .page-wrap {
            max-width: 1100px;
            margin: 0 auto;
            padding: 2.5rem 5vw 5rem;
        }

        /* result meta */
        .result-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .result-count {
            font-size: 0.82rem;
            color: var(--muted);
        }

        .result-count strong { color: var(--charcoal); }

        .clear-search {
            font-size: 0.8rem;
            color: var(--sage);
            text-decoration: none;
            font-weight: 500;
        }

        .clear-search:hover { text-decoration: underline; }

        /* ── GRID ── */
        .listings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        /* ── CARD ── */
        .listing-card {
            background: var(--white);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.07);
            display: flex;
            flex-direction: column;
            transition: box-shadow 0.25s, transform 0.25s;
        }

        .listing-card:hover {
            box-shadow: 0 10px 36px rgba(0,0,0,0.13);
            transform: translateY(-3px);
        }

        .card-img-wrap {
            position: relative;
        }

        .card-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }

        .status-badge {
            position: absolute;
            top: 0.85rem;
            right: 0.85rem;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            padding: 0.28rem 0.65rem;
            border-radius: 20px;
        }

        .badge-available {
            background: var(--sage-light);
            color: #2d6e44;
        }

        .badge-full {
            background: #fdecea;
            color: #b91c1c;
        }

        .card-body {
            padding: 1.3rem;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .card-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 0.4rem;
            line-height: 1.3;
        }

        .card-meta {
            font-size: 0.8rem;
            color: var(--muted);
            margin-bottom: 0.2rem;
        }

        .card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: auto;
            padding-top: 1rem;
            border-top: 1px solid var(--stone);
            margin-top: 1rem;
        }

        .price-tag {
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem;
            font-weight: 700;
        }

        .price-tag small {
            font-family: 'Inter', sans-serif;
            font-size: 0.75rem;
            color: var(--muted);
            font-weight: 400;
        }

        .btn-rent {
            background: var(--charcoal);
            color: var(--white);
            text-decoration: none;
            padding: 0.5rem 1.1rem;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 500;
            transition: background 0.2s;
        }

        .btn-rent:hover { background: #3f3e3e; }

        .btn-unavailable {
            background: var(--stone);
            color: var(--muted);
            padding: 0.5rem 1.1rem;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 500;
            cursor: not-allowed;
        }

        /* ── EMPTY STATE ── */
        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 5rem 2rem;
            background: var(--white);
            border-radius: 14px;
        }

        .empty-state .icon { font-size: 3rem; margin-bottom: 1rem; }

        .empty-state h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
            margin-bottom: 0.6rem;
        }

        .empty-state p {
            color: var(--muted);
            font-size: 0.88rem;
            margin-bottom: 1.5rem;
        }

        .btn-outline {
            display: inline-block;
            padding: 0.65rem 1.5rem;
            border: 1.5px solid var(--charcoal);
            border-radius: 6px;
            color: var(--charcoal);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            transition: background 0.2s, color 0.2s;
        }

        .btn-outline:hover { background: var(--charcoal); color: var(--white); }

        /* ── FOOTER ── */
        footer {
            background: var(--charcoal);
            padding: 2rem 5vw;
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
            text-decoration: none;
        }

        .footer-brand span { color: var(--sage); }

        footer p {
            font-size: 0.75rem;
            color: #666;
        }

        .footer-links {
            display: flex;
            gap: 1.5rem;
            list-style: none;
        }

        .footer-links a {
            font-size: 0.78rem;
            color: #888;
            text-decoration: none;
            transition: color 0.2s;
        }

        .footer-links a:hover { color: var(--white); }

        @media (max-width: 700px) {
            .nav-links { display: none; }
            .catalog-hero { padding: 2.5rem 5vw 0; }
            .listings-grid { grid-template-columns: 1fr; }
            footer { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>

    <!-- NAV -->
    <nav>
        <a href="index.php" class="nav-brand">House<span>Central</span></a>
        <ul class="nav-links">
            <li><a href="home.php">Home</a></li>
            <li><a href="index.php" class="active">Catalog</a></li>
            <li><a href="manage_listings.php">Account</a></li>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <li><a href="login.php" class="nav-cta">Login</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <!-- HERO STRIP -->
    <div class="catalog-hero">
        <div class="hero-inner">
            <p class="hero-eyebrow">Browse listings</p>
            <h1 class="hero-heading">
                Find your <em>boarding house</em><br>across the Philippines
            </h1>
            <p class="hero-sub">
                <?php if ($search): ?>
                    Showing results for <strong style="color:var(--white);">"<?php echo htmlspecialchars($search); ?>"</strong>
                <?php else: ?>
                    Search by city, name, or availability. All listings are posted by verified owners.
                <?php endif; ?>
            </p>

            <form action="" method="GET" class="search-form">
                <input
                    type="text"
                    name="search"
                    value="<?php echo htmlspecialchars($search); ?>"
                    placeholder="Search by city or name…">
                <button type="submit">Search</button>
            </form>
        </div>
    </div>

    <!-- LISTINGS -->
    <div class="page-wrap">

        <div class="result-meta">
            <p class="result-count">
                <strong><?php echo count($houses); ?></strong>
                <?php echo count($houses) === 1 ? 'listing' : 'listings'; ?>
                <?php echo $search ? ' found for "' . htmlspecialchars($search) . '"' : ' available'; ?>
            </p>
            <?php if ($search): ?>
                <a href="index.php" class="clear-search">✕ Clear search</a>
            <?php endif; ?>
        </div>

        <div class="listings-grid">
            <?php if (count($houses) === 0): ?>
                <div class="empty-state">
                    <div class="icon">🔍</div>
                    <h3>No results found</h3>
                    <p>No boarding houses match "<?php echo htmlspecialchars($search); ?>". Try a different city or name.</p>
                    <a href="index.php" class="btn-outline">View all listings</a>
                </div>

            <?php else: ?>
                <?php foreach ($houses as $row): ?>
                    <div class="listing-card">
                        <div class="card-img-wrap">
                            <img
                                src="<?php echo htmlspecialchars($row['house_image']); ?>"
                                alt="<?php echo htmlspecialchars($row['house_name']); ?>"
                                class="card-img">
                            <span class="status-badge <?php echo $row['bh_status'] === 'available' ? 'badge-available' : 'badge-full'; ?>">
                                <?php echo ucfirst($row['bh_status']); ?>
                            </span>
                        </div>

                        <div class="card-body">
                            <h3 class="card-name"><?php echo htmlspecialchars($row['house_name']); ?></h3>
                            <p class="card-meta">📍 <?php echo htmlspecialchars($row['city']); ?></p>
                            <p class="card-meta">👥 Capacity: <?php echo htmlspecialchars($row['capacity']); ?> persons</p>

                            <div class="card-footer">
                                <span class="price-tag">
                                    ₱<?php echo number_format($row['price']); ?><small>/mo</small>
                                </span>

                                <?php if ($row['bh_status'] === 'available'): ?>
                                    <a href="rent.php?id=<?php echo $row['house_ID']; ?>" class="btn-rent">Rent Now</a>
                                <?php else: ?>
                                    <span class="btn-unavailable">Not Available</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- FOOTER -->
    <footer>
        <a href="index.php" class="footer-brand">House<span>Central</span></a>
        <ul class="footer-links">
            <li><a href="index.php">Catalog</a></li>
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php">Register</a></li>
        </ul>
        <p>© 2026 HouseCentral. All rights reserved.</p>
    </footer>

</body>
</html>