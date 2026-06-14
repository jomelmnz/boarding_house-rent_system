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
        </ul>
    </nav>

<body class="bg-light">

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
                    
                    <button type="submit" class="btn btn-success w-100 mt-2">Login</button>

                    <div class="text-center mt-3">
                        <p class="mb-0 text-muted">Don't have an account? <a href="register.php" class="fw-bold text-decoration-none" style="color: #3E3D3D;">Register</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>