<?php
require_once 'db.php';
require_once 'security.php';

class SalesManager extends DatabaseModel {

    public function recordSale($carId, $userId, $customerName, $salePrice) {
        $this->db->beginTransaction();
        try {
            $encCustomer = SecurityHelper::encrypt($customerName);
            $encPrice = SecurityHelper::encrypt($salePrice);

            // Record sale
            $stmt = $this->db->prepare("INSERT INTO sales (car_id, user_id, encrypted_customer_name, encrypted_sale_price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$carId, $userId, $encCustomer, $encPrice]);

            // Update car status to 'Sold'
            $stmtUpdate = $this->db->prepare("UPDATE cars SET status = 'Sold' WHERE car_id = ?");
            $stmtUpdate->execute([$carId]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT s.*, u.username FROM sales s JOIN users u ON s.user_id = u.user_id");
        $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($sales as &$sale) {
            $sale['customer_name'] = SecurityHelper::decrypt($sale['encrypted_customer_name']);
            $sale['sale_price'] = SecurityHelper::decrypt($sale['encrypted_sale_price']);
            
            // Pata gari husika
            $carStmt = $this->db->prepare("SELECT * FROM cars WHERE car_id = ?");
            $carStmt->execute([$sale['car_id']]);
            $car = $carStmt->fetch(PDO::FETCH_ASSOC);
            $sale['car_make'] = SecurityHelper::decrypt($car['encrypted_make']);
            $sale['car_model'] = SecurityHelper::decrypt($car['encrypted_model']);
        }
        return $sales;
    }

    // Reporting Feature
    public function generateSalesReport() {
        $sales = $this->getAll();
        $report = ['total_sales' => 0, 'total_revenue' => 0, 'data' => $sales];
        foreach ($sales as $sale) {
            $report['total_sales']++;
            $report['total_revenue'] += floatval($sale['sale_price']);
        }
        return $report;
    }
}
?>