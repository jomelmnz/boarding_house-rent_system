<?php
session_start();
include_once 'includes/database.php';

$object = new Dbh();
$conn = $object->connect();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage_listings.php");
    exit();
}

$houseId = $_GET['id'];

$sql = "SELECT * FROM tb_boardhouse WHERE house_ID = :house_id AND user_id = :user_id";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':house_id', $houseId);
$stmt->bindValue(':user_id', $userId);
$stmt->execute();
$house = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$house) {
    header("Location: manage_listings.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bh_name  = $_POST['bh_name'];
    $city     = $_POST['city'];
    $capacity = $_POST['capacity'];
    $price    = $_POST['price'];
    $status   = $_POST['bh_status'];
    $targetFilePath = $house['house_image'];

    // Optional: Handle new image upload if provided
    if (isset($_FILES['img']) && $_FILES['img']['error'] == 0) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

        $fileName = time() . "_" . basename($_FILES["img"]["name"]);
        $newFilePath = $targetDir . $fileName;

        if (move_uploaded_file($_FILES["img"]["tmp_name"], $newFilePath)) {
            $targetFilePath = $newFilePath;
        }
    }

    $updateSql = "UPDATE tb_boardhouse 
                  SET house_name = :name, city = :city, capacity = :capacity, price = :price, bh_status = :status, house_image = :image 
                  WHERE house_ID = :house_id AND user_id = :user_id";

    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bindValue(':name', $bh_name);
    $updateStmt->bindValue(':city', $city);
    $updateStmt->bindValue(':capacity', $capacity);
    $updateStmt->bindValue(':price', $price);
    $updateStmt->bindValue(':status', $status);
    $updateStmt->bindValue(':image', $targetFilePath);
    $updateStmt->bindValue(':house_id', $houseId);
    $updateStmt->bindValue(':user_id', $userId);

    if ($updateStmt->execute()) {
        header("Location: manage_listings.php?msg=updated");
        exit();
    } else {
        $error = "Failed to update property details.";
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Boarding House</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>

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

    body{
        padding: 50px;
    }


    html { scroll-behavior: smooth; }
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

</style>


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

<body class="bg-light">

    <div class="container mt-5">
        <div class="card shadow" style="width: 500px; margin: auto;">
            <div class="card-header text-center">
                <h4>Edit Boarding House</h4>
            </div>

            <div class="card-body">
                <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Boarding House Name</label>
                        <input type="text" class="form-control" name="bh_name" value="<?php echo htmlspecialchars($house['house_name']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">City (Location)</label>
                        <input type="text" class="form-control" name="city" value="<?php echo htmlspecialchars($house['city']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Capacity</label>
                        <input type="number" class="form-control" name="capacity" min="1" max="100" value="<?php echo htmlspecialchars($house['capacity']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Price</label>
                        <input type="number" class="form-control" name="price" min="1" value="<?php echo htmlspecialchars($house['price']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="bh_status">
                            <option value="available" <?php echo $house['bh_status'] == 'available' ? 'selected' : ''; ?>>Available</option>
                            <option value="full" <?php echo $house['bh_status'] != 'available' ? 'selected' : ''; ?>>Full</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Update Image (Leave blank to keep current)</label>
                        <input type="file" name="img" class="form-control">
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="manage_listings.php" class="btn btn-secondary w-45">Cancel</a>
                        <button type="submit" class="btn btn-submit w-50">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
</body>



</html>