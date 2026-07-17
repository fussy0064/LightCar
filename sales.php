<?php
require_once 'auth.php';
require_once 'car.php';
require_once 'sale.php';
$auth = new Auth();
if (!$auth->isLoggedIn()) { header("Location: index.php"); exit(); }

$carManager = new CarManager();
$salesManager = new SalesManager();
$error = ""; $success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['sell_car']) && !SecurityHelper::csrfCheck($_POST['csrf_token'] ?? '')) {
    $error = "Invalid session token, please try again.";
} elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['sell_car'])) {
    $carId = SecurityHelper::sanitize($_POST['car_id']);
    $customerName = SecurityHelper::sanitize($_POST['customer_name']);
    $salePrice = SecurityHelper::sanitize($_POST['sale_price']);

    if (empty($carId) || empty($customerName) || empty($salePrice)) {
        $error = "All fields are required.";
    } elseif (!is_numeric($salePrice) || $salePrice <= 0) {
        $error = "Sale price must be a positive number.";
    } else {
        if ($salesManager->recordSale($carId, $_SESSION['user_id'], $customerName, $salePrice)) {
            $success = "Sale recorded successfully!";
        } else {
            $error = "Sale processing failed.";
        }
    }
}

$availableCars = array_filter($carManager->getAll(), function($car) {
    return $car['status'] === 'Available';
});

$report = $salesManager->generateSalesReport();
$csrfToken = SecurityHelper::csrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales & Reports</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background-color: #f4f6f9; }
        .navbar { background: #343a40; padding: 15px; color: white; display: flex; justify-content: space-between; }
        .navbar a { color: white; text-decoration: none; margin-left: 15px; }
        .container { padding: 30px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        .btn { padding: 8px 15px; background: #28a745; border: none; color: white; cursor: pointer; border-radius: 4px; }
        input, select { padding: 8px; width: 95%; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="navbar">
        <span>Car Sales System</span>
        <div>
            <a href="dashboard.php">Dashboard</a>
            <a href="cars.php">Manage Cars</a>
            <a href="sales.php">Sales & Reports</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>
    <div class="container">
        <h2>Sales & Business Reports</h2>
        
        <?php if($error): ?><p style="color:red;"><?= $error ?></p><?php endif; ?>
        <?php if($success): ?><p style="color:green;"><?= $success ?></p><?php endif; ?>

        <div class="grid">
            <div class="card">
                <h3>Record New Sale</h3>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <label>Select Car</label>
                    <select name="car_id" required>
                        <option value="">-- Choose Car --</option>
                        <?php foreach($availableCars as $car): ?>
                            <option value="<?= $car['car_id'] ?>"><?= $car['make'] . ' ' . $car['model'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label>Customer Name</label>
                    <input type="text" name="customer_name" required>
                    
                    <label>Agreed Selling Price</label>
                    <input type="text" name="sale_price" required>
                    
                    <button class="btn" type="submit" name="sell_car">Record Sale</button>
                </form>
            </div>

            <div class="card">
                <h3>Financial Report Overview</h3>
                <p><b>Total Number of Sales:</b> <?= $report['total_sales'] ?></p>
                <p><b>Total Revenue Generated:</b> Tsh <?= number_format($report['total_revenue']) ?></p>
            </div>
        </div>

        <div class="card" style="margin-top: 20px;">
            <h3>Decrypted Sales Log</h3>
            <table>
                <thead>
                    <tr>
                        <th>Sale ID</th>
                        <th>Vehicle Sold</th>
                        <th>Sold By (User)</th>
                        <th>Customer Name</th>
                        <th>Revenue</th>
                        <th>Date Processed</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($report['data'] as $sale): ?>
                    <tr>
                        <td><?= $sale['sale_id'] ?></td>
                        <td><?= htmlspecialchars($sale['car_make'] . ' ' . $sale['car_model']) ?></td>
                        <td><?= htmlspecialchars($sale['username']) ?></td>
                        <td><?= htmlspecialchars($sale['customer_name']) ?></td>
                        <td>Tsh <?= number_format(htmlspecialchars($sale['sale_price'])) ?></td>
                        <td><?= $sale['sale_date'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>