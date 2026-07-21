<?php
require_once __DIR__.'/../src/Auth.php';
$auth = new Auth();
if (!$auth->isLoggedIn()) { header("Location: index.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background-color: #f4f6f9; }
        .navbar { background: #343a40; padding: 15px; color: white; display: flex; justify-content: space-between; }
        .navbar a { color: white; text-decoration: none; margin-left: 15px; }
        .container { padding: 30px; }
        .card { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
    <div class="navbar">
        <span>LightCar Inventory Tracking System - Welcome, <b><?= htmlspecialchars($_SESSION['username']) ?></b> (<?= $_SESSION['role'] ?>)</span>
        <div>
            <a href="dashboard.php">Dashboard</a>
            <a href="cars.php">Inventory</a>
            <a href="sales.php">Sales & Reports</a>
            <?php if ($auth->isAdmin()): ?><a href="users.php">Manage Users</a><?php endif; ?>
            <a href="logout.php" style="color: #ffc107;">Logout</a>
        </div>
    </div>
    <div class="container">
        <h2>Dashboard Overview</h2>
        <div class="card">
            <h3>Inventory Quick Access</h3>
            <p>Use the navigation panel above to easily record transactions, manage vehicle listings, and view real-time sales performance metrics.</p>
        </div>
    </div>
</body>
</html>