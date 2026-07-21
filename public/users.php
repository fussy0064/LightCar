<?php
require_once __DIR__.'/../src/Auth.php';
require_once __DIR__.'/../src/SecurityHelper.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) { header("Location: index.php"); exit(); }

// Only Admin can reach this page - all accounts are now created by an Admin,
// there is no public registration form anymore.
if (!$auth->isAdmin()) { header("Location: dashboard.php"); exit(); }

$error = ""; $success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!SecurityHelper::csrfCheck($_POST['csrf_token'] ?? '')) {
        $error = "Invalid session token, please try again.";
    } elseif (isset($_POST['create_user'])) {
        $username = SecurityHelper::sanitize($_POST['username']);
        $password = $_POST['password'] ?? '';
        $role = SecurityHelper::sanitize($_POST['role']);

        if (empty($username) || empty($password) || empty($role)) {
            $error = "All fields are required.";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters.";
        } elseif (!in_array($role, ['Admin', 'Agent'], true)) {
            $error = "Invalid role selected.";
        } elseif ($auth->userExists($username)) {
            $error = "This username is already taken.";
        } else {
            if ($auth->createUser($username, $password, $role)) {
                $success = "User '$username' created successfully as $role.";
            } else {
                $error = "Failed to create user, please try again.";
            }
        }
    } elseif (isset($_POST['delete_user'])) {
        $targetId = (int) $_POST['delete_user'];
        if ($targetId === (int) $_SESSION['user_id']) {
            $error = "You cannot delete your own account while logged in.";
        } else {
            $auth->deleteUser($targetId);
            $success = "User removed successfully.";
        }
    }
}

$users = $auth->getAll();
$csrfToken = SecurityHelper::csrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
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
        input[type="text"], input[type="password"], select { padding: 8px; width: 220px; }
    </style>
</head>
<body>
    <div class="navbar">
        <span>LightCar Inventory Tracking System</span>
        <div>
            <a href="dashboard.php">Dashboard</a>
            <a href="cars.php">Inventory</a>
            <a href="sales.php">Sales & Reports</a>
            <a href="users.php">Manage Users</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>
    <div class="container">
        <h2>User Management</h2>
        <p style="color:#555;">Only a System Admin can create accounts. There is no public sign-up.</p>

        <?php if($error): ?><p style="color:red;"><?= $error ?></p><?php endif; ?>
        <?php if($success): ?><p style="color:green;"><?= $success ?></p><?php endif; ?>

        <div class="form-section">
            <h3>Create New User</h3>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password (min 6 chars)" required>
                <select name="role" required>
                    <option value="Agent">Sales Agent</option>
                    <option value="Admin">System Admin</option>
                </select>
                <button class="btn" type="submit" name="create_user">Create User</button>
            </form>
        </div>

        <div class="table-section">
            <h3>Existing Users</h3>
            <table>
                <thead>
                    <tr><th>ID</th><th>Username</th><th>Role</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php foreach($users as $u): ?>
                    <tr>
                        <td><?= $u['user_id'] ?></td>
                        <td><?= htmlspecialchars($u['username']) ?></td>
                        <td><?= htmlspecialchars($u['role']) ?></td>
                        <td>
                            <?php if ((int)$u['user_id'] !== (int)$_SESSION['user_id']): ?>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Remove this user?')">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                <input type="hidden" name="delete_user" value="<?= $u['user_id'] ?>">
                                <button class="btn btn-danger" type="submit">Delete</button>
                            </form>
                            <?php else: ?>
                                <span style="color:#999;">(you)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
