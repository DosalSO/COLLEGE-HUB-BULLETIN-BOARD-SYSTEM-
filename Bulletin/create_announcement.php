<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

if (!function_exists('isAdmin') || !isAdmin()) {
    header('Location: index.php');
    exit();
}

$title = $content = $success = $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    $author = $_SESSION['username'] ?? "Admin";
    $user_id = $_SESSION['user_id'] ?? null;

    if ($title === "" || $content === "") {
        $error = "Both title and content are required.";
    } elseif (!$user_id) {
        $error = "User not found in session. Please login again.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO announcements (title, content, created_at, author, user_id) VALUES (?, ?, NOW(), ?, ?)");
        if ($stmt->execute([$title, $content, $author, $user_id])) {
            $_SESSION['success'] = "Announcement created successfully!";
            header("Location: announcements.php");
            exit();
        } else {
            $error = "Failed to create announcement. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Announcement - College Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f6f8fa; }
        .container { max-width: 600px; margin-top: 48px; }
        .card { border-radius: 12px; }
        .form-label { font-weight: 600; }
        .btn-primary { background: linear-gradient(90deg, #34a853, #4285f4); border: none; }
        .btn-primary:hover { background: linear-gradient(90deg, #4285f4, #34a853);}
    </style>
</head>
<body>
    <div class="container">
        <div class="card shadow p-4">
            <h3 class="mb-4 text-center"><i class="fas fa-bullhorn me-2"></i>Create Announcement</h3>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST" action="create_announcement.php">
                <div class="mb-3">
                    <label for="title" class="form-label">Announcement Title</label>
                    <input type="text" class="form-control" id="title" name="title" maxlength="150" required value="<?= htmlspecialchars($title) ?>">
                </div>
                <div class="mb-3">
                    <label for="content" class="form-label">Announcement Content</label>
                    <textarea class="form-control" id="content" name="content" rows="6" required><?= htmlspecialchars($content) ?></textarea>
                </div>
                <div class="d-flex justify-content-between">
                    <a href="announcements.php" class="btn btn-secondary">Back</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-paper-plane me-1"></i>Post Announcement
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>