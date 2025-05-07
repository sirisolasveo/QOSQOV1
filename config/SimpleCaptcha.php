<?php
class SimpleCaptcha {
    public static function generateCaptcha() {
        $num1 = rand(1, 10);
        $num2 = rand(1, 10);
        $operators = ['+', '-', 'x'];
        $operator = $operators[array_rand($operators)];
        
        switch ($operator) {
            case '+':
                $result = $num1 + $num2;
                break;
            case '-':
                $result = $num1 - $num2;
                break;
            case 'x':
                $result = $num1 * $num2;
                break;
        }
        
        $_SESSION['captcha_result'] = $result;
        return "$num1 $operator $num2 = ?";
    }

    public static function validateCaptcha($userAnswer) {
        if (!isset($_SESSION['captcha_result'])) {
            return false;
        }
        
        $result = (int)$_SESSION['captcha_result'];
        unset($_SESSION['captcha_result']); // Usar una sola vez
        return $userAnswer === (string)$result;
    }

    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        return htmlspecialchars(strip_tags(trim($data)));
    }

    public static function generateCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function checkBruteForce($ip, $conn) {
        // Limpiar intentos antiguos
        $sql = "DELETE FROM login_attempts WHERE attempt_time < DATE_SUB(NOW(), INTERVAL 30 MINUTE)";
        $conn->query($sql);
        
        // Verificar intentos
        $sql = "SELECT COUNT(*) as attempts FROM login_attempts WHERE ip_address = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL 30 MINUTE)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $ip);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        return $result['attempts'] >= 5;
    }

    public static function logLoginAttempt($ip, $conn) {
        $sql = "INSERT INTO login_attempts (ip_address, attempt_time) VALUES (?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $ip);
        return $stmt->execute();
    }

    public static function resetLoginAttempts($ip, $conn) {
        $sql = "DELETE FROM login_attempts WHERE ip_address = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $ip);
        return $stmt->execute();
    }
}
?>