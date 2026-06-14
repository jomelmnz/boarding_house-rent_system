<?php
session_start();
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'User' && $_SESSION['role'] !== 'User-Owner')) {
    header("Location: login.php");
    exit();
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Boarding House Rent</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
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
                <h4>Add Boarding House</h4>
            </div>

            <div class="card-body">
                <form method="POST" action="processInputList.php" enctype="multipart/form-data">

                    <div class="mb-3">
                        <label class="form-label">Boarding House Name</label>
                        <input type="text" class="form-control" name="bh_name" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">City (Location)</label>
                        <input type="text" class="form-control" name="city" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Capacity</label>
                        <input type="number" class="form-control" name="capacity" min="1" max="100" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Price</label>
                        <input type="number" class="form-control" name="price" min="1" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Upload Image</label>
                        <input type="file" name="img" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-submit w-100">Submit</button>

                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

</html>