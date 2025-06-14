<?php
require_once 'config.php';

$error = '';
$success = '';
$step = 'email'; // email, code, reset

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['step'])) {
        $step = $_POST['step'];
        
        switch ($step) {
            case 'email':
                $email = sanitizeInput($_POST['email'] ?? '');
                
                if (empty($email)) {
                    $error = 'Please enter your email address.';
                } else {
                    try {
                        // Check if email exists
                        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND is_active = 1");
                        $stmt->execute([$email]);
                        $user = $stmt->fetch();
                        
                        if ($user) {
                            // Generate reset code
                            $reset_code = sprintf('%06d', mt_rand(100000, 999999));
                            $expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                            
                            // Store reset code in session (in production, store in database)
                            $_SESSION['reset_email'] = $email;
                            $_SESSION['reset_code'] = $reset_code;
                            $_SESSION['reset_expires'] = $expires_at;
                            
                            // In production, send email here
                            // For demo purposes, we'll show the code
                            $success = "Reset code sent to your email. For demo: Your code is <strong>$reset_code</strong>";
                            $step = 'code';
                        } else {
                            $error = 'No account found with that email address.';
                        }
                    } catch (PDOException $e) {
                        $error = 'An error occurred. Please try again.';
                    }
                }
                break;
                
            case 'code':
                $code = sanitizeInput($_POST['reset_code'] ?? '');
                
                if (empty($code)) {
                    $error = 'Please enter the reset code.';
                } elseif (!isset($_SESSION['reset_code']) || $code !== $_SESSION['reset_code']) {
                    $error = 'Invalid reset code.';
                } elseif (strtotime($_SESSION['reset_expires']) < time()) {
                    $error = 'Reset code has expired. Please request a new one.';
                    unset($_SESSION['reset_code'], $_SESSION['reset_email'], $_SESSION['reset_expires']);
                    $step = 'email';
                } else {
                    $success = 'Code verified! Please enter your new password.';
                    $step = 'reset';
                }
                break;
                
            case 'reset':
                $password = $_POST['password'] ?? '';
                $confirm_password = $_POST['confirm_password'] ?? '';
                
                if (empty($password) || empty($confirm_password)) {
                    $error = 'Please fill in all fields.';
                } elseif (strlen($password) < 6) {
                    $error = 'Password must be at least 6 characters long.';
                } elseif ($password !== $confirm_password) {
                    $error = 'Passwords do not match.';
                } else {
                    try {
                        // Update password
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
                        $stmt->execute([$hashed_password, $_SESSION['reset_email']]);
                        
                        // Clear session
                        unset($_SESSION['reset_code'], $_SESSION['reset_email'], $_SESSION['reset_expires']);
                        
                        $success = 'Password updated successfully! You can now login with your new password.';
                        $step = 'complete';
                    } catch (PDOException $e) {
                        $error = 'An error occurred. Please try again.';
                    }
                }
                break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - <?= SITE_NAME ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 2rem 0;
        }
        
        .auth-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: none;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .auth-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.15);
        }
        
        .auth-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 2rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .auth-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
        
        .auth-logo {
            position: relative;
            z-index: 2;
        }
        
        .auth-logo i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.9;
        }
        
        .auth-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 2;
        }
        
        .auth-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            position: relative;
            z-index: 2;
        }
        
        .auth-form {
            padding: 2.5rem;
        }
        
        .form-floating {
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .form-floating input {
            border: 2px solid transparent;
            border-radius: 15px;
            padding: 1.5rem 1rem 0.5rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }
        
        .form-floating input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            background: white;
        }
        
        .form-floating label {
            padding: 1rem;
            font-weight: 500;
            color: #6c757d;
        }
        
        .form-floating input:focus ~ label,
        .form-floating input:not(:placeholder-shown) ~ label {
            color: #667eea;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 15px;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-primary:hover::before {
            left: 100%;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
        
        .btn-outline-primary {
            border: 2px solid #667eea;
            color: #667eea;
            border-radius: 15px;
            padding: 0.8rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-outline-primary:hover {
            background: #667eea;
            border-color: #667eea;
            transform: translateY(-2px);
        }
        
        .alert {
            border-radius: 15px;
            border: none;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            color: white;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #51cf66 0%, #40c057 100%);
            color: white;
        }
        
        .back-link {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .back-link:hover {
            color: white;
            transform: translateX(-5px);
        }
        
        .progress-steps {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }
        
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 10px;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .step.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .step.completed {
            background: linear-gradient(135deg, #51cf66 0%, #40c057 100%);
            color: white;
        }
        
        .step::after {
            content: '';
            position: absolute;
            right: -25px;
            width: 20px;
            height: 2px;
            background: #e9ecef;
            top: 50%;
            transform: translateY(-50%);
        }
        
        .step:last-child::after {
            display: none;
        }
        
        .step.completed::after {
            background: linear-gradient(135deg, #51cf66 0%, #40c057 100%);
        }
        
        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }
        
        .shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float-shapes 10s infinite linear;
        }
        
        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }
        
        .shape:nth-child(2) {
            width: 120px;
            height: 120px;
            top: 60%;
            right: 10%;
            animation-delay: -3s;
        }
        
        .shape:nth-child(3) {
            width: 60px;
            height: 60px;
            bottom: 20%;
            left: 20%;
            animation-delay: -6s;
        }
        
        @keyframes float-shapes {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-30px) rotate(180deg); }
        }
        
        .code-input {
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
            letter-spacing: 0.5rem;
        }
        
        @media (max-width: 768px) {
            .auth-card {
                margin: 1rem;
            }
            
            .auth-header {
                padding: 2rem 1.5rem 1.5rem;
            }
            
            .auth-title {
                font-size: 2rem;
            }
            
            .auth-form {
                padding: 2rem 1.5rem;
            }
            
            .progress-steps {
                margin-bottom: 1.5rem;
            }
            
            .step {
                width: 35px;
                height: 35px;
                margin: 0 5px;
            }
        }
    </style>
</head>
<body>
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>
    
    <div class="auth-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6 col-xl-5">
                    <div class="auth-card">
                        <div class="auth-header">
                            <div class="auth-logo">
                                <i class="fas fa-key"></i>
                            </div>
                            <h2 class="auth-title">
                                <?php
                                switch ($step) {
                                    case 'email': echo 'Forgot Password'; break;
                                    case 'code': echo 'Enter Code'; break;
                                    case 'reset': echo 'Reset Password'; break;
                                    case 'complete': echo 'Success!'; break;
                                }
                                ?>
                            </h2>
                            <p class="auth-subtitle">
                                <?php
                                switch ($step) {
                                    case 'email': echo 'Enter your email to receive a reset code'; break;
                                    case 'code': echo 'Check your email for the verification code'; break;
                                    case 'reset': echo 'Create your new password'; break;
                                    case 'complete': echo 'Your password has been updated'; break;
                                }
                                ?>
                            </p>
                        </div>

                        <div class="auth-form">
                            <?php if ($step !== 'complete'): ?>
                                <div class="progress-steps">
                                    <div class="step <?= $step === 'email' ? 'active' : ($step !== 'email' ? 'completed' : '') ?>">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                    <div class="step <?= $step === 'code' ? 'active' : ($step === 'reset' ? 'completed' : '') ?>">
                                        <i class="fas fa-shield-alt"></i>
                                    </div>
                                    <div class="step <?= $step === 'reset' ? 'active' : '' ?>">
                                        <i class="fas fa-lock"></i>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($error): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($success): ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i><?= $success ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($step === 'email'): ?>
                                <form method="POST">
                                    <input type="hidden" name="step" value="email">
                                    <div class="form-floating">
                                        <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                                        <label for="email"><i class="fas fa-envelope me-2"></i>Email Address</label>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100 mb-3">
                                        <i class="fas fa-paper-plane me-2"></i>Send Reset Code
                                    </button>
                                </form>
                            
                            <?php elseif ($step === 'code'): ?>
                                <form method="POST">
                                    <input type="hidden" name="step" value="code">
                                    <div class="form-floating">
                                        <input type="text" class="form-control code-input" id="reset_code" name="reset_code" placeholder="000000" maxlength="6" required>
                                        <label for="reset_code"><i class="fas fa-shield-alt me-2"></i>Verification Code</label>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100 mb-3">
                                        <i class="fas fa-check me-2"></i>Verify Code
                                    </button>
                                    <button type="button" class="btn btn-outline-primary w-100" onclick="location.reload()">
                                        <i class="fas fa-redo me-2"></i>Resend Code
                                    </button>
                                </form>
                            
                            <?php elseif ($step === 'reset'): ?>
                                <form method="POST">
                                    <input type="hidden" name="step" value="reset">
                                    <div class="form-floating">
                                        <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                                        <label for="password"><i class="fas fa-lock me-2"></i>New Password</label>
                                    </div>
                                    <div class="form-floating">
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                                        <label for="confirm_password"><i class="fas fa-lock me-2"></i>Confirm Password</label>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100 mb-3">
                                        <i class="fas fa-save me-2"></i>Update Password
                                    </button>
                                </form>
                            
                            <?php elseif ($step === 'complete'): ?>
                                <div class="text-center">
                                    <div class="mb-4">
                                        <i class="fas fa-check-circle fa-5x text-success"></i>
                                    </div>
                                    <a href="login.php" class="btn btn-primary w-100">
                                        <i class="fas fa-sign-in-alt me-2"></i>Go to Login
                                    </a>
                                </div>
                            <?php endif; ?>

                            <?php if ($step !== 'complete'): ?>
                                <div class="text-center">
                                    <p class="mb-0">Remember your password? 
                                        <a href="login.php" class="text-decoration-none fw-bold" style="color: #667eea;">Sign In</a>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <a href="index.php" class="back-link">
                            <i class="fas fa-arrow-left me-2"></i>Back to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Enhanced form interactions
            const inputs = document.querySelectorAll('.form-floating input');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });
                input.addEventListener('blur', function() {
                    if (!this.value) {
                        this.parentElement.classList.remove('focused');
                    }
                });
                
                // Add typing animation effect
                input.addEventListener('input', function() {
                    this.style.transform = 'scale(1.02)';
                    setTimeout(() => {
                        this.style.transform = 'scale(1)';
                    }, 150);
                });
            });
            
            // Add entrance animation
            const authCard = document.querySelector('.auth-card');
            authCard.style.opacity = '0';
            authCard.style.transform = 'translateY(30px)';
            
            setTimeout(() => {
                authCard.style.transition = 'all 0.6s ease';
                authCard.style.opacity = '1';
                authCard.style.transform = 'translateY(0)';
            }, 100);
            
            // Auto-format verification code input
            const codeInput = document.getElementById('reset_code');
            if (codeInput) {
                codeInput.addEventListener('input', function() {
                    this.value = this.value.replace(/[^0-9]/g, '');
                });
                
                codeInput.addEventListener('paste', function(e) {
                    e.preventDefault();
                    const paste = (e.clipboardData || window.clipboardData).getData('text');
                    this.value = paste.replace(/[^0-9]/g, '').substring(0, 6);
                });
            }
            
            // Password strength indicator
            const passwordInput = document.getElementById('password');
            if (passwordInput) {
                passwordInput.addEventListener('input', function() {
                    const password = this.value;
                    let strength = 0;
                    
                    if (password.length >= 6) strength++;
                    if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
                    if (password.match(/\d/)) strength++;
                    if (password.match(/[^a-zA-Z\d]/)) strength++;
                    
                    // Visual feedback could be added here
                });
            }
        });
    </script>
</body>
</html>