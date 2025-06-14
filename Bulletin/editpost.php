<?php
require_once 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('isLoggedIn') || !isLoggedIn()) {
    header('Location: login.php');
    exit();
}

if (!function_exists('isAdmin') || !isAdmin()) {
    $_SESSION['error'] = 'Access denied. Admin privileges required.';
    header('Location: index.php');
    exit();
}

$post_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Handle update POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title === '' || $content === '') {
        $error = "Both title and content are required.";
    } else {
        $stmt = $pdo->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ?");
        if ($stmt->execute([$title, $content, $post_id])) {
            $_SESSION['success'] = "Post updated successfully!";
            header("Location: viewpost.php?id=$post_id");
            exit();
        } else {
            $error = "Failed to update post. Try again.";
        }
    }
}

// Fetch post to edit
$stmt = $pdo->prepare("SELECT p.*, u.username AS author FROM posts p LEFT JOIN users u ON p.user_id = u.id WHERE p.id = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    $_SESSION['error'] = 'Post not found.';
    header('Location: adminpanel.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Post - Admin Panel</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container { max-width: 700px; margin-top: 40px; }
        .card { border-radius: 10px; }
        .back-btn { margin-bottom: 20px; }
        .form-label { font-weight: 600; }
    </style>
</head>
<body>
    <div class="container">
        <a href="viewpost.php?id=<?= $post['id'] ?>" class="btn btn-outline-secondary back-btn">
            <i class="fas fa-arrow-left"></i> Back to Post
        </a>
        <div class="card shadow p-4">
            <h3 class="mb-4"><i class="fas fa-edit me-2"></i>Edit Post</h3>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST" action="editpost.php?id=<?= $post['id'] ?>">
                <div class="mb-3">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" name="title" id="title" maxlength="255"
                        class="form-control" required value="<?= htmlspecialchars($post['title']) ?>">
                </div>
                <div class="mb-3">
                    <label for="content" class="form-label">Content</label>
                    <textarea name="content" id="content" rows="7"
                        class="form-control" required><?= htmlspecialchars($post['content']) ?></textarea>
                </div>
                <div class="d-flex justify-content-between">
                    <a href="adminpanel.php" class="btn btn-secondary">Back to Admin Panel</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-1"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>