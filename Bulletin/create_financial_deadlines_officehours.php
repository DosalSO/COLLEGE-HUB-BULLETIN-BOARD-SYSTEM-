<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

// Only admin allowed (adjust as needed)
if (!isset($_SESSION['user_id']) || !(function_exists('isAdmin') && isAdmin())) {
    header("Location: login.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deadlines = trim($_POST['deadlines']);
    $contact_email = trim($_POST['contact_email']);
    $contact_phone = trim($_POST['contact_phone']);
    $office_days = trim($_POST['office_days']);
    $office_hours = trim($_POST['office_hours']);
    $walkin_label = trim($_POST['walkin_label']);
    $walkin_hours = trim($_POST['walkin_hours']);

    if ($deadlines && $contact_email && $contact_phone && $office_days && $office_hours) {
        $stmt = $pdo->prepare("REPLACE INTO financial_office_info 
            (id, deadlines, contact_email, contact_phone, office_days, office_hours, walkin_label, walkin_hours) 
            VALUES (1, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $deadlines, $contact_email, $contact_phone, $office_days, $office_hours, $walkin_label, $walkin_hours
        ]);
        $_SESSION['success'] = "Deadlines and Office Hours updated!";
        header("Location: financial_aid.php");
        exit();
    } else {
        $error = "All fields except walk-in are required.";
    }
}

// Fetch current data to prefill
$stmt = $pdo->prepare("SELECT * FROM financial_office_info WHERE id=1");
$stmt->execute();
$current = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Important Deadlines & Office Hours</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .card { border-left: 4px solid #ffc107; border-radius: 10px; box-shadow: 0 2px 7px rgba(255,193,7,0.10); margin-top: 40px;}
        .card-header { background: #ffc107; color: #212529; font-weight: 500;}
        .btn-warning { background: #ffc107; border: none; }
        .btn-warning:hover { background: #ffb300; }
        .form-label { color: #ffc107; font-weight: 500;}
        textarea.form-control { min-height: 90px; }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9">
            <form method="post" class="card p-4 shadow-sm">
                <div class="card-header mb-3">
                    <i class="fas fa-calendar-alt me-1"></i> Edit Important Deadlines & Office Hours
                </div>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <div class="mb-3">
                    <label class="form-label" for="deadlines">Important Deadlines (use &lt;hr&gt; to separate)</label>
                    <textarea class="form-control" id="deadlines" name="deadlines" required><?= htmlspecialchars($current['deadlines'] ?? "June 30: FAFSA Application<hr>July 1: Scholarship Applications<hr>July 15: Fall Payment Due") ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="contact_email">Contact Email</label>
                    <input type="email" class="form-control" id="contact_email" name="contact_email"
                        value="<?= htmlspecialchars($current['contact_email'] ?? 'finaid@collegehub.edu') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="contact_phone">Contact Phone</label>
                    <input type="text" class="form-control" id="contact_phone" name="contact_phone"
                        value="<?= htmlspecialchars($current['contact_phone'] ?? '(555) 123-4567') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="office_days">Office Days</label>
                    <input type="text" class="form-control" id="office_days" name="office_days"
                        value="<?= htmlspecialchars($current['office_days'] ?? 'Monday - Friday') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="office_hours">Office Hours</label>
                    <input type="text" class="form-control" id="office_hours" name="office_hours"
                        value="<?= htmlspecialchars($current['office_hours'] ?? '8:00 AM - 4:30 PM') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="walkin_label">Walk-in Label (optional)</label>
                    <input type="text" class="form-control" id="walkin_label" name="walkin_label"
                        value="<?= htmlspecialchars($current['walkin_label'] ?? 'Walk-in Hours') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="walkin_hours">Walk-in Hours (optional)</label>
                    <input type="text" class="form-control" id="walkin_hours" name="walkin_hours"
                        value="<?= htmlspecialchars($current['walkin_hours'] ?? '9:00 AM - 3:00 PM') ?>">
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <button type="submit" class="btn btn-warning px-4">
                        <i class="fas fa-save me-1"></i>Save
                    </button>
                    <a href="financial_aid.php" class="btn btn-outline-warning">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>