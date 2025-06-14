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
    $icon = trim($_POST['icon']);
    $title = trim($_POST['title']);
    $tip = trim($_POST['tip']);

    if ($title && $tip) {
        $stmt = $pdo->prepare("INSERT INTO quick_tips (icon, title, tip) VALUES (?, ?, ?)");
        $stmt->execute([$icon, $title, $tip]);
        $_SESSION['success'] = "Quick Tip added!";
        header("Location: student_life.php");
        exit();
    } else {
        $_SESSION['error'] = "Title and tip required!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Quick Tip - College Hub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
        .card {
            border-left: 6px solid #dc3545;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(220,53,69,0.08);
            margin-top: 30px;
        }
        .card-header {
            background: linear-gradient(90deg, #dc3545 0%, #fff0f0 100%);
            color: #fff;
            font-weight: bold;
            letter-spacing: 1.2px;
            border-radius: 8px 8px 0 0;
        }
        .btn-danger {
            background-color: #dc3545;
            border: none;
        }
        .btn-danger:hover, .btn-outline-danger:hover {
            background-color: #b21c2b !important;
        }
        .form-label {
            font-weight: 500;
            color: #dc3545;
        }
        .form-control:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.20rem rgba(220,53,69,0.14);
        }
        .required {
            color: #dc3545;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg" style="background: linear-gradient(135deg, #dc3545 0%, #fff0f0 100%); box-shadow: 0 2px 10px rgba(220,53,69,0.09);">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="fas fa-graduation-cap me-2"></i>College Hub
        </a>
    </div>
</nav>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9">
            <form method="post" class="card p-4 shadow-sm mt-5">
                <div class="card-header mb-3">
                    <i class="fas fa-lightbulb me-1"></i> Add Quick Tip
                </div>
                <?php if (!empty($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
                <?php endif; ?>
                <div class="mb-3">
                    <label for="icon" class="form-label">Icon (emoji, optional)</label>
                    <input type="text" name="icon" id="icon" class="form-control" maxlength="10" placeholder="e.g. 💡">
                </div>
                <div class="mb-3">
                    <label for="title" class="form-label">Tip Title <span class="required">*</span></label>
                    <input type="text" name="title" id="title" required class="form-control" maxlength="100" placeholder="e.g. Academic Tips">
                </div>
                <div class="mb-3">
                    <label for="tip" class="form-label">Tip <span class="required">*</span></label>
                    <input type="text" name="tip" id="tip" required class="form-control" maxlength="255" placeholder="e.g. Use study apps, join group studies, ask for help.">
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <button type="submit" class="btn btn-danger px-4"><i class="fas fa-save me-1"></i>Save Tip</button>
                    <a href="student_life.php" class="btn btn-outline-danger">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>