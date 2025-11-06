<?php
require_once __DIR__ . '/../utils/paths.php';
require_once UTILS_DIR . '/auth.php';

$error = '';
$message = isset($_GET['msg']) ? $_GET['msg'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (login_user($email, $password)) {
        header("Location: ../maintenance/");
        exit;
    } else {
        $error = 'Invalid email or password. Please check your credentials and try again.';
    }
}

$basePrefix = '..';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sign In - EventLink</title>
    <link rel="stylesheet" href="../css/style.css" />
</head>
<body>
    <?php require_once LAYOUT_DIR . '/navbar.php'; ?>
    
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <img src="../img/logo_main.png" alt="EventLink Logo" class="login-logo" />
                <h1 class="login-title">Sign In</h1>
                <p class="login-subtitle">Enter your credentials to continue</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            
            <?php if ($message): ?>
                <div class="info-message"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input" 
                        required 
                        autofocus
                        placeholder="Enter your email"
                    />
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input" 
                        required
                        placeholder="Enter your password"
                    />
                </div>
                
                <button type="submit" class="submit-btn">Sign In</button>
            </form>
        </div>
    </div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>lucide.createIcons();</script>
</body>
</html>
