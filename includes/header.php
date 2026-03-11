<!--Aa file ma apne Navbar banavishu 
jema Login thaya pachi "My Dashboard" dekhase ane Login pehla "Login/Register".
-->
<?php
// start session when header is included, but do not force login here
if(session_status() === PHP_SESSION_NONE){
    session_start();
}
// load configuration so BASE_URL is available for links
require_once __DIR__ . '/../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LearnsDecode - Master Your Skills</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; }
        .navbar { background-color: #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .btn-primary { background-color: #6c63ff; border: none; }
        .btn-primary:hover { background-color: #5751d9; }
        .dashboard-link { color: #6c63ff !important; font-weight: 600; }
        .dashboard-link:hover { color: #5751d9 !important; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="index.php" style="font-size: 1.5rem;">Learns<span class="text-dark">Decode</span></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item"><a class="nav-link" href="courses.php">Courses</a></li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <?php if($_SESSION['user_role'] == 'admin'): ?>
                        <li class="nav-item"><a class="nav-link dashboard-link" href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/admin/dashboard.php"><i class="fas fa-tachometer-alt me-1"></i>Admin Dashboard</a></li>
                    <?php elseif($_SESSION['user_role'] == 'instructor'): ?>
                        <li class="nav-item"><a class="nav-link dashboard-link" href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/instructor/dashboard.php"><i class="fas fa-chalkboard-teacher me-1"></i>Instructor Dashboard</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link dashboard-link" href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/student/dashboard.php"><i class="fas fa-graduation-cap me-1"></i>My Dashboard</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="btn btn-outline-danger btn-sm ms-lg-3" href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/logout.php" onclick="return confirm('Are you sure you want to logout?')"><i class="fas fa-sign-out-alt me-1"></i>Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/login.php">Login</a></li>
                    <li class="nav-item"><a class="btn btn-primary btn-sm ms-lg-3 text-white" href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/register.php"><i class="fas fa-user-plus me-1"></i>Join for Free</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>