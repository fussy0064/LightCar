<?php
require_once 'auth.php';
require_once 'car.php';
$auth = new Auth();
if (!$auth->isLoggedIn()) { header("Location: index.php"); exit(); }

$carManager = new CarManager();
$error = ""; $success = "";

if (isset($_GET['delete'])) {
    $carManager->delete($_GET['delete']);
    $success = "Car removed successfully!";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_car'])) {
    $make = SecurityHelper::sanitize($_POST['make']);
    $model = SecurityHelper::sanitize($_POST['model']);
    $price = SecurityHelper::sanitize($_POST['price']);

    if (empty($make) || empty($model) || empty($price)) {
        $error = "All fields are required.";
    } elseif (!is_numeric($price) || $price <= 0) {
        $error = "Price must be a positive number.";
    } else {
        $carManager->create($make, $model, $price);
        $success = "New car listed successfully!";
    }
}

$cars = isset($_GET['search']) && !empty($_GET['search']) ? $carManager->search($_GET['search']) : $carManager->getAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Cars</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background-color: #f4f6f9; }
        .navbar { background: #343a40; padding: 15px; color: white; display: flex; justify-content: space-between; }
        .navbar a { color: white; text-decoration: none; margin-left: 15px; }
        .container { padding: 30px; }
        .form-section, .table-section { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        .btn { padding: 6px 12px; background: #007bff; border: none; color: white; cursor: pointer; border-radius: 4px; text-decoration: none; }
        .btn-danger { background: #dc3545; }
        input[type="text"] { padding: 8px; width: 200px; }
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
        <h2>Car Inventory Management</h2>
        
        <?php if($error): ?><p style="color:red;"><?= $error ?></p><?php endif; ?>
        <?php if($success): ?><p style="color:green;"><?= $success ?></p><?php endif; ?>

        <div class="form-section">
            <h3>Add New Car</h3>
            <form method="POST">
                <input type="text" name="make" placeholder="Car Brand (e.g. Toyota)" required>
                <input type="text" name="model" placeholder="Model (e.g. RAV4)" required>
                <input type="text" name="price" placeholder="Price" required>
                <button class="btn" type="submit" name="add_car">Save Car</button>
            </form>
        </div>

        <div class="table-section">
            <h3>Car Inventory List</h3>
            <form method="GET">
                <input type="text" name="search" placeholder="Search by brand or model..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                <button class="btn" type="submit">Search</button>
            </form>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Brand (Decrypted)</th>
                        <th>Model (Decrypted)</th>
                        <th>Price (Decrypted)</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($cars as $car): ?>
                    <tr>
                        <td><?= $car['car_id'] ?></td>
                        <td><?= htmlspecialchars($car['make']) ?></td>
                        <td><?= htmlspecialchars($car['model']) ?></td>
                        <td>Tsh <?= number_format(htmlspecialchars($car['price'])) ?></td>
                        <td><b><?= $car['status'] ?></b></td>
                        <td>
                            <a class="btn btn-danger" href="cars.php?delete=<?= $car['car_id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>