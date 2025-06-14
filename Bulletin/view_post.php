<?php
require_once 'config.php';

// Check if user is logged in and is admin
if (!function_exists('isLoggedIn') || !isLoggedIn()) {
    header('Location: login.php');
    exit();
}

if (!function_exists('isAdmin') || !isAdmin()) {
    $_SESSION['error'] = 'Access denied. Admin privileges required.';
    header('Location: index.php');
    exit();
}

$post_id = (int)($_GET['id'] ?? 0);

if ($post_id <= 0) {
    $_SESSION['error'] = 'Invalid post ID.';
    header('Location: adminpanel.php');
    exit();
}

// Get post details with author information
try {
    $stmt = $pdo->prepare("
        SELECT p.*, u.username as author_name, u.email as author_email 
        FROM posts p 
        LEFT JOIN users u ON p.user_id = u.id 
        WHERE p.id = ?
    ");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch();

    if (!$post) {
        $_SESSION['error'] = 'Post not found.';
        header('Location: adminpanel.php');
        exit();
    }

    // Get comments for this post if comments table exists
    $comments = [];
    try {
        $stmt = $pdo->prepare("
            SELECT c.*, u.username as commenter_name 
            FROM comments c 
            LEFT JOIN users u ON c.user_id = u.id 
            WHERE c.post_id = ? 
            ORDER BY c.created_at ASC
        ");
        $stmt->execute([$post_id]);
        $comments = $stmt->fetchAll();
    } catch (PDOException $e) {
        // Comments table might not exist, that's okay
    }

} catch (PDOException $e) {
    $_SESSION['error'] = 'Database error: ' . $e->getMessage();
    header('Location: adminpanel.php');
    exit();
}

// Handle comment deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comment'])) {
    $comment_id = (int)($_POST['comment_id'] ?? 0);
    if ($comment_id > 0) {
        try {
            $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
            if ($stmt->execute([$comment_id])) {
                $_SESSION['success'] = 'Comment deleted successfully.';
            } else {
                $_SESSION['error'] = 'Failed to delete comment.';
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Error deleting comment: ' . $e->getMessage();
        }
        header('Location: view_post.php?id=' . $post_id);
        exit();
    }
}

$success_message = $_SESSION['success'] ?? null;
$error_message = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Post - <?= htmlspecialchars($post['title']) ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
        }
        .post-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
        }
        .post-content {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-top: -30px;
            position: relative;
            z-index: 1;
        }
        .post-meta {
            border-bottom: 1px solid #eee;
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }
        .comment-card {
            border-left: 4px solid #007bff;
            background: #f8f9fa;
            margin-bottom: 1rem;
        }
        .comment-header {
            background: #e9ecef;
            border-bottom: 1px solid #dee2e6;
        }
        .btn-back {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: color 0.3s;
        }
        .btn-back:hover {
            color: white;
        }
        .post-actions {
            position: sticky;
            top: 20px;
            z-index: 100;
        }
        .content-display {
            line-height: 1.8;
            font-size: 1.1rem;
        }
        .content-display img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 1rem 0;
        }
        .content-display pre {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            border-left: 4px solid #007bff;
        }
    </style>
</head>
<body>
    <div class="post-header">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <a href="adminpanel.php" class="btn-back mb-3 d-inline-block">
                        <i class="fas fa-arrow-left me-2"></i>Back to Admin Panel
                    </a>
                    <h1 class="mb-0"><?= htmlspecialchars($post['title']) ?></h1>
                </div>
            </div>
        </div>
    </div>

    <div class="container my-4">
        <!-- Alert Messages -->
        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success_message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error_message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-8">
                <div class="post-content p-4">
                    <!-- Post Meta Information -->
                    <div class="post-meta">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Author Information</h6>
                                <p class="mb-1"><strong>Name:</strong> <?= htmlspecialchars($post['author_name'] ?? 'Anonymous') ?></p>
                                <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($post['author_email'] ?? 'N/A') ?></p>
                                <p class="mb-0"><strong>User ID:</strong> <?= $post['user_id'] ?? 'N/A' ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Post Information</h6>
                                <p class="mb-1"><strong>Post ID:</strong> <?= $post['id'] ?></p>
                                <p class="mb-1"><strong>Created:</strong> <?= date('F j, Y \a\t g:i A', strtotime($post['created_at'])) ?></p>
                                <p class="mb-1"><strong>Updated:</strong> <?= isset($post['updated_at']) ? date('F j, Y \a\t g:i A', strtotime($post['updated_at'])) : 'Never' ?></p>
                                <p class="mb-0"><strong>Status:</strong> <span class="badge bg-success">Published</span></p>
                            </div>
                        </div>
                    </div>

                    <!-- Post Content -->
                    <div class="content-display">
                        <?= nl2br(htmlspecialchars($post['content'])) ?>
                    </div>

                    <?php if (!empty($post['tags'])): ?>
                        <div class="mt-4">
                            <h6 class="text-muted">Tags:</h6>
                            <?php 
                            $tags = explode(',', $post['tags']);
                            foreach ($tags as $tag): 
                            ?>
                                <span class="badge bg-secondary me-1"><?= htmlspecialchars(trim($tag)) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Comments Section -->
                <?php if (!empty($comments)): ?>
                    <div class="mt-4">
                        <h4 class="mb-3">Comments (<?= count($comments) ?>)</h4>
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment-card card">
                                <div class="comment-header card-header py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?= htmlspecialchars($comment['commenter_name'] ?? 'Anonymous') ?></strong>
                                            <small class="text-muted ms-2"><?= date('M j, Y \a\t g:i A', strtotime($comment['created_at'])) ?></small>
                                        </div>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this comment?')">
                                            <input type="hidden" name="delete_comment" value="1">
                                            <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="mb-0"><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-4">
                <div class="post-actions card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Post Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="edit_post.php?id=<?= $post['id'] ?>" class="btn btn-warning">
                                <i class="fas fa-edit me-2"></i>Edit Post
                            </a>
                            <a href="adminpanel.php" class="btn btn-secondary">
                                <i class="fas fa-list me-2"></i>All Posts
                            </a>
                            <hr>
                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this post? This action cannot be undone and will also delete all comments.')">
                                <input type="hidden" name="action" value="delete_post">
                                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="fas fa-trash me-2"></i>Delete Post
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Post Statistics -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Statistics</h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <h4 class="text-primary"><?= count($comments) ?></h4>
                                <small class="text-muted">Comments</small>
                            </div>
                            <div class="col-6">
                                <h4 class="text-success"><?= str_word_count($post['content']) ?></h4>
                                <small class="text-muted">Words</small>
                            </div>
                        </div>
                        <hr>
                        <div class="row text-center">
                            <div class="col-6">
                                <h4 class="text-info"><?= strlen($post['content']) ?></h4>
                                <small class="text-muted">Characters</small>
                            </div>
                            <div class="col-6">
                                <h4 class="text-warning"><?= ceil(str_word_count($post['content']) / 200) ?></h4>
                                <small class="text-muted">Min Read</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Author Card -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-user me-2"></i>Author Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="text-center">
                            <div class="mb-3">
                                <i class="fas fa-user-circle fa-4x text-secondary"></i>
                            </div>
                            <h5><?= htmlspecialchars($post['author_name'] ?? 'Anonymous') ?></h5>
                            <p class="text-muted small"><?= htmlspecialchars($post['author_email'] ?? 'No email') ?></p>
                            <a href="view_user.php?id=<?= $post['user_id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye me-1"></i>View Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>

        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        document.querySelector('form[onsubmit*="delete this post"]')?.addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to delete this post? This action cannot be undone and will also delete all comments.')) {
                e.preventDefault();
            } else {

                this.action = 'adminpanel.php';
            }
        });


        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });


        function copyPostLink() {
            const url = window.location.origin + '/read.php?id=' + <?= $post['id'] ?>;
            navigator.clipboard.writeText(url).then(function() {
                alert('Post link copied to clipboard!');
            });
        }

        if (navigator.clipboard) {
            const actionsCard = document.querySelector('.post-actions .card-body .d-grid');
            const copyButton = document.createElement('button');
            copyButton.className = 'btn btn-info';
            copyButton.innerHTML = '<i class="fas fa-link me-2"></i>Copy Link';
            copyButton.onclick = copyPostLink;
            actionsCard.insertBefore(copyButton, actionsCard.querySelector('hr'));
        }
    </script>
</body>
</html>