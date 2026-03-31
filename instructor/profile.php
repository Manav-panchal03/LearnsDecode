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

$profile_q = mysqli_query($conn, "SELECT u.name, u.email, p.expertise, p.bio, p.profile_pic FROM users u LEFT JOIN instructor_profiles p ON u.id = p.user_id WHERE u.id = $instructor_id");
$profile_data = mysqli_fetch_assoc($profile_q);

$p_img = ($profile_data && !empty($profile_data['profile_pic']) && $profile_data['profile_pic'] != 'default-avatar.png')
        ? "../uploads/profile/" . $profile_data['profile_pic']
        : "https://ui-avatars.com/api/?name=" . urlencode($profile_data['name']) . "&background=6c63ff&color=fff";

$masked_password = '••••••••••••';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Profile | LearnsDecode</title>
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
        .profile-card { border: none; border-radius: 25px; padding: 35px; background: #fff; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        .detail-label { font-size: 0.8rem; font-weight: 700; color: #6b6f82; text-transform: uppercase; letter-spacing: 0.08em; }
        .detail-value { font-size: 1rem; color: #222; }
        .password-box .form-control { background: #f8f9ff; }
        .button-row a { min-width: 180px; }
        .profile-picture { width: 120px; height: 120px; object-fit: cover; border-radius: 30px; }
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
        <div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-down">
            <div>
                <h2 class="fw-bold">Instructor Profile</h2>
                <p class="text-muted mb-0">Review your account details and update your password anytime.</p>
            </div>
            <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill">Back to Dashboard</a>
        </div>

        <div class="profile-card" data-aos="fade-up">
            <div class="row g-4">
                <div class="col-lg-4 text-center border-end">
                    <img src="<?= $p_img ?>" alt="Profile Picture" class="profile-picture mb-4 shadow-sm">
                    <h4 class="fw-bold mb-1"><?= htmlspecialchars($profile_data['name']) ?></h4>
                    <p class="text-muted mb-3">Instructor</p>
                    <a href="change_password.php" class="btn btn-primary rounded-pill px-4 py-2">Change Password</a>
                </div>
                <div class="col-lg-8">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <div class="detail-label">Full Name</div>
                                <div class="detail-value"><?= htmlspecialchars($profile_data['name']) ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <div class="detail-label">Email Address</div>
                                <div class="detail-value"><?= htmlspecialchars($profile_data['email']) ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <div class="detail-label">Expertise</div>
                                <div class="detail-value"><?= htmlspecialchars($profile_data['expertise'] ?: 'Not set yet') ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <div class="detail-label">Password</div>
                                <div class="input-group password-box">
                                    <input id="passwordField" type="password" class="form-control bg-light border-0" value="<?= $masked_password ?>" readonly>
                                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword()" id="togglePasswordBtn">Show</button>
                                </div>
                                <small class="text-muted">Password is kept secure. Change it on the password screen.</small>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="detail-label">Bio</div>
                            <div class="detail-value" style="white-space: pre-wrap; min-height: 120px; padding: 18px; background: #f8f9ff; border-radius: 16px; border: 1px solid #e8ebf4;"><?= nl2br(htmlspecialchars($profile_data['bio'] ?: 'No bio available yet.')) ?></div>
                        </div>
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

    function togglePassword() {
        const field = document.getElementById('passwordField');
        const btn = document.getElementById('togglePasswordBtn');
        if(field.type === 'password') {
            field.type = 'text';
            btn.textContent = 'Hide';
        } else {
            field.type = 'password';
            btn.textContent = 'Show';
        }
    }

    $(document).ready(function () {
        $('#sidebarCollapse').on('click', function () {
            $('#sidebar').toggleClass('active');
            $('#content').toggleClass('active');
        });
    });
</script>
