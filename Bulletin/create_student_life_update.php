<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $user_id = $_SESSION['user_id'];
    $category_id = 4; // Student Life
    $image_path = '';

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $uploads_dir = 'uploads/';
        if (!is_dir($uploads_dir)) {
            mkdir($uploads_dir, 0755, true);
        }
        $tmp_name = $_FILES['image']['tmp_name'];
        $basename = basename($_FILES['image']['name']);
        $ext = strtolower(pathinfo($basename, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($ext, $allowed)) {
            $filename = uniqid('img_', true) . '.' . $ext;
            $destination = $uploads_dir . $filename;
            if (move_uploaded_file($tmp_name, $destination)) {
                $image_path = $destination;
            } else {
                $_SESSION['error'] = "Image upload failed.";
                header("Location: student_life.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Invalid image type. Allowed: " . implode(', ', $allowed);
            header("Location: student_life.php");
            exit();
        }
    }

    if ($title && $content) {
        $stmt = $pdo->prepare("INSERT INTO posts (title, content, user_id, category_id, image_path, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$title, $content, $user_id, $category_id, $image_path]);
        $_SESSION['success'] = "Student life update posted!";
        header("Location: student_life.php");
        exit();
    } else {
        $_SESSION['error'] = "Title and content are required.";
        header("Location: student_life.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Student Life Update</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h3 class="mb-4 text-danger"><i class="fas fa-plus-circle"></i> Create Student Life Update</h3>
    <form action="create_student_life_update.php" method="post" enctype="multipart/form-data" class="card p-4 shadow-sm">
        <div class="mb-3">
            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
            <input type="text" name="title" id="title" maxlength="100" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
            <textarea name="content" id="content" rows="5" maxlength="2000" class="form-control" required></textarea>
        </div>
        <div class="mb-3">
            <label for="image" class="form-label">Image (optional)</label>
            <input class="form-control" type="file" name="image" id="image" accept="image/*">
            <small class="text-muted">Allowed types: jpg, jpeg, png, gif, webp</small>
        </div>
        <button type="submit" class="btn btn-danger">Post Update</button>
        <a href="student_life.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>