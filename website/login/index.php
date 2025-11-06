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
    <style>
        .login-container {
            min-height: calc(100vh - 4rem);
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary-color) 0%, #1e40af 50%, var(--secondary-color) 100%);
            padding: 2rem 1rem;
        }
        .login-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            padding: 2.5rem;
            width: 100%;
            max-width: 400px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-logo {
            height: 48px;
            margin-bottom: 1rem;
        }
        .login-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }
        .login-subtitle {
            color: var(--text-secondary);
            font-size: 0.875rem;
        }
        .form-group {
            margin-bottom: 1.25rem;
        }
        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }
        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: border-color 150ms;
        }
        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        .error-message {
            background-color: #fee;
            color: #c00;
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }
        .info-message {
            background-color: #eff6ff;
            color: #1e40af;
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }
        .submit-btn {
            width: 100%;
            padding: 0.75rem;
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: background-color 150ms;
        }
        .submit-btn:hover {
            background-color: var(--primary-hover);
        }
    </style>
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
