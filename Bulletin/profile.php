<?php
require_once 'config.php'; // Include database connection and utility functions
requireLogin(); // Function to restrict access if not logged in

$user_id = $_SESSION['user_id'];

// Get user data from database
$stmt = $pdo->prepare("SELECT first_name, last_name, email, user_type, created_at FROM users WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "User not found.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile - College Hub</title>
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
        .profile-container {
            max-width: 520px;
            margin: 60px auto;
            padding: 36px 32px 32px 32px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(66,133,244,0.08);
            position: relative;
        }
        .profile-avatar {
            width: 86px;
            height: 86px;
            border-radius: 50%;
            object-fit: cover;
            object-position: center;
            background: #e9ecef;
            box-shadow: 0 2px 8px rgba(52,152,219,0.11);
            display: block;
            margin: 0 auto 15px auto;
        }
        .profile-badge {
            position: absolute;
            top: 28px;
            right: 36px;
            background: #34a853;
            color: #fff;
            padding: 7px 16px 7px 11px;
            border-radius: 18px;
            font-size: 1em;
            font-weight: bold;
            box-shadow: 0 2px 8px rgba(52,152,219,0.13);
        }
        h2 {
            text-align: center;
            margin-bottom: 22px;
            color: #333;
            font-weight: 700;
            letter-spacing: 1.2px;
        }
        .profile-info {
            font-size: 16px;
        }
        .profile-info strong {
            color: #34a853;
            min-width: 110px;
            display: inline-block;
        }
        .profile-label {
            color: #888;
            font-weight: 500;
            font-size: 15px;
            min-width: 100px;
            display: inline-block;
        }
        .profile-row {
            margin-bottom: 13px;
        }
        .btn-back {
            margin-top: 30px;
            font-size: 1.08em;
            box-shadow: 0 2px 8px rgba(66,133,244,0.10);
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
            <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
</nav>

<div class="profile-container mt-5">
    <!-- Optionally add user initials as avatar if no image -->
    <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['first_name'].' '.$user['last_name']) ?>&background=34a853&color=fff&size=128" alt="User Avatar" class="profile-avatar">
    <div class="profile-badge"><i class="fas fa-user-circle"></i> <?= htmlspecialchars(ucfirst($user['user_type'])) ?></div>
    <h2><i class="fas fa-user"></i> My Profile</h2>
    <div class="profile-info mx-2">
        <div class="profile-row"><span class="profile-label"><i class="fas fa-user me-1"></i>Full Name:</span> <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></div>
        <div class="profile-row"><span class="profile-label"><i class="fas fa-envelope me-1"></i>Email:</span> <?= htmlspecialchars($user['email']) ?></div>
        <div class="profile-row"><span class="profile-label"><i class="fas fa-id-badge me-1"></i>User Type:</span> <?= htmlspecialchars(ucfirst($user['user_type'])) ?></div>
        <div class="profile-row"><span class="profile-label"><i class="fas fa-calendar me-1"></i>Member Since:</span> <?= formatDateTime($user['created_at']) ?></div>
    </div>
</div>

</body>
</html>