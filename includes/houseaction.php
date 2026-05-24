<?php
include_once 'database.php'; 

class HouseAction extends Dbh {
    
    public function saveHouse($house_name, $city, $capacity, $price, $user_ID, $house_image) {
  
        $conn = $this->connect();
        
        $sql = "INSERT INTO tb_boardhouse (house_name, city, capacity, price, bh_status, user_ID, house_image)
                VALUES (:house_name, :city, :capacity, :price, :bh_status, :user_id, :img)";

        
        $stmt = $conn->prepare($sql);
        return $stmt->execute([

        ':house_name' => $house_name,
        ':city' => $city,
        ':capacity' => $capacity, 
        ':price' => $price,
        ':bh_status' => 'available',
        ':user_id' => $user_ID,
        ':img' => $house_image
        
        ]);
    }
}