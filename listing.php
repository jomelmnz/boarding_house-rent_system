<?php
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

<body class="bg-light">
    <nav class="navbar customNavBar">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">Boarding House Rent System</span>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="card shadow" style="width: 500px; margin: auto;">
            <div class="card-header text-center">
                <h4>Add Boarding House</h4>
            </div>

            <div class="card-body">
                <form method="POST" action="processInputList.php">

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

                    <button type="submit" class="btn btn-submit w-100">Submit</button>

                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

</html>