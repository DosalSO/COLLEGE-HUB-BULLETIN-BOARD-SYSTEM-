<?php
require_once 'config.php'; // I-load ang PDO connection at helper functions

requireLogin(); // Tiyakin na naka-login ang user

$user_id = $_SESSION['user_id']; // Kunin ang user ID

// Kunin lahat ng posts ng user gamit ang PDO, kasama ang category at image kung meron
$sql = "SELECT p.id, p.title, p.content, p.created_at, p.image_path, c.name AS category
        FROM posts p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.user_id = :user_id
        ORDER BY p.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $user_id]);
$posts = $stmt->fetchAll();

// Categories for sidebar
$categories = [
    ['name' => 'Academic Resources', 'link' => 'academic_resources.php', 'color' => '#28a745'],
    ['name' => 'Career Services', 'link' => 'career_services.php', 'color' => '#007bff'],
    ['name' => 'Financial Aid', 'link' => 'financial_aid.php', 'color' => '#ffc107'],
    ['name' => 'Student Life', 'link' => 'student_life.php', 'color' => '#dc3545'],
    ['name' => 'General', 'link' => 'general.php', 'color' => '#6c757d']
];

// formatDateTime is already in config.php, do NOT redeclare
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Posts - College Hub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .navbar {
            background: linear-gradient(135deg, #4285f4, #34a853);
            box-shadow: 0 2px 10px rgba(40,167,69,0.08);
        }
        .navbar-brand, .nav-link { color: #fff !important; }
        .container-main { margin-top: 45px; }
        .my-post-card {
            border-left: 6px solid #007bff;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,123,255,0.07);
            transition: box-shadow 0.18s, transform 0.18s;
        }
        .my-post-card:hover {
            box-shadow: 0 6px 24px rgba(0,123,255,0.15);
            transform: translateY(-4px) scale(1.01);
        }
        .category-badge {
            font-size: 0.92em;
            font-weight: 500;
            margin-right: 8px;
            padding: 6px 11px;
            background: #eee;
            color: #333;
        }
        .post-image-thumb {
            width: 100%;
            max-width: 160px;
            max-height: 110px;
            object-fit: cover;
            border-radius: 7px;
            margin-right: 20px;
            background: #f5f5f5;
            cursor: zoom-in;
        }
        .sidebar .card { border-left: 4px solid #007bff; }
        .sidebar .card-header { background-color: #007bff; color: #fff; }
        .sidebar .fw-bold { font-weight: bold !important;}
        .sidebar .active { font-weight: bold; color: #194b9a !important; }
        .quick-actions .btn { margin-bottom: 7px; }
        .back-button { margin-top: 28px; }
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
        @media (max-width: 991px) {
            .container-main { margin-top: 18px; }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="fas fa-graduation-cap me-2"></i>College Hub
        </a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="profile.php"><i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['username']); ?></a>
            <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
</nav>

<div class="container container-main">
    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0 text-primary"><i class="fas fa-thumbtack"></i> My Posts</h4>
                <a href="create_post.php" class="btn btn-primary shadow-sm">+ Create Post</a>
            </div>
            <?php if ($posts): ?>
                <?php foreach ($posts as $post): ?>
                    <div class="card mb-4 my-post-card">
                        <div class="card-body d-flex flex-wrap align-items-center">
                            <?php if (!empty($post['image_path'])): ?>
                                <img src="<?= htmlspecialchars($post['image_path']) ?>" alt="Post Image" class="post-image-thumb mb-3 mb-md-0" onclick="showImageModal(this)">
                            <?php endif; ?>
                            <div class="flex-grow-1">
                                <div class="mb-1">
                                    <?php if (!empty($post['category'])): ?>
                                        <span class="badge category-badge" style="background:<?= htmlspecialchars($categories[array_search($post['category'], array_column($categories, 'name'))]['color'] ?? '#007bff') ?>">
                                            <?= htmlspecialchars($post['category']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <small class="text-muted">Posted on <?= formatDateTime($post['created_at']) ?></small>
                                </div>
                                <h5 class="fw-bold mb-1">
                                    <a href="view_post.php?id=<?= $post['id'] ?>" class="text-decoration-none text-dark"><?= htmlspecialchars($post['title']) ?></a>
                                </h5>
                                <p class="mb-1"><?= nl2br(htmlspecialchars(mb_strimwidth($post['content'], 0, 160, '...'))) ?></p>
                                <a href="view_post.php?id=<?= $post['id'] ?>" class="btn btn-outline-primary btn-sm">View Post</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-info">No posts yet.</div>
            <?php endif; ?>
        </div>
        <div class="col-lg-4 sidebar">
            <div class="card mb-3">
                <div class="card-header">
                    <i class="fas fa-list-ul me-1"></i>Categories
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
            <div class="card mb-3 shadow quick-actions">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-bolt me-1"></i>Quick Actions
                </div>
                <div class="card-body">
                    <a href="create_post.php" class="btn btn-success w-100">+ Create New Post</a>
                    <a href="my_posts.php" class="btn btn-outline-primary w-100">📝 My Posts</a>
                </div>
            </div>
            <div class="card mt-3 shadow-sm">
                <div class="card-header">
                    <i class="fas fa-info-circle me-1"></i>About College Hub
                </div>
                <div class="card-body">
                    <small>
                        Easily manage and review all your posts here. You can view, edit, or delete your posts and track your activity in the College Hub community.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Zoom Modal -->
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
    // Show image in zoom modal
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
</script>
</body>
</html>