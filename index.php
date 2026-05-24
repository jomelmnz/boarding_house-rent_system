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
    <title>Boarding House Rent</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>

<body>

    <nav class="navbar navbar-expand-lg customNavBar py-3 shadow-sm">
        <div class="container ps-0">
            <a class="navbar-brand" href="index.php">
                <img src="assets/images/house_rental_logo.png" alt="HouseCentral Logo" class="navbar-logo">
            </a>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item mx-3">
                        <a class="nav-link active" aria-current="page" href="index.php">Home</a>
                    </li>
                    <li class="nav-item mx-3">
                        <a class="nav-link" href="#">Catalog</a>
                    </li>
                    <li class="nav-item mx-3">
                        <a class="nav-link" href="#">Account</a>
                    </li>
                </ul>
            </div>


            <?php /* ?>
                <div class="ms-auto d-flex align-items-center">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <span class="me-3">Hi, <strong><?php echo htmlspecialchars($_SESSION['user_fname']); ?></strong></span>
                        <?php if ($_SESSION['role'] === 'User-Owner'): ?>
                            <a href="listing.php" class="btn btn-sm btn-dark me-2">Add House</a>
                        <?php endif; ?>
                        <a href="logout.php" class="btn btn-sm btn-danger">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-sm btn-secondary me-2">Login</a>
                        <a href="register.php" class="btn btn-sm btn-outline-primary">Register</a>
                    <?php endif; ?>
                </div> 
            <?php */ ?>

        </div>
    </nav>

    <div class="container mt-4">

        <form action="" method="GET" class="searchBar">
            <div class="input-group">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                    class="form-control" placeholder="Search by city or name...">
                <button type="submit" class="btn btn-search">Search</button>
            </div>
        </form>

        <div class="row mt-5">
            <?php if (count($houses) == 0): ?>

                <div class="col-12 text-center py-5">
                    <h5 class="text-muted">No boarding houses found matching your criteria.</h5>
                </div>

            <?php else: ?>

                <div class="row row-cols-1 row-cols-md-3 g-4 w-100 m-0">
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

                                    <p class="card-text text-muted mb-1">
                                        <strong>City:</strong> <?php echo htmlspecialchars($row['city']); ?>
                                    </p>
                                    <p class="card-text text-muted mb-3">
                                        <strong>Capacity:</strong> <?php echo htmlspecialchars($row['capacity']); ?>
                                    </p>

                                    <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                                        <span class="fs-5 fw-bold price-tag">
                                            ₱<?php echo number_format($row['price']); ?><small class="fs-6 text-muted fw-normal">/mo</small>
                                        </span>

                                        <?php if ($row['bh_status'] == "available"): ?>
                                            <a href="index.php?id=<?php echo $row['house_ID']; ?>" class="btn btn-card-rent btn-sm px-3">Rent</a>
                                        <?php else: ?>
                                            <button class="btn btn-secondary btn-sm px-3" disabled>Not Available</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>