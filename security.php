<?php
require_once 'config.php';

class SecurityHelper {
    private static $method = 'aes-256-cbc';

    // NOTE: each call generates a fresh random IV and stores it with the
    // ciphertext (iv + encrypted, base64). Reusing one static IV (old code)
    // leaks patterns between equal plaintexts - this fixes that.
    public static function encrypt($data) {
        if ($data === null || $data === '') return $data;
        $key = hash('sha256', ENCRYPTION_KEY, true);
        $ivLen = openssl_cipher_iv_length(self::$method);
        $iv = random_bytes($ivLen);
        $encrypted = openssl_encrypt((string)$data, self::$method, $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv . $encrypted);
    }

    public static function decrypt($data) {
        if (empty($data)) return $data;
        $key = hash('sha256', ENCRYPTION_KEY, true);
        $raw = base64_decode($data);
        $ivLen = openssl_cipher_iv_length(self::$method);
        $iv = substr($raw, 0, $ivLen);
        $encrypted = substr($raw, $ivLen);
        return openssl_decrypt($encrypted, self::$method, $key, OPENSSL_RAW_DATA, $iv);
    }

    public static function sanitize($data) {
        return htmlspecialchars(strip_tags(trim($data)));
    }

    // CSRF protection
    public static function csrfToken() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function csrfCheck($token) {
        if (session_status() == PHP_SESSION_NONE) session_start();
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$token);
    }
}
?>