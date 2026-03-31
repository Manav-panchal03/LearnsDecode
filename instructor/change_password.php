<?php
session_start();
require '../config/config.php';
require_once '../includes/session_utils.php';
activateRole('instructor');

if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'instructor'){
    header('Location: ../login.php');
    exit();
}

$instructor_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

if(isset($_POST['change_password'])){
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if(empty($current_password) || empty($new_password) || empty($confirm_password)){
        $error_msg = 'Please fill all password fields.';
    } elseif($new_password !== $confirm_password){
        $error_msg = 'New password and confirmation do not match.';
    } elseif(strlen($new_password) < 8){
        $error_msg = 'New password must be at least 8 characters long.';
    } else {
        $user_q = mysqli_query($conn, "SELECT password FROM users WHERE id = $instructor_id");
        $user_data = mysqli_fetch_assoc($user_q);

        if(!$user_data || !password_verify($current_password, $user_data['password'])){
            $error_msg = 'Current password is incorrect.';
        } else {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            if(mysqli_query($conn, "UPDATE users SET password = '$hashed' WHERE id = $instructor_id")){
                $success_msg = 'Password changed successfully!';
            } else {
                $error_msg = 'Unable to update password. Please try again.';
            }
        }
    }
}

$profile_q = mysqli_query($conn, "SELECT u.name, p.profile_pic FROM users u LEFT JOIN instructor_profiles p ON u.id = p.user_id WHERE u.id = $instructor_id");
$profile_data = mysqli_fetch_assoc($profile_q);
$p_img = ($profile_data && !empty($profile_data['profile_pic']) && $profile_data['profile_pic'] != 'default-avatar.png')
        ? "../uploads/profile/" . $profile_data['profile_pic']
        : "https://ui-avatars.com/api/?name=" . urlencode($profile_data['name']) . "&background=6c63ff&color=fff";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password | LearnsDecode</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 280px;
            --primary-color: #6c63ff;
            --dark-bg: #1e1e2d;
        }
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; overflow-x: hidden; }
        #sidebar { width: var(--sidebar-width); height: 100vh; position: fixed; left: 0; top: 0; background: var(--dark-bg); color: #a2a3b7; transition: all 0.3s ease-in-out; z-index: 1000; box-shadow: 4px 0 10px rgba(0,0,0,0.1); }
        #sidebar.active { left: calc(-1 * var(--sidebar-width)); }
        .sidebar-header { padding: 30px 20px; text-align: center; background: rgba(0,0,0,0.2); }
        .nav-links { padding: 20px 0; }
        .nav-links a { padding: 12px 25px; display: flex; align-items: center; color: #a2a3b7; text-decoration: none; transition: 0.2s; border-left: 4px solid transparent; }
        .nav-links a i { width: 30px; font-size: 1.1rem; }
        .nav-links a:hover, .nav-links a.active { background: #2b2b40; color: #ffffff; border-left: 4px solid var(--primary-color); }
        #content { width: calc(100% - var(--sidebar-width)); margin-left: var(--sidebar-width); transition: all 0.3s ease-in-out; min-height: 100vh; }
        #content.active { width: 100%; margin-left: 0; }
        .navbar-custom { background: #ffffff; padding: 15px 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        #sidebarCollapse { background: var(--primary-color); border: none; color: white; padding: 5px 12px; border-radius: 5px; }
        .card-panel { border: none; border-radius: 25px; background: #ffffff; box-shadow: 0 18px 45px rgba(36, 38, 70, 0.08); }
        .form-control { border-radius: 15px; border: 1px solid #e7e9f2; padding: 14px 18px; }
        .form-control:focus { box-shadow: none; border-color: #6c63ff; }
        .btn-primary { border-radius: 50px; padding: 12px 28px; }
        .profile-portrait { width: 52px; height: 52px; border-radius: 18px; object-fit: cover; }
        .note-box { background: #f8f9ff; border-radius: 16px; padding: 18px; border: 1px solid #e7e9f2; }
    </style>
</head>
<body>

<nav id="sidebar">
    <div class="sidebar-header">
        <h3 class="fw-bold text-white mb-0">Learns<span style="color:var(--primary-color)">Decode</span></h3>
        <small>Instructor Workspace</small>
    </div>
    <div class="nav-links">
        <a href="dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
        <a href="add_course.php"><i class="fas fa-plus-square"></i> Create Course</a>
        <a href="manage_courses.php"><i class="fas fa-book-open"></i> My Courses</a>
        <a href="add_quiz.php"><i class="fas fa-question-circle"></i> Create Quizzes</a>
        <a href="manage_quizzes.php"><i class="fas fa-tasks"></i> Manage Quizzes</a>
        <a href="broadcast_center.php"><i class="fas fa-envelope"></i> Broadcast Center</a>
        <a href="manage_students.php"><i class="fas fa-users"></i> Students</a>
        <a href="manage_certificates.php"><i class="fas fa-certificate"></i> Certificates</a>
        <a href="earnings.php"><i class="fas fa-wallet"></i> Earnings</a>
        <a href="analytics.php"><i class="fas fa-chart-line"></i> Analytics</a>
        <hr style="border-color: rgba(255,255,255,0.1)">
        <a href="profile.php" class="active"><i class="fas fa-user-circle"></i> Profile</a>
        <a href="../logout.php" class="text-danger"><i class="fas fa-power-off"></i> Logout</a>
    </div>
</nav>

<div id="content">
    <nav class="navbar navbar-custom d-flex justify-content-between align-items-center">
        <button type="button" id="sidebarCollapse"><i class="fas fa-align-left"></i></button>
        <div class="user-info d-flex align-items-center">
            <div class="text-end me-3 d-none d-sm-block">
                <div class="fw-bold lh-1"><?= htmlspecialchars($profile_data['name']) ?></div>
                <small class="text-muted" style="font-size: 11px;">Instructor</small>
            </div>
            <img src="<?= $p_img ?>" class="rounded-circle shadow-sm border" width="45" height="45" style="object-fit: cover;">
        </div>
    </nav>

    <div class="container-fluid p-4">
        <div class="row justify-content-center">
            <div class="col-xl-8">
                <div class="card-panel p-4" data-aos="fade-up">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <h2 class="fw-bold mb-1">Change Password</h2>
                            <p class="text-muted mb-0">Enter your current password, then choose a new secure password.</p>
                        </div>
                        <div class="text-end">
                            <img src="<?= $p_img ?>" alt="Profile" class="profile-portrait shadow-sm">
                        </div>
                    </div>

                    <?php if($success_msg): ?>
                        <div class="alert alert-success mb-4" role="alert"><?= htmlspecialchars($success_msg) ?></div>
                    <?php endif; ?>
                    <?php if($error_msg): ?>
                        <div class="alert alert-danger mb-4" role="alert"><?= htmlspecialchars($error_msg) ?></div>
                    <?php endif; ?>

                    <form method="POST" class="row g-4">
                        <div class="col-md-12">
                            <label class="form-label fw-bold small text-muted">Current Password</label>
                            <input type="password" name="current_password" class="form-control" placeholder="Enter current password" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">New Password</label>
                            <input type="password" name="new_password" class="form-control" placeholder="New password" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="Confirm new password" required>
                        </div>
                        <div class="col-12 d-flex flex-column flex-sm-row gap-2 justify-content-between align-items-center">
                            <button type="submit" name="change_password" class="btn btn-primary">Save New Password</button>
                            <a href="profile.php" class="btn btn-outline-secondary">Back to Profile</a>
                        </div>
                    </form>

                    <div class="note-box mt-4">
                        <h6 class="mb-2">Security note</h6>
                        <p class="mb-0">Your password is stored securely in hashed form. If you forget it, use the password reset flow from the login page.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({ duration: 700, once: true });
    $(document).ready(function () {
        $('#sidebarCollapse').on('click', function () {
            $('#sidebar').toggleClass('active');
            $('#content').toggleClass('active');
        });
    });
</script>
