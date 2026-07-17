<?php
require_once 'config.php';

class SecurityHelper {
    private static $method = 'aes-256-cbc';

    public static function encrypt($data) {
        if (empty($data)) return $data;
        $key = hash('sha256', ENCRYPTION_KEY);
        // Kuzalisha initialization vector ya urefu unaostahili
        $iv = substr(hash('sha256', ENCRYPTION_KEY), 0, 16);
        $encrypted = openssl_encrypt($data, self::$method, $key, 0, $iv);
        return base64_encode($encrypted);
    }

    public static function decrypt($data) {
        if (empty($data)) return $data;
        $key = hash('sha256', ENCRYPTION_KEY);
        $iv = substr(hash('sha256', ENCRYPTION_KEY), 0, 16);
        $decrypted = openssl_decrypt(base64_decode($data), self::$method, $key, 0, $iv);
        return $decrypted;
    }

    public static function sanitize($data) {
        return htmlspecialchars(strip_tags(trim($data)));
    }
}
?>