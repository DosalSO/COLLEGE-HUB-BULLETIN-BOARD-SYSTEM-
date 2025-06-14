<?php
require_once 'config.php';


if (!function_exists('isLoggedIn') || !isLoggedIn()) {
    header('Location: login.php');
    exit();
}


if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$post_id = (int)$_GET['id'];


$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch();

if (!$post) {
    $_SESSION['error'] = "Post not found.";
    header('Location: index.php');
    exit();
}


$user_id = $_SESSION['user_id'] ?? null;
$is_admin = function_exists('isAdmin') && isAdmin();
$is_owner = ($post['user_id'] == $user_id);

if (!$is_owner && !$is_admin) {
    $_SESSION['error'] = "You don't have permission to delete this post.";
    header('Location: index.php');
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
     
        $pdo->beginTransaction();

        if (!empty($post['image_path']) && file_exists($post['image_path'])) {
            unlink($post['image_path']);
        }
        

        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->execute([$post_id]);
        

        $pdo->commit();
        
        $_SESSION['success'] = "Post deleted successfully.";
        header('Location: index.php');
        exit();
        
    } catch (Exception $e) {

        $pdo->rollback();
        $_SESSION['error'] = "Error deleting post: " . $e->getMessage();
        header('Location: index.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Post - <?= defined('SITE_NAME') ? SITE_NAME : 'College Hub' ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-graduation-cap me-2"></i>College Hub
            </a>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-danger text-white">
                        <h4 class="mb-0"><i class="fas fa-trash-alt me-2"></i>Delete Post</h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Warning:</strong> This action cannot be undone!
                        </div>
                        
                        <div class="mb-4">
                            <h5>Post to be deleted:</h5>
                            <div class="card">
                                <?php if (!empty($post['image_path']) && file_exists($post['image_path'])): ?>
                                    <img src="<?= htmlspecialchars($post['image_path']) ?>" 
                                         alt="Post Image" 
                                         class="card-img-top" 
                                         style="height: 200px; object-fit: cover;">
                                <?php endif; ?>
                                <div class="card-body">
                                    <h6 class="card-title"><?= htmlspecialchars($post['title']) ?></h6>
                                    <p class="card-text"><?= nl2br(htmlspecialchars(substr($post['content'], 0, 200))) ?>...</p>
                                    <small class="text-muted">
                                        By <?= htmlspecialchars($post['author'] ?? 'Anonymous') ?> • 
                                        <?= htmlspecialchars($post['created_at']) ?>
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Cancel
                            </a>
                            
                            <form method="POST" class="d-inline">
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you absolutely sure you want to delete this post? This action cannot be undone.')">
                                    <i class="fas fa-trash-alt me-2"></i>Delete Post
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>