<?php
require_once 'config.php';

// Display success/error messages
if (isset($_SESSION['success'])) {
    $success_message = $_SESSION['success'];
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    $error_message = $_SESSION['error'];
    unset($_SESSION['error']);
}

$stmt = $pdo->prepare("
    SELECT * FROM posts 
    WHERE 1=1 
    ORDER BY created_at DESC 
    LIMIT 10
");
$stmt->execute();
$posts = $stmt->fetchAll();

$categories = [
    ['name' => 'Academic Resources', 'link' => 'academic_resources.php', 'color' => '#28a745'],
    ['name' => 'Career Services', 'link' => 'career_services.php', 'color' => '#007bff'],
    ['name' => 'Financial Aid', 'link' => 'financial_aid.php', 'color' => '#ffc107'],
    ['name' => 'Student Life', 'link' => 'student_life.php', 'color' => '#dc3545'],
    ['name' => 'General', 'link' => 'general.php', 'color' => '#6c757d']
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= defined('SITE_NAME') ? SITE_NAME : 'College Hub Bulletin Board System' ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .post-image {
            width: 100%;
            height: 200px;
            object-fit: contain;
            background: #f5f5f5;
            border-radius: 5px 5px 0 0;
            display: block;
            cursor: zoom-in;
            transition: filter 0.3s;
        }
        .post-image:hover {
            filter: brightness(0.9);
        }
        #modalZoomImg {
            width: auto;
            max-width: 100%;
            max-height: 80vh;
            display: block;
            margin: 0 auto;
            cursor: grab;
            background: #222;
        }
        .modal-content.bg-dark {
            background: #000 !important;
        }
        .post-actions {
            opacity: 0;
            transition: opacity 0.3s;
        }
        .post-card:hover .post-actions {
            opacity: 1;
        }
        .alert-dismissible {
            position: relative;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-graduation-cap me-2"></i>College Hub
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            Categories
                        </a>
                        <ul class="dropdown-menu">
                            <?php foreach ($categories as $category): ?>
                                <li><a class="dropdown-item" href="<?= $category['link'] ?>">
                                    <span class="badge me-2" style="background-color: <?= $category['color'] ?>"></span>
                                    <?= htmlspecialchars($category['name']) ?>
                                </a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (function_exists('isLoggedIn') && isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-plus me-1"></i>Create
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="create_post.php">New Post</a></li>
                                <?php if (function_exists('isAdmin') && isAdmin()): ?>
                                    <li><a class="dropdown-item" href="create_announcement.php">New Announcement</a></li>
                                <?php endif; ?>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                                <li><a class="dropdown-item" href="my_posts.php">My Posts</a></li>
                                <?php if (function_exists('isAdmin') && isAdmin()): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="adminpanel.php">Admin Panel</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Sign Up</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="hero-section" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 80px 0; min-height: 400px; display: flex; align-items: center;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold text-white mb-4">
                        College Hub Bulletin Board System
                    </h1>
                    <p class="lead text-white-50 mb-4">
                        Your centralized platform for academic communication, resources, and announcements.
                    </p>
                    <?php if (!function_exists('isLoggedIn') || !isLoggedIn()): ?>
                        <div class="d-flex gap-3">
                            <a href="register.php" class="btn btn-light btn-lg">Get Started</a>
                            <a href="login.php" class="btn btn-outline-light btn-lg">Login</a>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-lg-6">
                    <div class="hero-graphic" style="text-align: center;">
                        <i class="fas fa-bullhorn fa-10x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-5">
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success_message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error_message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-8">
                <h2 class="mb-4"><i class="fas fa-newspaper text-primary me-2"></i>Recent Posts</h2>
                <?php if (empty($posts)): ?>
                    <div class="alert alert-info">
                        <h5>No posts yet!</h5>
                        <p>Be the first to create a post and start the conversation.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <div class="card mb-4 post-card">
                            <?php if (!empty($post['image_path'])): ?>
                                <img src="<?= htmlspecialchars($post['image_path']) ?>"
                                     alt="Post Image"
                                     class="card-img-top post-image"
                                     onclick="showImageModal(this)">
                            <?php endif; ?>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h5 class="card-title mb-0">
                                        <a href="read.php?id=<?= $post['id'] ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($post['title']) ?>
                                        </a>
                                    </h5>
                                    
                                    <?php if (function_exists('isLoggedIn') && isLoggedIn()): ?>
                                        <?php 
                                        $user_id = $_SESSION['user_id'] ?? null;
                                        $is_admin = function_exists('isAdmin') && isAdmin();
                                        $is_owner = ($post['user_id'] == $user_id);
                                        ?>
                                        
                                        <?php if ($is_owner || $is_admin): ?>
                                            <div class="post-actions">
                                                <div class="btn-group" role="group">
                                                    <a href="edit_post.php?id=<?= $post['id'] ?>" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       title="Edit Post">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="delete.php?id=<?= $post['id'] ?>" 
                                                       class="btn btn-sm btn-outline-danger" 
                                                       title="Delete Post"
                                                       onclick="return confirm('Are you sure you want to delete this post?')">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                                
                                <p class="card-text"><?= nl2br(htmlspecialchars(substr($post['content'], 0, 200))) ?>...</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        By <?= htmlspecialchars($post['author'] ?? 'Anonymous') ?> • 
                                        <?= function_exists('formatDateTime') ? formatDateTime($post['created_at']) : htmlspecialchars($post['created_at']) ?>
                                    </small>
                                    <a href="read.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-primary">
                                        Read More <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-tags me-2"></i>Categories</h6>
                    </div>
                    <div class="card-body">
                        <?php foreach ($categories as $category): ?>
                            <a href="<?= $category['link'] ?>" class="d-block text-decoration-none mb-2">
                                <span class="badge me-2" style="background-color: <?= $category['color'] ?>"></span>
                                <?= htmlspecialchars($category['name']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="imageZoomModal" tabindex="-1" aria-labelledby="imageZoomModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark border-0 shadow-none">
          <div class="modal-body p-0 text-center">
            <img src="" alt="Zoomed Image" id="modalZoomImg">
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/wheelzoom/4.0.1/wheelzoom.min.js"></script>
    <script>
        function showImageModal(img) {
            const modalImg = document.getElementById('modalZoomImg');
            modalImg.src = img.src;

            if (modalImg._wheelzoom) wheelzoom.destroy(modalImg);

            const modal = new bootstrap.Modal(document.getElementById('imageZoomModal'));
            modal.show();

            setTimeout(function() {
                wheelzoom(modalImg, {zoom: 0.06, maxZoom: 20});
            }, 350);
        }
        
        document.getElementById('imageZoomModal').addEventListener('hidden.bs.modal', function () {
            const modalImg = document.getElementById('modalZoomImg');
            if (modalImg._wheelzoom) wheelzoom.destroy(modalImg);
            modalImg.src = "";
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>