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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    switch ($action) {
        case 'delete_post':
            $post_id = $_POST['post_id'] ?? 0;
            $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
            if ($stmt->execute([$post_id])) {
                $_SESSION['success'] = 'Post deleted successfully.';
            } else {
                $_SESSION['error'] = 'Failed to delete post.';
            }
            break;
        case 'delete_user':
            $user_id = $_POST['user_id'] ?? 0;
            if ($user_id != $_SESSION['user_id']) {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                if ($stmt->execute([$user_id])) {
                    $_SESSION['success'] = 'User deleted successfully.';
                } else {
                    $_SESSION['error'] = 'Failed to delete user.';
                }
            } else {
                $_SESSION['error'] = 'Cannot delete your own account.';
            }
            break;
        case 'toggle_user_status':
            $user_id = $_POST['user_id'] ?? 0;
            $new_status = $_POST['new_status'] ?? 'active';
            $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
            if ($stmt->execute([$new_status, $user_id])) {
                $_SESSION['success'] = 'User status updated successfully.';
            } else {
                $_SESSION['error'] = 'Failed to update user status.';
            }
            break;
        case 'make_admin':
            $user_id = $_POST['user_id'] ?? 0;
            $stmt = $pdo->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
            if ($stmt->execute([$user_id])) {
                $_SESSION['success'] = 'User promoted to admin successfully.';
            } else {
                $_SESSION['error'] = 'Failed to promote user.';
            }
            break;
    }
    header('Location: adminpanel.php');
    exit();
}

$stats = [];
$stmt = $pdo->query("SELECT COUNT(*) as total FROM posts");
$stats['total_posts'] = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
$stats['total_users'] = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM posts WHERE DATE(created_at) = CURDATE()");
$stats['posts_today'] = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$stats['new_users_week'] = $stmt->fetch()['total'];

$stmt = $pdo->prepare("
    SELECT p.*, u.username as author 
    FROM posts p 
    LEFT JOIN users u ON p.user_id = u.id 
    ORDER BY p.created_at DESC 
    LIMIT 10
");
$stmt->execute();
$recent_posts = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT u.*, COUNT(p.id) as post_count 
    FROM users u 
    LEFT JOIN posts p ON u.id = p.user_id 
    GROUP BY u.id 
    ORDER BY u.created_at DESC
");
$stmt->execute();
$users = $stmt->fetchAll();

if (isset($_SESSION['success'])) {
    $success_message = $_SESSION['success'];
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    $error_message = $_SESSION['error'];
    unset($_SESSION['error']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - <?= defined('SITE_NAME') ? SITE_NAME : 'College Hub' ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .admin-sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            z-index: 1000;
            transition: transform 0.3s ease;
        }
        .admin-content { margin-left: 250px; padding: 20px; min-height: 100vh; background: #f8f9fa; }
        .sidebar-nav { padding: 20px 0; }
        .sidebar-nav .nav-link { color: rgba(255, 255, 255, 0.8); padding: 12px 20px; border-radius: 0; transition: all 0.3s; }
        .sidebar-nav .nav-link:hover, .sidebar-nav .nav-link.active { color: white; background: rgba(255, 255, 255, 0.1); border-left: 4px solid white; }
        .stat-card { background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); transition: transform 0.3s; }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-icon { width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; color: white; }
        .table-actions .btn { padding: 4px 8px; font-size: 12px; }
        @media (max-width: 768px) {
            .admin-sidebar { transform: translateX(-100%); }
            .admin-sidebar.show { transform: translateX(0); }
            .admin-content { margin-left: 0; }
        }
        .admin-header { background: white; padding: 15px 0; border-bottom: 1px solid #dee2e6; margin-bottom: 30px; }
        .brand-logo { color: #667eea; font-weight: bold; font-size: 24px; }
    </style>
</head>
<body>
    <nav class="admin-sidebar" id="adminSidebar">
        <div class="p-3">
            <h4 class="text-white mb-0">
                <i class="fas fa-shield-alt me-2"></i>Admin Panel
            </h4>
            <small class="text-white-50">College Hub Management</small>
        </div>
        <ul class="nav flex-column sidebar-nav">
            <li class="nav-item">
                <a class="nav-link active" href="#dashboard" data-section="dashboard">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#posts" data-section="posts">
                    <i class="fas fa-newspaper me-2"></i>Manage Posts
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#users" data-section="users">
                    <i class="fas fa-users me-2"></i>Manage Users
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#settings" data-section="settings">
                    <i class="fas fa-cog me-2"></i>Settings
                </a>
            </li>
            <li class="nav-item mt-4">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-arrow-left me-2"></i>Back to Site
                </a>
            </li>
        </ul>
    </nav>
    <div class="admin-content">
        <div class="admin-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <button class="btn btn-outline-primary d-md-none" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <span class="brand-logo ms-2">College Hub Admin</span>
                </div>
                <div>
                    <span class="text-muted">Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></span>
                    <a href="logout.php" class="btn btn-sm btn-outline-danger ms-2">
                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                    </a>
                </div>
            </div>
        </div>
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
        <div id="dashboard-section" class="admin-section">
            <h2 class="mb-4">Dashboard Overview</h2>
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="stat-card">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon" style="background: #28a745;">
                                <i class="fas fa-newspaper"></i>
                            </div>
                            <div class="ms-3">
                                <h3 class="mb-0"><?= $stats['total_posts'] ?></h3>
                                <small class="text-muted">Total Posts</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon" style="background: #007bff;">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="ms-3">
                                <h3 class="mb-0"><?= $stats['total_users'] ?></h3>
                                <small class="text-muted">Total Users</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon" style="background: #ffc107;">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                            <div class="ms-3">
                                <h3 class="mb-0"><?= $stats['posts_today'] ?></h3>
                                <small class="text-muted">Posts Today</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon" style="background: #dc3545;">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="ms-3">
                                <h3 class="mb-0"><?= $stats['new_users_week'] ?></h3>
                                <small class="text-muted">New Users (7d)</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Recent Posts</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_posts)): ?>
                        <p class="text-muted">No recent posts found.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Author</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($recent_posts, 0, 5) as $post): ?>
                                        <tr>
                                            <td>
                                                <a href="read.php?id=<?= $post['id'] ?>" class="text-decoration-none">
                                                    <?= htmlspecialchars(substr($post['title'], 0, 50)) ?>...
                                                </a>
                                            </td>
                                            <td><?= htmlspecialchars($post['author'] ?? 'Anonymous') ?></td>
                                            <td><?= function_exists('formatDateTime') ? formatDateTime($post['created_at']) : date('M j, Y', strtotime($post['created_at'])) ?></td>
                                            <td>
                                                <a href="viewpost.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                                                <a href="editpost.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-outline-warning">Edit</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div id="posts-section" class="admin-section" style="display: none;">
            <h2 class="mb-4">Manage Posts</h2>
            
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-newspaper me-2"></i>All Posts</h5>
                    <a href="create_post.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i>Create Post
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_posts as $post): ?>
                                    <tr>
                                        <td><?= $post['id'] ?></td>
                                        <td>
                                            <a href="read.php?id=<?= $post['id'] ?>" class="text-decoration-none">
                                                <?= htmlspecialchars(substr($post['title'], 0, 40)) ?>...
                                            </a>
                                        </td>
                                        <td><?= htmlspecialchars($post['author'] ?? 'Anonymous') ?></td>
                                        <td><?= function_exists('formatDateTime') ? formatDateTime($post['created_at']) : date('M j, Y', strtotime($post['created_at'])) ?></td>
                                        <td class="table-actions">
                                            <a href="read.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="editpost.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-outline-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this post?')">
                                                <input type="hidden" name="action" value="delete_post">
                                                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

  
        <div id="users-section" class="admin-section" style="display: none;">
            <h2 class="mb-4">Manage Users</h2>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>All Users</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Posts</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?= $user['id'] ?></td>
                                        <td><?= htmlspecialchars($user['username']) ?></td>
                                        <td><?= htmlspecialchars($user['email'] ?? 'N/A') ?></td>
                                        <td>
                                            <span class="badge <?= ($user['role'] ?? 'user') === 'admin' ? 'bg-danger' : 'bg-secondary' ?>">
                                                <?= ucfirst($user['role'] ?? 'user') ?>
                                            </span>
                                        </td>
                                        <td><?= $user['post_count'] ?></td>
                                        <td><?= function_exists('formatDateTime') ? formatDateTime($user['created_at']) : date('M j, Y', strtotime($user['created_at'])) ?></td>
                                        <td class="table-actions">
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <?php if (($user['role'] ?? 'user') !== 'admin'): ?>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="action" value="make_admin">
                                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Make Admin">
                                                            <i class="fas fa-user-shield"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                                    <input type="hidden" name="action" value="delete_user">
                                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-muted">Current User</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div id="settings-section" class="admin-section" style="display: none;">
            <h2 class="mb-4">System Settings</h2>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-cog me-2"></i>General Settings</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Site configuration and preferences.</p>
                            <div class="list-group list-group-flush">
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    Site Name
                                    <span class="text-muted"><?= defined('SITE_NAME') ? SITE_NAME : 'College Hub' ?></span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    Database Status
                                    <span class="badge bg-success">Connected</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    PHP Version
                                    <span class="text-muted"><?= phpversion() ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Security</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Security and maintenance options.</p>
                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-warning">
                                    <i class="fas fa-broom me-2"></i>Clear Cache
                                </button>
                                <button class="btn btn-outline-info">
                                    <i class="fas fa-download me-2"></i>Backup Database
                                </button>
                                <button class="btn btn-outline-danger">
                                    <i class="fas fa-exclamation-triangle me-2"></i>Maintenance Mode
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('[data-section]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelectorAll('.sidebar-nav .nav-link').forEach(l => l.classList.remove('active'));
                this.classList.add('active');
                const section = this.getAttribute('data-section');
                document.querySelectorAll('.admin-section').forEach(s => s.style.display = 'none');
                document.getElementById(section + '-section').style.display = 'block';
            });
        });
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('adminSidebar').classList.toggle('show');
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