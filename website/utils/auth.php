<?php
require_once __DIR__ . '/paths.php';
require_once UTILS_DIR . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function login_user($email, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT user_id, email, password, name, role, status FROM User WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && $user['status'] === 'ACTIVE' && password_verify($password, $user['password'])) {
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_role'] = $user['role'];
        return true;
    }
    
    return false;
}

function logout_user() {
    session_destroy();
    $depth = isset($_SESSION['path_depth']) ? $_SESSION['path_depth'] : 1;
    $prefix = str_repeat('../', $depth);
    header("Location: {$prefix}login/");
    exit;
}

function require_login() {
    if (!is_user_logged_in()) {
        $scriptPath = $_SERVER['SCRIPT_NAME'];
        $depth = substr_count($scriptPath, '/') - 2;
        $prefix = str_repeat('../', max(1, $depth));
        header("Location: {$prefix}login/?msg=Please+log+in+to+access+this+page");
        exit;
    }
}

function is_user_logged_in() {
    return isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
}

function get_user_name() {
    return $_SESSION['user_name'] ?? 'User';
}

function get_user_role() {
    return $_SESSION['user_role'] ?? null;
}
