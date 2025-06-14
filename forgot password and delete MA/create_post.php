<?php
require 'config.php';

requireLogin();

$category_redirect_map = [
    'Academic Resources' => 'academic_resources.php',
    'Career Services'    => 'career_services.php',
    'Financial Aid'      => 'financial_aid.php',
    'Student Life'       => 'student_life.php',
    'General'            => 'general.php'
];

$categories = [
    ['id' => 1, 'category' => 'Academic Resources'],
    ['id' => 2, 'category' => 'Career Services'],
    ['id' => 3, 'category' => 'Financial Aid'],
    ['id' => 4, 'category' => 'Student Life'],
    ['id' => 5, 'category' => 'General']
];

$error = '';
$success = '';


$preselect_category_id = '';
if (isset($_GET['category'])) {
    $category_slug = trim($_GET['category']);
    foreach ($categories as $cat) {
        if (
            strtolower($cat['category']) === strtolower(str_replace("_", " ", $category_slug)) ||
            $cat['id'] == $category_slug
        ) {
            $preselect_category_id = $cat['id'];
            break;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitizeInput($_POST['title']);
    $content = sanitizeInput($_POST['content']);
    $category_id = intval($_POST['category_id']);
    $user_id = $_SESSION['user_id'];
    $image_path = null;


    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName = basename($_FILES['image']['name']);
        $fileSize = $_FILES['image']['size'];
        $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($fileType, ALLOWED_EXTENSIONS) && $fileSize <= MAX_FILE_SIZE) {
            $newFileName = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $fileName);
            $destPath = UPLOAD_DIR . $newFileName;
            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $image_path = $destPath;
            } else {
                $error = "Failed to move uploaded file.";
            }
        } else {
            $error = "Invalid file type or size.";
        }
    }

    if (empty($error)) {
        try {

            $category_label = '';
            foreach ($categories as $cat) {
                if ($cat['id'] == $category_id) {
                    $category_label = $cat['category'];
                    break;
                }
            }


            $stmt = $pdo->prepare("INSERT INTO posts (user_id, category_id, title, content, image_path) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $category_id, $title, $content, $image_path]);
            $success = "Post created successfully.";

  
            $target_file = isset($category_redirect_map[$category_label]) ? $category_redirect_map[$category_label] : 'index.php';

            if (file_exists($target_file)) {
                header("Location: $target_file");
                exit();
            } else {
                header("Location: index.php?error=category_redirect");
                exit();
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Post - <?= SITE_NAME ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 0 30px 0;
            min-height: 180px;
            display: flex;
            align-items: center;
            border-radius: 0 0 18px 18px;
            margin-bottom: 2rem;
        }
        .hero-icon {
            font-size: 3rem;
            color: #fff;
            opacity: 0.2;
            position: absolute;
            right: 40px;
            top: 25px;
            z-index: 0;
            animation: float 5s infinite linear;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px);}
            50% { transform: translateY(-18px);}
        }
        .hero-content {
            position: relative;
            z-index: 2;
        }
        .form-label {
            font-weight: 500;
        }
        .form-section {
            background: #fff;
            padding: 2.5rem 2rem 2rem 2rem;
            border-radius: 18px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            margin-bottom: 2rem;
        }
        .form-control, .form-select {
            border-radius: 8px;
        }
        .btn-primary {
            background: linear-gradient(90deg, #667eea 0%, #007bff 100%);
            border: none;
            font-weight: 500;
            padding-left: 2rem;
            padding-right: 2rem;
        }
        .btn-primary:hover {
            background: linear-gradient(90deg, #007bff 0%, #667eea 100%);
        }
        .alert {
            border-radius: 10px;
        }
        .back-button {
            margin-bottom: 18px;
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
                </ul>

            </div>
        </div>
    </nav>

    <div class="hero-section position-relative mb-4">
        <div class="container hero-content">
            <h2 class="fw-bold text-white mb-2"><i class="fas fa-pen-nib me-2"></i>Create New Post</h2>
            <p class="text-white-50 mb-0">
                Share announcements, opportunities, and more with the College Hub community.
            </p>
        </div>
        <span class="hero-icon"><i class="fas fa-pen-nib"></i></span>
    </div>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7 col-md-9">
                <div class="form-section">
                    <?php if ($error): ?>
                        <div class="alert alert-danger d-flex align-items-center" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php elseif ($success): ?>
                        <div class="alert alert-success d-flex align-items-center" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?= htmlspecialchars($success) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label" for="title">Title</label>
                            <input type="text" class="form-control" name="title" id="title" maxlength="100" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="category_id">Category</label>
                            <select class="form-select" name="category_id" id="category_id" required>
                                <option value="">Select category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= ($preselect_category_id == $cat['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['category']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="content">Content</label>
                            <textarea class="form-control" name="content" id="content" rows="6" maxlength="5000" required></textarea>
                        </div>
                        <div class="mb-4">
                            <label class="form-label" for="image">Upload Image (optional)</label>
                            <input type="file" class="form-control" name="image" id="image" accept=".jpg,.jpeg,.png,.gif">
                            <div class="form-text">Accepted: JPG, PNG, GIF. Max file size: <?= defined('MAX_FILE_SIZE') ? round(MAX_FILE_SIZE/1024/1024,1) : '2' ?>MB</div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane me-2"></i>Post
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>