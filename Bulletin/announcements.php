<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

// Optional: Only admins can create announcements
$isAdmin = function_exists('isAdmin') && isAdmin();

// Fetch all announcements, latest first, join with users if user_id is used
$stmt = $pdo->query("SELECT a.*, u.username AS user_name 
    FROM announcements a 
    LEFT JOIN users u ON a.user_id = u.id 
    ORDER BY a.created_at DESC");
$announcements = $stmt->fetchAll();

$success_message = $_SESSION['success'] ?? '';
if (isset($_SESSION['success'])) unset($_SESSION['success']);
$error_message = $_SESSION['error'] ?? '';
if (isset($_SESSION['error'])) unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Announcements - College Hub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f6f8fa; }
        .container { max-width: 800px; margin-top: 36px; }
        .card { border-radius: 12px; }
        .card-announcement {
            border-left: 5px solid #4285f4;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(66,133,244,0.07);
            transition: box-shadow 0.15s;
        }
        .card-announcement:hover {
            box-shadow: 0 6px 24px rgba(66,133,244,0.14);
        }
        .announcement-title {
            font-size: 1.25rem;
            font-weight: bold;
        }
        .announcement-meta {
            color: #777;
            font-size: 0.96em;
        }
        .add-btn {
            background: linear-gradient(90deg, #34a853, #4285f4); 
            border: none;
            color: #fff;
        }
        .add-btn:hover { background: linear-gradient(90deg, #4285f4, #34a853);}
    </style>
</head>
<body>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-bullhorn me-2 text-primary"></i>Announcements</h2>
            <?php if ($isAdmin): ?>
                <a href="create_announcement.php" class="btn add-btn">
                    <i class="fas fa-plus me-1"></i>New Announcement
                </a>
            <?php endif; ?>
        </div>
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

        <?php if (empty($announcements)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>No announcements yet.
            </div>
        <?php else: ?>
            <?php foreach ($announcements as $a): ?>
            <div class="card card-announcement">
                <div class="card-body">
                    <div class="announcement-title mb-2"><?= htmlspecialchars($a['title']) ?></div>
                    <div class="announcement-meta mb-2">
                        <i class="fas fa-user"></i>
                        <?= htmlspecialchars($a['user_name'] ?? $a['author'] ?? 'Unknown') ?>
                        &nbsp; • &nbsp;
                        <i class="fas fa-calendar"></i>
                        <?= date('M j, Y g:i A', strtotime($a['created_at'])) ?>
                    </div>
                    <div class="announcement-content mb-1">
                        <?= nl2br(htmlspecialchars($a['content'])) ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <div class="mt-4">
            <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to Mainboard</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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