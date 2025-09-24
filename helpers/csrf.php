<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Lấy CSRF token từ session (nếu chưa có thì tạo mới).
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Sinh input hidden chứa CSRF token để đưa vào form.
 */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . 
        htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Kiểm tra CSRF token trong request.
 * @param string $token token nhận từ client (POST hoặc GET).
 * @return bool true nếu hợp lệ, false nếu không.
 */
function verify_csrf(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
