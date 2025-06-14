<?php
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

$general_category_id = 5;

$stmt = $pdo->prepare("SELECT * FROM posts WHERE category_id = ? ORDER BY created_at DESC");
$stmt->execute([$general_category_id]);
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
    <title>General - College Hub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .navbar {
            background: linear-gradient(135deg, #6c757d 0%, #f8f9fa 100%);
            box-shadow: 0 2px 10px rgba(108,117,125,0.09);
        }
        .navbar-brand, .nav-link { color: #fff !important; }
        .hero-section {
            background: linear-gradient(90deg, #6c757d 0%, #f8f9fa 100%);
            color: #fff;
            padding: 45px 0 30px 0;
            margin-bottom: 2rem;
            border-radius: 0 0 24px 24px;
            box-shadow: 0 4px 20px rgba(108,117,125,0.10);
        }
        .hero-section h2 { font-weight: bold; letter-spacing: 1.5px;}
        .hero-section p { opacity: 0.92; }
        .post-card {
            border-left: 6px solid #6c757d;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(108,117,125,0.05);
            transition: box-shadow 0.18s, transform 0.18s;
        }
        .post-card:hover {
            box-shadow: 0 6px 24px rgba(108,117,125,0.12);
            transform: translateY(-4px) scale(1.01);
        }
        .category-badge {
            background-color: #6c757d;
            color: #fff;
            font-weight: 500;
            font-size: 0.93em;
        }
        .post-image {
            width: 100%;
            max-height: 220px;
            object-fit: contain;
            background: #f5f5f5;
            border-radius: 8px 8px 0 0;
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
        .sidebar .card { border-left: 4px solid #6c757d; }
        .sidebar .card-header { background-color: #6c757d; color: #fff; }
        .sidebar .fw-bold { font-weight: bold !important;}
        .sidebar .active { font-weight: bold; color: #343a40 !important; }
        .quick-actions .btn { margin-bottom: 7px; }
        .citation { background-color: #f8f9fa; border-left: 4px solid #6c757d; padding: 15px; font-size: 14px; margin-top: 20px; color: #555;}
        .about-college-image {
            display: block;
            margin: 10px auto 20px auto;
            max-width: 96px;
            width: 100%;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(108,117,125,0.14);
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
        @media (max-width: 991px) {
            .hero-section { margin-bottom: 1rem; }
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
                            <li><a class="dropdown-item<?= $category['name'] === 'General' ? ' active' : '' ?>" href="<?= $category['link'] ?>">
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

<section class="hero-section text-center">
    <div class="container">
        <h2><i class="fas fa-bullhorn me-2"></i>General Announcements</h2>
        <p class="lead mb-0">Stay updated with general college news, announcements, and important information for everyone.</p>
    </div>
</section>

<div class="container mt-3">
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
        <div class="col-lg-8 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0 text-secondary"><i class="fas fa-folder-open"></i> Latest General Posts</h5>
                <a href="create_post.php?category=General" class="btn btn-secondary shadow-sm">+ Create Post</a>
            </div>
            <?php if (empty($posts)): ?>
                <div class="alert alert-info">
                    <h5>No general posts yet!</h5>
                    <p>Be the first to share a general announcement or information!</p>
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
                                        <span class="badge category-badge">General</span>
                                        <?php if (isset($post['featured']) && $post['featured']): ?>
                                            <span class="badge bg-danger">Featured</span>
                                        <?php endif; ?>
                                    </div>
                                    <h5 class="card-title mb-0">
                                        <a href="read.php?id=<?= $post['id'] ?>" class="text-decoration-none text-dark">
                                            <?= htmlspecialchars($post['title']); ?>
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
                                                   class="btn btn-sm btn-outline-secondary" 
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
                            
                            <p class="card-text mb-2"><?= nl2br(htmlspecialchars(mb_strimwidth($post['content'], 0, 180, '...'))); ?></p>
                            <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-user"></i> <?= htmlspecialchars($post['author'] ?? 'Anonymous'); ?> &nbsp;•&nbsp;
                                    <i class="fas fa-calendar-day"></i> <?= function_exists('formatDateTime') ? formatDateTime($post['created_at']) : date('M j, Y g:i A', strtotime($post['created_at'])); ?> &nbsp;•&nbsp;
                                    <i class="fas fa-eye"></i> <?= isset($post['views']) ? $post['views'] : 0; ?> views
                                </small>
                                <a href="read.php?id=<?php echo $post['id']; ?>" class="btn btn-outline-secondary btn-sm">
                                    Read More <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="col-lg-4 sidebar">
            <div class="card mb-3">
                <div class="card-header">
                    <i class="fas fa-list-ul me-1"></i>Categories
                </div>
                <div class="card-body">
                    <?php foreach ($categories as $category): ?>
                        <a href="<?= $category['link'] ?>"
                           class="d-block text-decoration-none mb-2<?= $category['name'] === 'General' ? ' fw-bold text-secondary active' : '' ?>">
                            <span class="badge me-2" style="background-color: <?= $category['color'] ?>"></span>
                            <?= htmlspecialchars($category['name']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="card mb-3 shadow quick-actions">
                <div class="card-header bg-secondary text-white">
                    <i class="fas fa-bolt me-1"></i>Quick Actions
                </div>
                <div class="card-body">
                    <a href="create_post.php" class="btn btn-primary w-100">+ Create New Post</a>
                    <a href="my_posts.php" class="btn btn-outline-secondary w-100">📝 My Posts</a>
                </div>
            </div>
            <div class="card mt-3 shadow-sm">
                <div class="card-header">
                    <i class="fas fa-info-circle me-1"></i>About College Hub
                    <?php if (function_exists('isAdmin') && isAdmin()): ?>
                        <a href="create_about_college_hub.php" class="btn btn-link btn-sm float-end text-white" style="text-decoration:underline">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <p>
                        The <strong>College Hub Bulletin Board System</strong> is an all-in-one digital platform to streamline communication, guidance services, and manage academic and administrative tasks efficiently within the college or university.
                    </p>
                    <ul>
                        <li><strong>Lack of a Centralized Platform:</strong> Students often miss out on updates because information is scattered.</li>
                        <li><strong>Accessibility:</strong> Designed with inclusivity and accessibility in mind.</li>
                    </ul>
                    <div class="citation small mt-2">
                        - Doe (2023): 60% of students struggle to receive real-time updates about scholarship opportunities.<br>
                        - Smith et al. (2022): Systems like Blackboard have been proven effective in enhancing academic communication.
                    </div>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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