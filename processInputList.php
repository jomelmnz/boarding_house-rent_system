<?php
include_once 'includes/database.php';
include_once 'includes/listing.php';

$object = new Dbh();
$conn = $object->connect();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $bh_name  = $_POST['bh_name'];
    $city     = $_POST['city'];
    $capacity = $_POST['capacity'];
    $price    = $_POST['price'];

    $sql = "INSERT INTO tb_boardhouse (house_name, city, capacity, price, bh_status)
            VALUES (:house_name, :city, :capacity, :price, :bh_status)";

    $stmt = $conn->prepare($sql);

    $stmt->bindValue(':house_name', $bh_name);
    $stmt->bindValue(':city', $city);
    $stmt->bindValue(':capacity', $capacity);
    $stmt->bindValue(':price', $price);
    $stmt->bindValue(':bh_status', 'available');

    if ($stmt->execute()) {
        header("Location: index.php"); 
        exit;
    } else {
        echo "Failed to insert data.";
    }
}
?>