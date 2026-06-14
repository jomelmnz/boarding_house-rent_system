<?php
session_start();
include_once 'includes/database.php';

$object = new Dbh();
$conn = $object->connect();

// Restore session from remember me cookie
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

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Must have a house ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$houseId = (int) $_GET['id'];
$userId  = $_SESSION['user_id'];

// Fetch the house
$sql = "SELECT * FROM tb_boardhouse WHERE house_ID = :house_id";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':house_id', $houseId, PDO::PARAM_INT);
$stmt->execute();
$house = $stmt->fetch(PDO::FETCH_ASSOC);

// House doesn't exist
if (!$house) {
    header("Location: index.php");
    exit();
}

// House is not available
if ($house['bh_status'] !== 'available') {
    header("Location: index.php?error=unavailable");
    exit();
}

// Block owner from renting their own listing
$isOwner = ($house['user_ID'] == $userId);

// Check if user already has an active rent record for this house
$alreadyRentedError = false;
if (!$isOwner) {
    $checkSql = "SELECT COUNT(*) FROM tb_rent WHERE user_ID = :user_id AND house_ID = :house_id";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bindValue(':user_id',  $userId,  PDO::PARAM_INT);
    $checkStmt->bindValue(':house_id', $houseId, PDO::PARAM_INT);
    $checkStmt->execute();
    $alreadyRentedError = $checkStmt->fetchColumn() > 0;
}

// Handle form submission
$successMsg = '';
$errorMsg   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$isOwner && !$alreadyRentedError) {
    try {
        $conn->beginTransaction();

        $insertSql = "INSERT INTO tb_rent (rent_date, user_ID, house_ID) 
                      VALUES (CURDATE(), :user_id, :house_id)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bindValue(':user_id',  $userId,  PDO::PARAM_INT);
        $insertStmt->bindValue(':house_id', $houseId, PDO::PARAM_INT);
        $insertStmt->execute();

        $updateSql = "UPDATE tb_boardhouse SET bh_status = 'full' WHERE house_ID = :house_id";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bindValue(':house_id', $houseId, PDO::PARAM_INT);
        $updateStmt->execute();

        $conn->commit();
        $house['bh_status'] = 'full';
        $successMsg = htmlspecialchars($house['house_name']);

    } catch (PDOException $e) {
        $conn->rollBack();
        $errorMsg = "Something went wrong. Please try again.";
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rent — <?php echo htmlspecialchars($house['house_name']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --charcoal: #2C2B2B;
            --sage:     #7BAF8A;
            --sage-light: #D4E8D9;
            --cream:    #F7F5F2;
            --stone:    #E8E4DF;
            --muted:    #8A7B6F;
            --white:    #FAFAFA;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--cream);
            color: var(--charcoal);
            min-height: 100vh;
        }

        /* NAV */
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
        }

        .nav-links a {
            color: #ccc;
            text-decoration: none;
            font-size: 0.85rem;
            transition: color 0.2s;
        }

        .nav-links a:hover { color: var(--white); }

        /* PAGE */
        .page-wrap {
            max-width: 560px;
            margin: 0 auto;
            padding: 4rem 1.5rem 5rem;
        }

        /* CARD */
        .rent-card {
            background: var(--white);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 8px 40px rgba(0,0,0,0.09);
        }

        .house-img {
            width: 100%;
            height: 240px;
            object-fit: cover;
            display: block;
        }

        .card-body {
            padding: 2rem;
        }

        /* eyebrow */
        .eyebrow {
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--sage);
            margin-bottom: 0.5rem;
        }

        .card-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.55rem;
            font-weight: 700;
            margin-bottom: 1.6rem;
            line-height: 1.2;
        }

        /* detail rows */
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.65rem 0;
            border-bottom: 1px solid var(--stone);
            font-size: 0.88rem;
        }

        .detail-row:last-child { border-bottom: none; }
        .detail-label { color: var(--muted); }
        .detail-value { font-weight: 600; }

        .badge-available {
            background: var(--sage-light);
            color: #3a7a50;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            padding: 0.25rem 0.65rem;
            border-radius: 20px;
        }

        /* price bar */
        .price-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--cream);
            border-radius: 8px;
            padding: 1rem 1.2rem;
            margin: 1.6rem 0;
        }

        .price-label {
            font-size: 0.8rem;
            color: var(--muted);
        }

        .price-value {
            font-family: 'Playfair Display', serif;
            font-size: 1.7rem;
            font-weight: 700;
        }

        .price-value small {
            font-family: 'Inter', sans-serif;
            font-size: 0.8rem;
            color: var(--muted);
            font-weight: 400;
        }

        /* buttons */
        .btn-confirm {
            width: 100%;
            background: var(--charcoal);
            color: var(--white);
            border: none;
            padding: 0.85rem;
            border-radius: 7px;
            font-family: 'Inter', sans-serif;
            font-size: 0.92rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
            letter-spacing: 0.02em;
        }

        .btn-confirm:hover { background: #3f3e3e; }

        .btn-back {
            display: block;
            text-align: center;
            margin-top: 1rem;
            font-size: 0.82rem;
            color: var(--muted);
            text-decoration: none;
            transition: color 0.2s;
        }

        .btn-back:hover { color: var(--charcoal); }

        /* state cards: success / blocked / already rented */
        .state-card {
            text-align: center;
            padding: 3.5rem 2rem;
        }

        .state-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
        }

        .state-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.7rem;
        }

        .state-desc {
            font-size: 0.9rem;
            color: var(--muted);
            line-height: 1.75;
            margin-bottom: 2rem;
            max-width: 340px;
            margin-left: auto;
            margin-right: auto;
        }

        .btn-outline {
            display: inline-block;
            padding: 0.7rem 1.8rem;
            border: 1.5px solid var(--charcoal);
            border-radius: 7px;
            color: var(--charcoal);
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 500;
            transition: background 0.2s, color 0.2s;
        }

        .btn-outline:hover {
            background: var(--charcoal);
            color: var(--white);
        }

        .error-alert {
            background: #fef2f2;
            border-left: 3px solid #e57373;
            color: #b91c1c;
            padding: 0.75rem 1rem;
            border-radius: 6px;
            font-size: 0.85rem;
            margin-bottom: 1.2rem;
        }

        /* owner block banner */
        .owner-banner {
            background: #fff8e1;
            border-left: 3px solid #f6c90e;
            color: #7a5c00;
            padding: 0.75rem 1rem;
            border-radius: 6px;
            font-size: 0.85rem;
            margin-bottom: 1.2rem;
        }

        @media (max-width: 600px) {
            .nav-links { display: none; }
            .page-wrap { padding: 2rem 1rem 4rem; }
        }
    </style>
</head>
<body>

    <nav>
        <a href="index.php" class="nav-brand">House<span>Central</span></a>
        <ul class="nav-links">
            <li><a href="#">Home</a></li>
            <li><a href="index.php">Catalog</a></li>
            <li><a href="manage_listings.php">Account</a></li>
        </ul>
    </nav>

    <div class="page-wrap">
        <div class="rent-card">

            <?php if ($successMsg): ?>
            <!-- ── SUCCESS ── -->
            <div class="state-card">
                <span class="state-icon">🎉</span>
                <h2 class="state-title">Rental Confirmed!</h2>
                <p class="state-desc">
                    You've successfully rented <strong><?php echo $successMsg; ?></strong>.<br>
                    The owner will reach out to you shortly.
                </p>
                <a href="index.php" class="btn-outline">Back to Catalog</a>
            </div>

            <?php elseif ($isOwner): ?>
            <!-- ── OWNER BLOCKED ── -->
            <img src="<?php echo htmlspecialchars($house['house_image']); ?>"
                 alt="<?php echo htmlspecialchars($house['house_name']); ?>"
                 class="house-img">
            <div class="card-body">
                <div class="state-card" style="padding: 1.5rem 0 0.5rem;">
                    <span class="state-icon">🏠</span>
                    <h2 class="state-title">This is your listing</h2>
                    <p class="state-desc">
                        You can't rent a boarding house you own. Head to your account to manage this property instead.
                    </p>
                    <div style="display:flex; gap:0.75rem; justify-content:center; flex-wrap:wrap;">
                        <a href="manage_listings.php" class="btn-outline">Manage Listings</a>
                        <a href="index.php" class="btn-outline">Back to Catalog</a>
                    </div>
                </div>
            </div>

            <?php elseif ($alreadyRentedError): ?>
            <!-- ── ALREADY RENTED ── -->
            <img src="<?php echo htmlspecialchars($house['house_image']); ?>"
                 alt="<?php echo htmlspecialchars($house['house_name']); ?>"
                 class="house-img">
            <div class="card-body">
                <div class="state-card" style="padding: 1.5rem 0 0.5rem;">
                    <span class="state-icon">⚠️</span>
                    <h2 class="state-title">Already Rented</h2>
                    <p class="state-desc">
                        You already have an active rental for <strong><?php echo htmlspecialchars($house['house_name']); ?></strong>.
                    </p>
                    <a href="index.php" class="btn-outline">Back to Catalog</a>
                </div>
            </div>

            <?php else: ?>
            <!-- ── CONFIRMATION FORM ── -->
            <img src="<?php echo htmlspecialchars($house['house_image']); ?>"
                 alt="<?php echo htmlspecialchars($house['house_name']); ?>"
                 class="house-img">

            <div class="card-body">
                <p class="eyebrow">Confirm your rental</p>
                <h2 class="card-title"><?php echo htmlspecialchars($house['house_name']); ?></h2>

                <?php if ($errorMsg): ?>
                    <div class="error-alert"><?php echo $errorMsg; ?></div>
                <?php endif; ?>

                <div>
                    <div class="detail-row">
                        <span class="detail-label">Location</span>
                        <span class="detail-value"><?php echo htmlspecialchars($house['city']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Capacity</span>
                        <span class="detail-value"><?php echo htmlspecialchars($house['capacity']); ?> persons</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Status</span>
                        <span class="detail-value"><span class="badge-available">Available</span></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Rent Date</span>
                        <span class="detail-value"><?php echo date('F j, Y'); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Renter</span>
                        <span class="detail-value"><?php echo htmlspecialchars($_SESSION['user_fname']); ?></span>
                    </div>
                </div>

                <div class="price-bar">
                    <span class="price-label">Monthly rate</span>
                    <span class="price-value">₱<?php echo number_format($house['price']); ?><small>/mo</small></span>
                </div>

                <form method="POST">
                    <button type="submit" class="btn-confirm">Confirm Rental</button>
                </form>

                <a href="index.php" class="btn-back">Cancel and go back</a>
            </div>

            <?php endif; ?>

        </div>
    </div>

</body>
</html>