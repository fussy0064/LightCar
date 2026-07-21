<?php
require_once __DIR__.'/../src/Auth.php';
require_once __DIR__.'/../src/CarManager.php';
$auth = new Auth();
if (!$auth->isLoggedIn()) { header("Location: index.php"); exit(); }

$carManager = new CarManager();
$error = ""; $success = ""; $editCar = null;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_car'])) {
    if (!SecurityHelper::csrfCheck($_POST['csrf_token'] ?? '')) {
        $error = "Invalid session token, please try again.";
    } else {
        $carManager->delete($_POST['delete_car']);
        $success = "Car removed successfully!";
    }
}

if (isset($_GET['edit'])) {
    $editCar = $carManager->getById($_GET['edit']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && (isset($_POST['add_car']) || isset($_POST['update_car']))) {

    if (!SecurityHelper::csrfCheck($_POST['csrf_token'] ?? '')) {
        $error = "Invalid session token, please try again.";
    } else {
        $make = SecurityHelper::sanitize($_POST['make']);
        $model = SecurityHelper::sanitize($_POST['model']);
        $price = SecurityHelper::sanitize($_POST['price']);

        if (empty($make) || empty($model) || empty($price)) {
            $error = "All fields are required.";
        } elseif (!is_numeric($price) || $price <= 0) {
            $error = "Price must be a positive number.";
        } elseif (isset($_POST['update_car'])) {
            $status = SecurityHelper::sanitize($_POST['status']);
            $carManager->update($_POST['car_id'], $make, $model, $price, $status);
            $success = "Car updated successfully!";
        } else {
            $carManager->create($make, $model, $price);
            $success = "New car listed successfully!";
        }
    }
}

$cars = isset($_GET['search']) && !empty($_GET['search']) ? $carManager->search($_GET['search']) : $carManager->getAll();
$csrfToken = SecurityHelper::csrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inventory Management</title>
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
        <span>LightCar Inventory Tracking System</span>
        <div>
            <a href="dashboard.php">Dashboard</a>
            <a href="cars.php">Inventory</a>
            <a href="sales.php">Sales & Reports</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>
    <div class="container">
        <h2>Inventory Management</h2>
        
        <?php if($error): ?><p style="color:red;"><?= $error ?></p><?php endif; ?>
        <?php if($success): ?><p style="color:green;"><?= $success ?></p><?php endif; ?>

        <div class="form-section">
            <h3><?= $editCar ? 'Edit Car' : 'Add New Car' ?></h3>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <?php if ($editCar): ?>
                    <input type="hidden" name="car_id" value="<?= $editCar['car_id'] ?>">
                <?php endif; ?>
                <input type="text" name="make" placeholder="Car Brand (e.g. Toyota)" value="<?= $editCar ? htmlspecialchars($editCar['make']) : '' ?>" required>
                <input type="text" name="model" placeholder="Model (e.g. RAV4)" value="<?= $editCar ? htmlspecialchars($editCar['model']) : '' ?>" required>
                <input type="text" name="price" placeholder="Price" value="<?= $editCar ? htmlspecialchars($editCar['price']) : '' ?>" required>
                <?php if ($editCar): ?>
                    <select name="status">
                        <option value="Available" <?= $editCar['status']=='Available'?'selected':'' ?>>Available</option>
                        <option value="Sold" <?= $editCar['status']=='Sold'?'selected':'' ?>>Sold</option>
                    </select>
                    <button class="btn" type="submit" name="update_car">Update Car</button>
                    <a class="btn" style="background:#6c757d;" href="cars.php">Cancel</a>
                <?php else: ?>
                    <button class="btn" type="submit" name="add_car">Save Car</button>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-section">
            <h3>Stock List</h3>
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
                            <a class="btn" href="cars.php?edit=<?= $car['car_id'] ?>">Edit</a>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Are you sure?')">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                <input type="hidden" name="delete_car" value="<?= $car['car_id'] ?>">
                                <button class="btn btn-danger" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>