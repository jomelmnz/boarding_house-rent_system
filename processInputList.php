<?php
include_once 'includes/database.php';
include_once 'includes/listing.php';

$object = new Dbh();
$conn = $object->connect();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['img'])) {

    $bh_name  = $_POST['bh_name'];
    $city     = $_POST['city'];
    $capacity = $_POST['capacity'];
    $price    = $_POST['price'];

    $targetDir = "uploads/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

    $fileName = time() . "_" . basename($_FILES["img"]["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

    if (move_uploaded_file($_FILES["img"]["tmp_name"], $targetFilePath)) {

        resizeImage($targetFilePath, $targetFilePath, 500);

        $sql = "INSERT INTO tb_boardhouse (house_name, city, capacity, price, bh_status, house_image)
                VALUES (:house_name, :city, :capacity, :price, :bh_status, :img)";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':house_name', $bh_name);
        $stmt->bindValue(':city', $city);
        $stmt->bindValue(':capacity', $capacity);
        $stmt->bindValue(':price', $price);
        $stmt->bindValue(':bh_status', 'available');
        $stmt->bindValue(':img', $targetFilePath); // Storing the path

        if ($stmt->execute()) {
            header("Location: index.php?success=1");
            exit;
        }
    } else {
        echo "Error uploading file.";
    }
}

function resizeImage($sourcePath, $destPath, $maxWidth)
{
    list($width, $height, $type) = getimagesize($sourcePath);

    $ratio = $width / $height;
    $newWidth = $maxWidth;
    $newHeight = $maxWidth / $ratio;

    switch ($type) {
        case IMAGETYPE_JPEG:
            $src = imagecreatefromjpeg($sourcePath);
            break;
        case IMAGETYPE_PNG:
            $src = imagecreatefrompng($sourcePath);
            break;
        case IMAGETYPE_GIF:
            $src = imagecreatefromgif($sourcePath);
            break;
        default:
            return false;
    }

    $tmp = imagecreatetruecolor($newWidth, $newHeight);

    if ($type == IMAGETYPE_PNG) {
        imagealphablending($tmp, false);
        imagesavealpha($tmp, true);
    }

    imagecopyresampled($tmp, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    switch ($type) {
        case IMAGETYPE_JPEG:
            imagejpeg($tmp, $destPath, 85);
            break;
        case IMAGETYPE_PNG:
            imagepng($tmp, $destPath);
            break;
        case IMAGETYPE_GIF:
            imagegif($tmp, $destPath);
            break;
    }

    imagedestroy($src);
    imagedestroy($tmp);
}
