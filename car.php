<?php
require_once 'db.php';
require_once 'security.php';

interface Searchable {
    public function search($term);
}

class CarManager extends DatabaseModel implements Searchable {

    public function create($make, $model, $price) {
        $encMake = SecurityHelper::encrypt($make);
        $encModel = SecurityHelper::encrypt($model);
        $encPrice = SecurityHelper::encrypt($price);

        $stmt = $this->db->prepare("INSERT INTO cars (encrypted_make, encrypted_model, encrypted_price) VALUES (?, ?, ?)");
        return $stmt->execute([$encMake, $encModel, $encPrice]);
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM cars");
        $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Deserialization na Decryption kwa ajili ya kuonyesha taarifa
        foreach ($cars as &$car) {
            $car['make'] = SecurityHelper::decrypt($car['encrypted_make']);
            $car['model'] = SecurityHelper::decrypt($car['encrypted_model']);
            $car['price'] = SecurityHelper::decrypt($car['encrypted_price']);
        }
        return $cars;
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM cars WHERE car_id = ?");
        $stmt->execute([$id]);
        $car = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($car) {
            $car['make'] = SecurityHelper::decrypt($car['encrypted_make']);
            $car['model'] = SecurityHelper::decrypt($car['encrypted_model']);
            $car['price'] = SecurityHelper::decrypt($car['encrypted_price']);
        }
        return $car;
    }

    public function update($id, $make, $model, $price, $status) {
        $encMake = SecurityHelper::encrypt($make);
        $encModel = SecurityHelper::encrypt($model);
        $encPrice = SecurityHelper::encrypt($price);

        $stmt = $this->db->prepare("UPDATE cars SET encrypted_make = ?, encrypted_model = ?, encrypted_price = ?, status = ? WHERE car_id = ?");
        return $stmt->execute([$encMake, $encModel, $encPrice, $status, $id]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM cars WHERE car_id = ?");
        return $stmt->execute([$id]);
    }

    // Search functionality (Polymorphism & Interface Implementation)
    public function search($term) {
        $allCars = $this->getAll();
        $results = [];
        foreach ($allCars as $car) {
            if (stripos($car['make'], $term) !== false || stripos($car['model'], $term) !== false) {
                $results[] = $car;
            }
        }
        return $results;
    }
}
?>