<?php
require_once __DIR__.'/Database.php';

class Auth extends DatabaseModel {
    
    public function __construct() {
        parent::__construct();
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function login($username, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            return true;
        }
        return false;
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function isAdmin() {
        return $this->isLoggedIn() && ($_SESSION['role'] ?? '') === 'Admin';
    }

    public function logout() {
        session_unset();
        session_destroy();
    }

    public function userExists($username) {
        $stmt = $this->db->prepare("SELECT user_id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return (bool) $stmt->fetch();
    }

    // Only ever called from users.php, which is Admin-gated. Users can no
    // longer self-register (public register form removed).
    public function createUser($username, $password, $role) {
        if ($this->userExists($username)) {
            return false;
        }
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
        return $stmt->execute([$username, $hashed, $role]);
    }

    public function deleteUser($userId) {
        $stmt = $this->db->prepare("DELETE FROM users WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }

    public function getAll() {
        // Implementation of Abstract method
        $stmt = $this->db->query("SELECT user_id, username, role FROM users");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>