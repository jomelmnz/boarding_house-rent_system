<?php
include_once 'includes/database.php';
$object = new Dbh();
$conn = $object->connect();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fname   = $_POST['fname'];
    $lname   = $_POST['lname'];
    $email   = $_POST['email'];
    $contact = $_POST['contact'];
    do {
        
        $contact = preg_replace('/[^0-9]/', '', $contact);
        if (strpos($contact, '63') === 0) {
            $contact = substr($contact, 2);
        }
        if (strpos($contact, '0') === 0) {
            $contact = substr($contact, 1);
        }
    } while (strlen($contact) > 10);

    $pass    = $_POST['password'];

    try {
        $conn->beginTransaction();

        $sql = "INSERT INTO tbl_user (userFname, userLname, user_email, contact_no, password) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$fname, $lname, $email, $contact, $pass]);
        
        $lastId = $conn->lastInsertId();


        $sqlRole = "INSERT INTO tblink_user_role (user_ID, role_id) VALUES (?, 1)";
        $stmtRole = $conn->prepare($sqlRole);
        $stmtRole->execute([$lastId]);

        $conn->commit();
        header("Location: login.php?msg=success");
    } catch (Exception $e) {
        $conn->rollBack();
        echo "Registration failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - HouseRent</title>
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
    <div class="container mt-5" style="max-width: 450px;">
        <div class="card shadow">
            <div class="card-header customNavBar text-center text-light">
                <h4>Owner Registration</h4>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label>First Name</label>
                        <input type="text" name="fname" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Last Name</label>
                        <input type="text" name="lname" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Contact Number</label>
                        <input type="text" name="contact" class="form-control" placeholder="09123456789" required>
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-submit w-100">Create Account</button>
                </form>
                <div class="text-center mt-3">
                    <a href="login.php">Already have an account? Login here</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>