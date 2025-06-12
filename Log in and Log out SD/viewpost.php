<?php
require_once 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('isLoggedIn') || !isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$post_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Kuhanin ang post ayon sa ID
$stmt = $pdo->prepare("SELECT p.*, u.username AS author FROM posts p LEFT JOIN users u ON p.user_id = u.id WHERE p.id = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    $_SESSION['error'] = 'Post not found.';
    header('Location: adminpanel.php');
    exit();
}

if (!function_exists('formatDateTime')) {
    function formatDateTime($dt) {
        return date('M j, Y g:i A', strtotime($dt));
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Post - Admin Panel</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container { max-width: 700px; margin-top: 40px; }
        .card { border-radius: 10px; }
        .back-btn { margin-bottom: 20px; }
        .post-title { font-weight: bold; font-size: 2rem; }
        .post-meta { color: #6c757d; font-size: 1rem; }
        .post-content { margin-top: 24px; font-size: 1.1rem; }
    </style>
</head>
<body>
    <div class="container">
        <a href="adminpanel.php" class="btn btn-outline-secondary back-btn">
            <i class="fas fa-arrow-left"></i> Back to Admin Panel
        </a>
        <div class="card shadow p-4">
            <div class="post-title mb-2"><?= htmlspecialchars($post['title']) ?></div>
            <div class="post-meta mb-3">
                <i class="fas fa-user"></i> <?= htmlspecialchars($post['author'] ?? 'Unknown') ?>
                &nbsp; • &nbsp;
                <i class="fas fa-calendar"></i> <?= formatDateTime($post['created_at']) ?>
            </div>
            <div class="post-content">
                <?= nl2br(htmlspecialchars($post['content'])) ?>
            </div>
        </div>
    </div>
</body>
</html>