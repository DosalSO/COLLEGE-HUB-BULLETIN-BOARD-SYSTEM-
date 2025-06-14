<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_SESSION['success'])) {
    $success_message = $_SESSION['success'];
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    $error_message = $_SESSION['error'];
    unset($_SESSION['error']);
}

$categories = [
    ['name' => 'Academic Resources', 'link' => 'academic_resources.php', 'color' => '#28a745'],
    ['name' => 'Career Services', 'link' => 'career_services.php', 'color' => '#007bff'],
    ['name' => 'Financial Aid', 'link' => 'financial_aid.php', 'color' => '#ffc107'],
    ['name' => 'Student Life', 'link' => 'student_life.php', 'color' => '#dc3545'],
    ['name' => 'General', 'link' => 'general.php', 'color' => '#6c757d']
];

$career_category_id = 2;

$stmt = $pdo->prepare("SELECT * FROM posts WHERE category_id = ? ORDER BY created_at DESC");
$stmt->execute([$career_category_id]);
$posts = $stmt->fetchAll();

// Fetch current office hours from database
$office = $pdo->query("SELECT * FROM office_hours WHERE id=1")->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Career Services</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #007bff 100%);
            padding: 60px 0 40px 0;
            min-height: 220px;
            display: flex;
            align-items: center;
            border-radius: 0 0 18px 18px;
            margin-bottom: 2rem;
        }
        .hero-icon {
            font-size: 4rem;
            color: #fff;
            opacity: 0.2;
            position: absolute;
            right: 40px;
            top: 30px;
            z-index: 0;
            animation: float 5s infinite linear;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px);}
            50% { transform: translateY(-18px);}
        }
        .hero-content { position: relative; z-index: 2; }
        .category-badge { font-size: 0.9rem; margin-right: 5px; }
        .card-title a {
            color: #212529; text-decoration: none; transition: color 0.2s;
        }
        .card-title a:hover { color: #007bff; }
        .post-card {
            transition: transform 0.15s, box-shadow 0.15s;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .post-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
        }
        .post-image {
            width: 100%;
            max-height: 250px;
            object-fit: contain;
            background: #f5f5f5;
            border-radius: 5px 5px 0 0;
            display: block;
            margin-bottom: 10px;
            cursor: zoom-in;
            transition: filter 0.3s;
        }
        .post-image:hover { filter: brightness(0.9); }
        #modalZoomImg {
            width: auto;
            max-width: 100%;
            max-height: 80vh;
            display: block;
            margin: 0 auto;
            cursor: grab;
            background: #222;
            border-radius: 6px;
        }
        .modal-content.bg-dark { background: #000 !important; }
        .sidebar .card { border-left: 4px solid #007bff; }
        .sidebar .list-group-item {
            border: none; background: none; padding-left: 0;
        }
        .btn-outline-primary { transition: 0.3s; }
        .btn-outline-primary:hover {
            background-color: #007bff;
            color: white;
        }
        .back-button { margin-bottom: 18px; }
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
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" href="#" role="button" data-bs-toggle="dropdown">
                            Categories
                        </a>
                        <ul class="dropdown-menu">
                            <?php foreach ($categories as $category): ?>
                                <li><a class="dropdown-item<?= $category['name'] === 'Career Services' ? ' active' : '' ?>" href="<?= $category['link'] ?>">
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

    <div class="hero-section position-relative mb-4">
        <div class="container hero-content">
            <h2 class="fw-bold text-white mb-2"><i class="fas fa-briefcase me-2"></i>Career Services</h2>
            <p class="text-white-50 mb-0">
                Explore career opportunities, workshops, and professional development resources.
            </p>
        </div>
        <span class="hero-icon"><i class="fas fa-briefcase"></i></span>
    </div>

    <div class="container">
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
                <?php if (empty($posts)): ?>
                    <div class="alert alert-info">
                        <h5>No career services posts yet!</h5>
                        <p>Be the first to share a career opportunity or resource.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <div class="card mb-4 post-card">
                            <?php if (!empty($post['image_path'])): ?>
                                <img src="<?= htmlspecialchars($post['image_path']) ?>" alt="Post Image" class="post-image" onclick="showImageModal(this)">
                            <?php endif; ?>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <div class="mb-2">
                                            <span class="badge bg-primary category-badge">Career Services</span>
                                            <?php if (isset($post['featured']) && $post['featured']): ?>
                                                <span class="badge bg-warning text-dark">Featured</span>
                                            <?php endif; ?>
                                        </div>
                                        <h5 class="card-title mb-0">
                                            <a href="read.php?id=<?= $post['id'] ?>">
                                                <?= htmlspecialchars($post['title']) ?>
                                            </a>
                                        </h5>
                                    </div>
                                    
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
                                        <?= function_exists('formatDateTime') ? formatDateTime($post['created_at']) : date('M Y', strtotime($post['created_at'])) ?>
                                    </small>
                                    <a href="read.php?id=<?= $post['id'] ?>" class="btn btn-outline-primary btn-sm">
                                        Read More <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <a href="create_post.php?category=Career_Services" class="btn btn-success mb-4">+ Create Post</a>
            </div>
            <div class="col-lg-4 sidebar">
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-link me-1"></i>Quick Links
                    </div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <a href="#" class="text-decoration-none"><i class="fas fa-user-tie me-2"></i>Career Counseling</a>
                        </li>
                        <li class="list-group-item">
                            <a href="#" class="text-decoration-none"><i class="fas fa-clipboard-list me-2"></i>Job Postings</a>
                        </li>
                        <li class="list-group-item">
                            <a href="#" class="text-decoration-none"><i class="fas fa-chalkboard-teacher me-2"></i>Workshops & Seminars</a>
                        </li>
                        <li class="list-group-item">
                            <a href="#" class="text-decoration-none"><i class="fas fa-file-alt me-2"></i>Resume Resources</a>
                        </li>
                    </ul>
                </div>
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-clock me-1"></i>Office Hours
                        <?php if (function_exists('isAdmin') && isAdmin()): ?>
                            <a href="create_office_hours.php" class="btn btn-link btn-sm float-end text-white" style="text-decoration:underline">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if ($office): ?>
                            <small><?= htmlspecialchars($office['days']) ?></small><br>
                            <strong><?= date('g:i A', strtotime($office['start_time'])) ?> - <?= date('g:i A', strtotime($office['end_time'])) ?></strong>
                            <hr>
                            <small><i class="fas fa-envelope me-1"></i><?= htmlspecialchars($office['email']) ?></small>
                            <br>
                            <small><i class="fas fa-phone me-1"></i><?= htmlspecialchars($office['phone']) ?></small>
                        <?php else: ?>
                            <small>No office hours set.</small>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-tags me-1"></i>Categories
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