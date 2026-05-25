<?php
session_start();
include_once 'includes/database.php';

$object = new Dbh();
$conn = $object->connect();

// Handle Remember Me Cookie
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

$sql = "SELECT * FROM tb_boardhouse WHERE user_id = :user_id";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':user_id', $userId);
$stmt->execute();
$houses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Boarding House Rent</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>

<body>

    <nav class="navbar navbar-expand-lg customNavBar py-3 shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="assets/images/house_rental_logo.png" alt="HouseCentral Logo" class="navbar-logo">
            </a>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item mx-3"><a class="nav-link" href="#">Home</a></li>
                    <li class="nav-item mx-3"><a class="nav-link" href="index.php">Catalog</a></li>
                    <li class="nav-item mx-3"><a class="nav-link active" aria-current="page" href="manage_listings.php">Account</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">

        <div class="card mb-4">
            <div class="card-body d-flex justify-content-between align-items-center">
                <span>
                    Hi, <strong><?php echo htmlspecialchars($_SESSION['user_fname']); ?></strong>
                    <small class="text-muted ms-1">(<?php echo $_SESSION['role'] === 'User-Owner' ? 'User-Owner' : 'User'; ?>)</small>
                </span>
                <div>
                    <?php if ($_SESSION['role'] === 'User-Owner' || $_SESSION['role'] === 'User'): ?>
                        <a href="listing.php" class="btn btn-sm btn-dark me-2">Add House</a>
                    <?php endif; ?>
                    <a href="logout.php" class="btn btn-sm btn-danger">Logout</a>
                </div>
            </div>
        </div>

        <div class="col-12 text-center py-3">
            <h5 class="text-muted">Your Listed Properties</h5>
        </div>

        <div class="row row-cols-1 row-cols-md-3 g-4 w-100 m-0">
            <?php if (count($houses) > 0): ?>
                <?php foreach ($houses as $row): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm border-0">
                            <img src="<?php echo htmlspecialchars($row['house_image']); ?>"
                                class="card-img-top"
                                alt="<?php echo htmlspecialchars($row['house_name']); ?>"
                                style="height: 200px; object-fit: cover;">

                            <div class="card-body d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h5 class="card-title mb-0 fw-bold text-dark">
                                        <?php echo htmlspecialchars($row['house_name']); ?>
                                    </h5>
                                    <span class="badge <?php echo ($row['bh_status'] == 'available') ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo ucfirst(htmlspecialchars($row['bh_status'])); ?>
                                    </span>
                                </div>

                                <p class="card-text text-muted mb-1"><strong>City:</strong> <?php echo htmlspecialchars($row['city']); ?></p>
                                <p class="card-text text-muted mb-3"><strong>Capacity:</strong> <?php echo htmlspecialchars($row['capacity']); ?></p>

                                <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                                    <span class="fs-5 fw-bold price-tag">
                                        ₱<?php echo number_format($row['price']); ?><small class="fs-6 text-muted fw-normal">/mo</small>
                                    </span>

                                    <div>
                                        <a href="edit_house.php?id=<?php echo $row['house_ID']; ?>" class="btn btn-outline-secondary btn-sm me-1">Edit</a>
                                        <a href="delete_house.php?id=<?php echo $row['house_ID']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this listing?');">Delete</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="w-100 text-center py-5 d-block">
                    <p class="text-muted fs-5 mb-0">No listed house yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>