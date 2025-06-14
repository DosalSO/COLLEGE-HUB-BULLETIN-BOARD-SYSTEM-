<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

// Only admin can access this page
if (!isset($_SESSION['user_id']) || !(function_exists('isAdmin') && isAdmin())) {
    header("Location: login.php");
    exit();
}

// Handle form submission
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $headline = trim($_POST['headline']);
    $description = trim($_POST['description']);
    $features = trim($_POST['features']);
    $citations = trim($_POST['citations']);
    $image_path = trim($_POST['image_path']);

    if ($headline && $description && $features && $citations) {
        // Upsert logic: only one about_college_hub row (id=1)
        $stmt = $pdo->prepare("REPLACE INTO about_college_hub 
            (id, headline, description, features, citations, image_path) 
            VALUES (1,?,?,?,?,?)");
        $stmt->execute([$headline, $description, $features, $citations, $image_path]);
        $_SESSION['success'] = "About College Hub updated!";
        header("Location: general.php");
        exit();
    } else {
        $error = "All fields except image are required.";
    }
}

// Fetch current data for prefilling
$stmt = $pdo->prepare("SELECT * FROM about_college_hub WHERE id = 1");
$stmt->execute();
$current = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit About College Hub</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; }
        .card {
            border-left: 4px solid #6c757d;
            border-radius: 10px;
            box-shadow: 0 2px 7px rgba(108,117,125,0.07);
            margin-top: 40px;
        }
        .card-header {
            background: #6c757d;
            color: #fff;
            font-weight: 500;
        }
        .btn-secondary {
            background: #6c757d;
            border: none;
        }
        .btn-secondary:hover { background: #343a40; }
        .form-label { color: #6c757d; font-weight: 500;}
        textarea.form-control { min-height: 90px; }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9">
            <form method="post" class="card p-4 shadow-sm">
                <div class="card-header mb-3">
                    <i class="fas fa-info-circle me-1"></i> Edit About College Hub
                </div>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <div class="mb-3">
                    <label class="form-label" for="headline">Headline</label>
                    <input type="text" class="form-control" id="headline" name="headline" maxlength="100"
                        value="<?= htmlspecialchars($current['headline'] ?? 'College Hub Bulletin Board System') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" required><?= htmlspecialchars($current['description'] ?? 'An all-in-one digital platform to streamline communication, guidance services, and manage academic and administrative tasks efficiently within the college or university.') ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="features">Features (use HTML &lt;ul&gt;...&lt;/ul&gt; or newline for list)</label>
                    <textarea class="form-control" id="features" name="features" required><?= htmlspecialchars($current['features'] ?? "<ul>\n<li><strong>Lack of a Centralized Platform:</strong> Students often miss out on updates because information is scattered.</li>\n<li><strong>Accessibility:</strong> Designed with inclusivity and accessibility in mind.</li>\n</ul>") ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="citations">Citations (use &lt;br&gt; for line breaks)</label>
                    <textarea class="form-control" id="citations" name="citations" required><?= htmlspecialchars($current['citations'] ?? "- Doe (2023): 60% of students struggle to receive real-time updates about scholarship opportunities.<br>- Smith et al. (2022): Systems like Blackboard have been proven effective in enhancing academic communication.") ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="image_path">Image Path (optional, e.g. about_college_hub.png)</label>
                    <input type="text" class="form-control" id="image_path" name="image_path"
                        value="<?= htmlspecialchars($current['image_path'] ?? '') ?>">
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <button type="submit" class="btn btn-secondary px-4">
                        <i class="fas fa-save me-1"></i>Save
                    </button>
                    <a href="general.php" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>