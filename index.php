<?php
include_once 'includes/database.php';

$object = new Dbh();
$conn = $object->connect();

$search = isset($_GET['search']) ? $_GET['search'] : '';

if ($search != "") {
  $sql = "SELECT * FROM tb_boardhouse 
            WHERE house_name LIKE :search
            OR city LIKE :search
            OR bh_status LIKE :search
            OR capacity LIKE :search";

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

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link href="styles.css" rel="stylesheet">
</head>

<body>
  <nav class="navbar customNavBar">
    <div class="container-fluid">
      <span class="navbar-brand mb-0 h1">Boarding House Rent System</span>
    </div>
  </nav>

  <form action="" method="GET">
    <div class="searchBar">
      <div class="input-group mb-3">
        <input type="text" name="search" value="<?php if (isset($_GET['search'])) {
                                                  echo $_GET['search'];
                                                } ?>" class="form-control" placeholder="Search...">
        <button type="submit" class="btn btn-search">Search</button>
      </div>
    </div>
  </form>

  <table class="table" style="width: 900px; margin: 50px auto 0;">
    <thead>
      <tr>
        <th scope="col">Boarding House Name</th>
        <th scope="col">City</th>
        <th scope="col">Status</th>
        <th scope="col">Capacity</th>
        <th scope="col">Price</th>
      </tr>
    </thead>
    <tbody>
      <?php if (count($houses) == 0) { ?>
        <tr>
          <td colspan="5" class="text-center">No results found</td>
        </tr>
      <?php } ?>


      <?php foreach ($houses as $row) { ?>
        <tr>
          <td><?php echo $row['house_name']; ?></td>
          <td><?php echo $row['city']; ?></td>
          <td><?php echo $row['bh_status']; ?></td>
          <td><?php echo $row['capacity']; ?></td>
          <td><?php echo "₱".$row['price']; ?></td>
          <td>
            <?php if ($row['bh_status'] == "available") { ?>
              <button class="btn btn-rent btn-sm">Rent</button>
            <?php } else { ?>
              <button class="btn btn-secondary btn-sm" disabled>Not Available</button>
            <?php } ?>
          </td>
        </tr>
      <?php } ?>
    </tbody>
  </table>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

</html>