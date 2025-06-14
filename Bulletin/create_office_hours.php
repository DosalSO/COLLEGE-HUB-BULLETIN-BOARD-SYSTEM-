<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

// Only admin allowed (or adjust as needed)
if (!isset($_SESSION['user_id']) || !(function_exists('isAdmin') && isAdmin())) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $days = trim($_POST['days']);
    $start = trim($_POST['start_time']);
    $end = trim($_POST['end_time']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    if ($days && $start && $end && $email && $phone) {
        $stmt = $pdo->prepare("REPLACE INTO office_hours (id, days, start_time, end_time, email, phone) VALUES (1,?,?,?,?,?)");
        $stmt->execute([$days, $start, $end, $email, $phone]);
        $_SESSION['success'] = "Office hours updated!";
        header("Location: career_services.php");
        exit();
    } else {
        $error = "All fields are required.";
    }
}

// Optionally, fetch current office hours to prefill form
$stmt = $pdo->prepare("SELECT * FROM office_hours WHERE id = 1");
$stmt->execute();
$current = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Office Hours</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; }
        .card {
            border-left: 4px solid #0d6efd;
            border-radius: 10px;
            box-shadow: 0 2px 7px rgba(13,110,253,0.07);
            margin-top: 40px;
        }
        .card-header {
            background: #0d6efd;
            color: #fff;
            font-weight: 500;
        }
        .btn-primary {
            background: #0d6efd;
            border: none;
        }
        .btn-primary:hover { background: #084298; }
        .form-label { color: #0d6efd; font-weight: 500;}
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <form method="post" class="card p-4 shadow-sm">
                <div class="card-header mb-3">
                    <i class="fas fa-clock me-1"></i> Edit Office Hours
                </div>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <div class="mb-3">
                    <label class="form-label" for="days">Days</label>
                    <input type="text" class="form-control" id="days" name="days" maxlength="30"
                        value="<?= htmlspecialchars($current['days'] ?? 'Monday - Saturday') ?>" required>
                </div>
                <div class="row mb-3">
                    <div class="col">
                        <label class="form-label" for="start_time">Start Time</label>
                        <input type="time" class="form-control" id="start_time" name="start_time"
                            value="<?= htmlspecialchars($current['start_time'] ?? '06:00') ?>" required>
                    </div>
                    <div class="col">
                        <label class="form-label" for="end_time">End Time</label>
                        <input type="time" class="form-control" id="end_time" name="end_time"
                            value="<?= htmlspecialchars($current['end_time'] ?? '17:00') ?>" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="email">Contact Email</label>
                    <input type="email" class="form-control" id="email" name="email"
                        value="<?= htmlspecialchars($current['email'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="phone">Contact Phone</label>
                    <input type="text" class="form-control" id="phone" name="phone"
                        value="<?= htmlspecialchars($current['phone'] ?? '') ?>" required>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-1"></i>Save
                    </button>
                    <a href="career_services.php" class="btn btn-outline-primary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>