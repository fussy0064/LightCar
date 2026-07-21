<?php
require_once __DIR__.'/../src/Auth.php';
require_once __DIR__.'/../src/SecurityHelper.php';

$auth = new Auth();

if ($auth->isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$error = "";
$csrfToken = SecurityHelper::csrfToken();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login_user'])) {

    if (!SecurityHelper::csrfCheck($_POST['csrf_token'] ?? '')) {
        $error = "Invalid session token, please try again.";
    } else {
        $username = SecurityHelper::sanitize($_POST['username']);
        $password = SecurityHelper::sanitize($_POST['password']);

        if (empty($username) || empty($password)) {
            $error = "please fill in all required fields.";
        } else {
            if ($auth->login($username, $password)) {
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "username or password is incorrect!";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MaggieCar Inventory Tracking System - Login</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f7f6; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin:0; }
        .container-box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0px 4px 10px rgba(0,0,0,0.1); width: 350px; }
        h2 { text-align: center; margin-bottom: 20px; color: #333; }
        input[type="text"], input[type="password"] { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; border: none; color: white; font-weight: bold; cursor: pointer; border-radius: 4px; margin-top: 10px; }
        .btn-success { background: #28a745; }
        .error { color: red; text-align: center; font-size: 14px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container-box">
        <h2>MaggieCar Login</h2>
        <?php if($error): ?><p class="error"><?= $error ?></p><?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <label>Username</label>
            <input type="text" name="username" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <button type="submit" name="login_user" class="btn-success">Login</button>
        </form>
    </div>
</body>
</html>
