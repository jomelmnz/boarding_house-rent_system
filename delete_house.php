<?php
session_start();
include_once 'includes/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $houseId = $_GET['id'];
    $userId = $_SESSION['user_id'];

    $object = new Dbh();
    $conn = $object->connect();

    try {
        $conn->beginTransaction();

        $imgSql = "SELECT house_image FROM tb_boardhouse WHERE house_ID = :house_id AND user_ID = :user_id";
        $imgStmt = $conn->prepare($imgSql);
        $imgStmt->bindValue(':house_id', $houseId, PDO::PARAM_INT);
        $imgStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $imgStmt->execute();
        $house = $imgStmt->fetch(PDO::FETCH_ASSOC);

        if ($house) {
            $sql = "DELETE FROM tb_boardhouse WHERE house_ID = :house_id AND user_ID = :user_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':house_id', $houseId, PDO::PARAM_INT);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            $countSql = "SELECT COUNT(*) FROM tb_boardhouse WHERE user_ID = :user_id";
            $countStmt = $conn->prepare($countSql);
            $countStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $countStmt->execute();
            $remainingHouses = $countStmt->fetchColumn();

            if ($remainingHouses == 0) {
                $updateRoleSql = "UPDATE tblink_user_role SET role_id = 1 WHERE user_ID = :user_id";
                $updateRoleStmt = $conn->prepare($updateRoleSql);
                $updateRoleStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
                $updateRoleStmt->execute();

                $_SESSION['role'] = 'User';
            }

            $conn->commit();

            if (!empty($house['house_image']) && file_exists($house['house_image'])) {
                unlink($house['house_image']);
            }

            header("Location: manage_listings.php?delete=success");
            exit();

        } else {
            $conn->rollBack();
            header("Location: manage_listings.php?error=notfound");
            exit();
        }

    } catch (PDOException $e) {
        $conn->rollBack();
        header("Location: manage_listings.php?error=queryfailed");
        exit();
    }
} else {
    header("Location: manage_listings.php");
    exit();
}