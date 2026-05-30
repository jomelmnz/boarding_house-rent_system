<?php
session_start();
include_once 'includes/database.php';
$object = new Dbh();
$conn = $object->connect();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $pass  = $_POST['password'];

    $sql = "SELECT u.*, r.user_role 
            FROM tbl_user u
            JOIN tblink_user_role link ON u.user_ID = link.user_ID
            JOIN tb_role r ON link.role_id = r.role_id
            WHERE u.user_email = ?";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $pass === $user['password']) {

        setcookie("remember_user", $user['user_ID'], time() + 30, "/");

        $_SESSION['user_id'] = $user['user_ID'];
        $_SESSION['user_fname'] = $user['userFname'];
        $_SESSION['role'] = $user['user_role'];

        header("Location: index.php");
        exit();
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - House Central</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>

<body class="bg-light">

    <nav class="navbar navbar-expand-lg customNavBar py-3 shadow-sm">
        <div class="container ps-0">
            <a class="navbar-brand" href="index.php">
                <img src="assets/images/house_rental_logo.png" alt="HouseCentral Logo" class="navbar-logo">
            </a>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item mx-3">
                        <a class="nav-link" href="#">Home</a>
                    </li>
                    <li class="nav-item mx-3">
                        <a class="nav-link" aria-current="page" href="index.php">Catalog</a>
                    </li>
                    <li class="nav-item mx-3">
                        <a class="nav-link active" href="manage_listings.php">Account</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5" style="max-width: 400px;">
        <div class="card shadow">
            <div class="card-body">
                <h4 class="text-center">Login</h4>

                <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

                <form method="POST">
                    <div class="mb-3">
                        <input type="email" name="email" class="form-control" placeholder="Email" required>
                    </div>
                    <div class="mb-3">
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                    </div>
                    <div class="text-center mt-3">
                        <p class="mb-2 text-muted">Don't have an account? <a href="register.php" class="fw-bold text-decoration-none" style="color: #3E3D3D;">Register</a></p>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Login</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>