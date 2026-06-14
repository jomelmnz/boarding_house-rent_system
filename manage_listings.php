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

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// ── REMOVE TENANT ──────────────────────────────────────────
if (isset($_POST['remove_tenant']) && !empty($_POST['rent_id']) && !empty($_POST['house_id'])) {
    $rentId  = (int) $_POST['rent_id'];
    $houseId = (int) $_POST['house_id'];

    try {
        $conn->beginTransaction();

        // Verify this house belongs to the logged-in user before touching anything
        $verifySql = "SELECT house_ID FROM tb_boardhouse WHERE house_ID = :house_id AND user_ID = :user_id";
        $verifyStmt = $conn->prepare($verifySql);
        $verifyStmt->bindValue(':house_id', $houseId, PDO::PARAM_INT);
        $verifyStmt->bindValue(':user_id',  $userId,  PDO::PARAM_INT);
        $verifyStmt->execute();

        if ($verifyStmt->fetch()) {
            // Delete the rent record
            $delSql = "DELETE FROM tb_rent WHERE rent_ID = :rent_id AND house_ID = :house_id";
            $delStmt = $conn->prepare($delSql);
            $delStmt->bindValue(':rent_id',  $rentId,  PDO::PARAM_INT);
            $delStmt->bindValue(':house_id', $houseId, PDO::PARAM_INT);
            $delStmt->execute();

            // Set house back to available
            $updateSql = "UPDATE tb_boardhouse SET bh_status = 'available' WHERE house_ID = :house_id";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bindValue(':house_id', $houseId, PDO::PARAM_INT);
            $updateStmt->execute();

            $conn->commit();
            header("Location: manage_listings.php?msg=tenant_removed");
            exit();
        } else {
            $conn->rollBack();
        }
    } catch (PDOException $e) {
        $conn->rollBack();
        header("Location: manage_listings.php?error=remove_failed");
        exit();
    }
}

// ── FETCH LISTINGS WITH TENANT INFO ───────────────────────
$sql = "
    SELECT 
        h.*,
        r.rent_ID,
        r.rent_date,
        u.user_ID   AS tenant_id,
        u.userFname AS tenant_fname,
        u.userLname AS tenant_lname,
        u.user_email AS tenant_email,
        u.contact_no AS tenant_contact
    FROM tb_boardhouse h
    LEFT JOIN tb_rent r ON r.house_ID = h.house_ID
    LEFT JOIN tbl_user u ON u.user_ID = r.user_ID
    WHERE h.user_ID = :user_id
    ORDER BY h.house_ID DESC
";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
$stmt->execute();
$houses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Listings — HouseCentral</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --charcoal:    #2C2B2B;
            --sage:        #7BAF8A;
            --sage-light:  #D4E8D9;
            --cream:       #F7F5F2;
            --stone:       #E8E4DF;
            --muted:       #8A7B6F;
            --white:       #FAFAFA;
            --danger:      #E57373;
            --danger-light:#FDECEA;
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

        .nav-links a:hover, .nav-links a.active { color: var(--white); }

        /* ── PAGE ── */
        .page-wrap {
            max-width: 1100px;
            margin: 0 auto;
            padding: 3rem 2vw 5rem;
        }

        /* ── HEADER BAR ── */
        .page-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            margin-bottom: 2.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .page-header-left .eyebrow {
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--sage);
            margin-bottom: 0.3rem;
        }

        .page-header-left h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .page-header-left .user-role {
            font-size: 0.78rem;
            color: var(--muted);
            margin-top: 0.3rem;
        }

        .header-actions {
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }

        .btn-add {
            background: var(--charcoal);
            color: var(--white);
            text-decoration: none;
            padding: 0.6rem 1.3rem;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
            transition: background 0.2s;
        }

        .btn-add:hover { background: #3f3e3e; }

        .btn-logout {
            background: transparent;
            color: var(--danger);
            border: 1.5px solid var(--danger);
            text-decoration: none;
            padding: 0.6rem 1.3rem;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
            transition: background 0.2s, color 0.2s;
        }

        .btn-logout:hover {
            background: var(--danger);
            color: var(--white);
        }

        /* ── FLASH MESSAGES ── */
        .flash {
            padding: 0.85rem 1.2rem;
            border-radius: 8px;
            font-size: 0.87rem;
            font-weight: 500;
            margin-bottom: 1.5rem;
        }

        .flash-success {
            background: var(--sage-light);
            color: #2d6e44;
            border-left: 3px solid var(--sage);
        }

        .flash-error {
            background: var(--danger-light);
            color: #b91c1c;
            border-left: 3px solid var(--danger);
        }

        /* ── EMPTY STATE ── */
        .empty-state {
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
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }

        /* ── LISTINGS GRID ── */
        .listings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
        }

        /* ── LISTING CARD ── */
        .listing-card {
            background: var(--white);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.07);
            display: flex;
            flex-direction: column;
            transition: box-shadow 0.2s;
        }

        .listing-card:hover {
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        }

        .card-img {
            width: 100%;
            height: 190px;
            object-fit: cover;
            display: block;
        }

        .card-body {
            padding: 1.4rem;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .card-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.5rem;
            gap: 0.5rem;
        }

        .card-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.1rem;
            font-weight: 700;
            line-height: 1.3;
        }

        .status-badge {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            padding: 0.25rem 0.65rem;
            border-radius: 20px;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .badge-available {
            background: var(--sage-light);
            color: #2d6e44;
        }

        .badge-full {
            background: var(--danger-light);
            color: #b91c1c;
        }

        .card-meta {
            font-size: 0.82rem;
            color: var(--muted);
            margin-bottom: 0.2rem;
        }

        /* ── TENANT PANEL ── */
        .tenant-panel {
            margin-top: 1rem;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid var(--stone);
        }

        .tenant-panel-header {
            background: var(--stone);
            padding: 0.5rem 0.85rem;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--muted);
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .tenant-info {
            padding: 0.9rem 0.85rem;
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }

        .tenant-name {
            font-weight: 600;
            font-size: 0.88rem;
        }

        .tenant-detail {
            font-size: 0.78rem;
            color: var(--muted);
        }

        .tenant-date {
            font-size: 0.74rem;
            color: var(--sage);
            font-weight: 500;
            margin-top: 0.15rem;
        }

        .no-tenant {
            padding: 0.85rem;
            font-size: 0.8rem;
            color: var(--muted);
            font-style: italic;
        }

        /* ── CARD FOOTER ── */
        .card-footer-row {
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

        .card-actions {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .btn-edit {
            background: transparent;
            border: 1.5px solid var(--stone);
            color: var(--charcoal);
            text-decoration: none;
            padding: 0.42rem 0.9rem;
            border-radius: 6px;
            font-size: 0.78rem;
            font-weight: 500;
            transition: border-color 0.2s, background 0.2s;
        }

        .btn-edit:hover {
            border-color: var(--charcoal);
            background: var(--stone);
        }

        .btn-delete {
            background: transparent;
            border: 1.5px solid var(--danger);
            color: var(--danger);
            text-decoration: none;
            padding: 0.42rem 0.9rem;
            border-radius: 6px;
            font-size: 0.78rem;
            font-weight: 500;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            transition: background 0.2s, color 0.2s;
        }

        .btn-delete:hover {
            background: var(--danger);
            color: var(--white);
        }

        /* remove tenant button — distinct from delete listing */
        .btn-remove-tenant {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            width: 100%;
            background: var(--danger-light);
            border: none;
            color: #b91c1c;
            padding: 0.6rem 0.85rem;
            font-family: 'Inter', sans-serif;
            font-size: 0.78rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            border-top: 1px solid #f5c6c6;
        }

        .btn-remove-tenant:hover { background: #fbd0d0; }

        /* ── MODAL OVERLAY ── */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.45);
            z-index: 200;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .modal-overlay.active { display: flex; }

        .modal-box {
            background: var(--white);
            border-radius: 14px;
            padding: 2.2rem;
            max-width: 420px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.25);
        }

        .modal-icon { font-size: 2.5rem; margin-bottom: 1rem; }

        .modal-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 0.6rem;
        }

        .modal-desc {
            font-size: 0.87rem;
            color: var(--muted);
            line-height: 1.7;
            margin-bottom: 1.6rem;
        }

        .modal-desc strong { color: var(--charcoal); }

        .modal-actions {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
        }

        .btn-modal-cancel {
            padding: 0.65rem 1.4rem;
            border-radius: 6px;
            border: 1.5px solid var(--stone);
            background: transparent;
            color: var(--charcoal);
            font-family: 'Inter', sans-serif;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-modal-cancel:hover { background: var(--stone); }

        .btn-modal-confirm {
            padding: 0.65rem 1.4rem;
            border-radius: 6px;
            border: none;
            background: var(--danger);
            color: var(--white);
            font-family: 'Inter', sans-serif;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-modal-confirm:hover { background: #d95f5f; }

        /* hidden form used by JS to submit remove-tenant */
        #removeTenantForm { display: none; }

        @media (max-width: 700px) {
            .nav-links { display: none; }
            .page-wrap { padding: 2rem 1rem 4rem; }
            .listings-grid { grid-template-columns: 1fr; }
            .page-header { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>

    <!-- NAV -->
    <nav>
        <a href="index.php" class="nav-brand">House<span>Central</span></a>
        <ul class="nav-links">
            <li><a href="home.php">Home</a></li>
            <li><a href="index.php">Catalog</a></li>
            <li><a href="manage_listings.php" class="active">Account</a></li>
        </ul>
    </nav>

    <!-- hidden form for remove-tenant POST -->
    <form id="removeTenantForm" method="POST" action="manage_listings.php">
        <input type="hidden" name="remove_tenant" value="1">
        <input type="hidden" name="rent_id"  id="form_rent_id">
        <input type="hidden" name="house_id" id="form_house_id">
    </form>

    <!-- MODAL -->
    <div class="modal-overlay" id="removeTenantModal">
        <div class="modal-box">
            <div class="modal-icon">⚠️</div>
            <h2 class="modal-title">Remove Tenant?</h2>
            <p class="modal-desc">
                You're about to remove <strong id="modal_tenant_name"></strong> from
                <strong id="modal_house_name"></strong>.<br>
                The listing will be set back to <em>Available</em>.
            </p>
            <div class="modal-actions">
                <button class="btn-modal-cancel" onclick="closeModal()">Cancel</button>
                <button class="btn-modal-confirm" onclick="submitRemove()">Yes, Remove</button>
            </div>
        </div>
    </div>

    <div class="page-wrap">

        <!-- PAGE HEADER -->
        <div class="page-header">
            <div class="page-header-left">
                <p class="eyebrow">Your account</p>
                <h1>My Listings</h1>
                <p class="user-role">
                    Signed in as <strong><?php echo htmlspecialchars($_SESSION['user_fname']); ?></strong>
                    &mdash; <?php echo $_SESSION['role'] === 'User-Owner' ? 'Owner' : 'User'; ?>
                </p>
            </div>
            <div class="header-actions">
                <a href="listing.php" class="btn-add">+ Add House</a>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>

        <!-- FLASH MESSAGES -->
        <?php if (isset($_GET['msg'])): ?>
            <?php if ($_GET['msg'] === 'tenant_removed'): ?>
                <div class="flash flash-success">✓ Tenant removed successfully. The listing is now available again.</div>
            <?php elseif ($_GET['msg'] === 'updated'): ?>
                <div class="flash flash-success">✓ Listing updated successfully.</div>
            <?php elseif ($_GET['msg'] === 'delete=success'): ?>
                <div class="flash flash-success">✓ Listing deleted.</div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="flash flash-error">You can't delete with active tenant.</div>
        <?php endif; ?>

        <!-- LISTINGS -->
        <?php if (count($houses) === 0): ?>
            <div class="empty-state">
                <div class="icon">🏠</div>
                <h3>No listings yet</h3>
                <p>You haven't added any boarding houses. Add your first one to get started.</p>
                <a href="listing.php" class="btn-add">+ Add House</a>
            </div>

        <?php else: ?>
            <div class="listings-grid">
                <?php foreach ($houses as $row): ?>
                    <div class="listing-card">
                        <img src="<?php echo htmlspecialchars($row['house_image']); ?>"
                             alt="<?php echo htmlspecialchars($row['house_name']); ?>"
                             class="card-img">

                        <div class="card-body">

                            <div class="card-top">
                                <h3 class="card-name"><?php echo htmlspecialchars($row['house_name']); ?></h3>
                                <span class="status-badge <?php echo $row['bh_status'] === 'available' ? 'badge-available' : 'badge-full'; ?>">
                                    <?php echo ucfirst($row['bh_status']); ?>
                                </span>
                            </div>

                            <p class="card-meta">📍 <?php echo htmlspecialchars($row['city']); ?></p>
                            <p class="card-meta">👥 Capacity: <?php echo htmlspecialchars($row['capacity']); ?> persons</p>

                            <!-- TENANT PANEL -->
                            <div class="tenant-panel">
                                <div class="tenant-panel-header">
                                    🧑 Current Tenant
                                </div>

                                <?php if ($row['tenant_id']): ?>
                                    <div class="tenant-info">
                                        <span class="tenant-name">
                                            <?php echo htmlspecialchars($row['tenant_fname'] . ' ' . $row['tenant_lname']); ?>
                                        </span>
                                        <span class="tenant-detail">✉ <?php echo htmlspecialchars($row['tenant_email']); ?></span>
                                        <?php if ($row['tenant_contact']): ?>
                                            <span class="tenant-detail">📞 <?php echo htmlspecialchars($row['tenant_contact']); ?></span>
                                        <?php endif; ?>
                                        <span class="tenant-date">
                                            Rented on <?php echo date('F j, Y', strtotime($row['rent_date'])); ?>
                                        </span>
                                    </div>
                                    <button
                                        class="btn-remove-tenant"
                                        onclick="openModal(
                                            <?php echo $row['rent_ID']; ?>,
                                            <?php echo $row['house_ID']; ?>,
                                            '<?php echo addslashes($row['tenant_fname'] . ' ' . $row['tenant_lname']); ?>',
                                            '<?php echo addslashes($row['house_name']); ?>'
                                        )">
                                        ✕ Remove Tenant
                                    </button>
                                <?php else: ?>
                                    <p class="no-tenant">No tenant yet.</p>
                                <?php endif; ?>
                            </div>

                            <!-- FOOTER -->
                            <div class="card-footer-row">
                                <span class="price-tag">
                                    ₱<?php echo number_format($row['price']); ?><small>/mo</small>
                                </span>
                                <div class="card-actions">
                                    <a href="edit_house.php?id=<?php echo $row['house_ID']; ?>" class="btn-edit">Edit</a>
                                    <a href="delete_house.php?id=<?php echo $row['house_ID']; ?>"
                                       class="btn-delete"
                                       onclick="return confirm('Delete this listing permanently?');">Delete</a>
                                </div>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>

    <script>
        function openModal(rentId, houseId, tenantName, houseName) {
            document.getElementById('form_rent_id').value  = rentId;
            document.getElementById('form_house_id').value = houseId;
            document.getElementById('modal_tenant_name').textContent = tenantName;
            document.getElementById('modal_house_name').textContent  = houseName;
            document.getElementById('removeTenantModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('removeTenantModal').classList.remove('active');
        }

        function submitRemove() {
            document.getElementById('removeTenantForm').submit();
        }

        // Close modal on overlay click
        document.getElementById('removeTenantModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
    </script>

</body>
</html>