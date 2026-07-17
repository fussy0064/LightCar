<?php
require_once 'auth.php';
require_once 'security.php';

$auth = new Auth();

if ($auth->isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$error = "";
$success = "";
$csrfToken = SecurityHelper::csrfToken();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!SecurityHelper::csrfCheck($_POST['csrf_token'] ?? '')) {
        $error = "Invalid session token, please try again.";
    } else

    if (isset($_POST['login_user'])) {
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
    
    if (isset($_POST['register_user'])) {
        $username = SecurityHelper::sanitize($_POST['reg_username']);
        $password = SecurityHelper::sanitize($_POST['reg_password']);
        $role = SecurityHelper::sanitize($_POST['reg_role']);

        if (empty($username) || empty($password) || empty($role)) {
            $error = "please fill in all the registration fields.";
        } elseif (strlen($password) < 6) {
            $error = "password must be at least 6 characters.";
        } else {
            
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT user_id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            
            if ($stmt->fetch()) {
                $error = "this username is already registered!";
            } else {
                
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                
                $insertStmt = $db->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
                if ($insertStmt->execute([$username, $hashedPassword, $role])) {
                    $success = "registration was successful,you can now log in to the system.";
                } else {
                    $error = "registration failed,try again.";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Car Sales - Login & Register</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f7f6; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin:0; }
        .container-box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0px 4px 10px rgba(0,0,0,0.1); width: 350px; }
        h2 { text-align: center; margin-bottom: 20px; color: #333; }
        input[type="text"], input[type="password"], select { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; border: none; color: white; font-weight: bold; cursor: pointer; border-radius: 4px; margin-top: 10px; }
        .btn-success { background: #28a745; }
        .btn-primary { background: #007bff; }
        .error { color: red; text-align: center; font-size: 14px; font-weight: bold; }
        .success { color: green; text-align: center; font-size: 14px; font-weight: bold; }
        .toggle-link { text-align: center; margin-top: 15px; font-size: 14px; color: #007bff; cursor: pointer; }
        .hidden { display: none; }
    </style>
</head>
<body>
    <div class="container-box">
        <!-- Sehemu ya kuonyesha Alerts -->
        <?php if($error): ?><p class="error"><?= $error ?></p><?php endif; ?>
        <?php if($success): ?><p class="success"><?= $success ?></p><?php endif; ?>

        <!-- Form 1: LOGIN INTERFACE -->
        <div id="loginFormSection">
            <h2>login</h2>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <label>Username</label>
                <input type="text" name="username" required>
                
                </label>Password</label>
                <input type="password" name="password" required>
                
                <button type="submit" name="login_user" class="btn-success">Login</button>
            </form>
            <div class="toggle-link" onclick="toggleForms()">don't have account? register here</div>
        </div>

        <!-- Form 2: REGISTER INTERFACE (NEW SECTION) -->
        <div id="registerFormSection" class="hidden">
            <h2>register new user</h2>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <label>name of the new user</label>
                <input type="text" name="reg_username" required>
                
                <label>Password</label>
                <input type="password" name="reg_password" placeholder="not less than 6 charater" required>
                
                <label>Role</label>
                <select name="reg_role" required>
                    <option value="Agent">Sales Agent</option>
                    <option value="Admin">System Admin</option>
                </select>
                
                <button type="submit" name="register_user" class="btn-primary">Register New User</button>
            </form>
            <div class="toggle-link" onclick="toggleForms()">you already have an account? log in here</div>
        </div>
    </div>

    <!-- JavaScript ya Kubadili Fomu Bila Ku-refresh Page -->
    <script>
        function toggleForms() {
            var loginForm = document.getElementById('loginFormSection');
            var registerForm = document.getElementById('registerFormSection');
            
            if (loginForm.classList.contains('hidden')) {
                loginForm.classList.remove('hidden');
                registerForm.classList.add('hidden');
            } else {
                loginForm.classList.add('hidden');
                registerForm.classList.remove('hidden');
            }
        }
    </script>
</body>
</html>