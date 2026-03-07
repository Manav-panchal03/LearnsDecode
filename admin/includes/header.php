<?php
// start session and protect admin panel
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/session_utils.php';

// activate admin role if it exists
activateRole('admin');

// make sure only admins get in
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin'){
    header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/login.php");
    exit();
}

// determine active page for sidebar highlighting
$activePage = basename($_SERVER['PHP_SELF']);
$admin_name = $_SESSION['user_name'];
$admin_email = $_SESSION['user_email'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | LearnsDecode</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --sidebar-width: 280px;
            --primary-color: #6c63ff;
            --dark-bg: #1e1e2d;
        }

        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; overflow-x: hidden; }

        /* --- SIDEBAR & CONTENT --- */
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
    </style>
</head>
<body>

<!-- <nav class="navbar navbar-custom d-flex justify-content-between">
    <button id="sidebarCollapse" class="btn btn-outline-secondary btn-sm me-2"><i class="fas fa-bars"></i></button>
    <a class="navbar-brand fw-bold text-primary" href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/admin/dashboard.php">LearnsDecode Admin</a>
    <div class="user-info d-flex align-items-center">
        <div class="text-end me-3 d-none d-sm-block">
            <div class="fw-bold lh-1"><?= $admin_name; ?></div>
            <small class="text-muted" style="font-size: 11px;">Administrator</small>
        </div>
        <img src="https://ui-avatars.com/api/?name=<?= urlencode($admin_name) ?>&background=6c63ff&color=fff" class="rounded-circle shadow-sm border" width="45" height="45" style="object-fit: cover;">
    </div>
</nav> -->

<nav id="sidebar">
    <div class="sidebar-header">
        <h3 class="fw-bold text-white mb-0">Learns<span style="color:var(--primary-color)">Decode</span></h3>
        <small>Admin Panel</small>
    </div>
    <div class="nav-links">
        <a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>"><i class="fas fa-th-large"></i> Dashboard</a>
        <a href="manage_users.php" class="<?= basename($_SERVER['PHP_SELF']) == 'manage_users.php' ? 'active' : '' ?>"><i class="fas fa-users"></i> Manage Users</a>
        <a href="manage_courses.php" class="<?= basename($_SERVER['PHP_SELF']) == 'manage_courses.php' ? 'active' : '' ?>"><i class="fas fa-book-open"></i> Manage Courses</a>
        <a href="instructor_requests.php" class="<?= basename($_SERVER['PHP_SELF']) == 'instructor_requests.php' ? 'active' : '' ?>"><i class="fas fa-user-check"></i> Instructor Requests</a>
        <a href="manage_categories.php" class="<?= basename($_SERVER['PHP_SELF']) == 'manage_categories.php' ? 'active' : '' ?>"><i class="fas fa-tags"></i> Categories</a>
        <a href="reports.php" class="<?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : '' ?>"><i class="fas fa-chart-bar"></i> Reports</a>
        <hr style="border-color: rgba(255,255,255,0.1)">
        <a href="profile.php" class="<?= basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : '' ?>"><i class="fas fa-user-circle"></i> Profile</a>
        <a href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/logout.php" class="text-danger" onclick="return confirm('Are you sure you want to logout?')"><i class="fas fa-power-off"></i> Logout</a>
    </div>
</nav>

<div id="content">
    <nav class="navbar navbar-custom d-flex justify-content-between">
        <button type="button" id="sidebarCollapse">
            <i class="fas fa-align-left"></i>
        </button>
        <div class="user-info d-flex align-items-center">
            <div class="text-end me-3 d-none d-sm-block">
                <div class="fw-bold lh-1"><?= $admin_name; ?></div>
                <small class="text-muted" style="font-size: 11px;">Administrator</small>
            </div>
            <img src="https://ui-avatars.com/api/?name=<?= urlencode($admin_name) ?>&background=6c63ff&color=fff" class="rounded-circle shadow-sm border" width="45" height="45" style="object-fit: cover;">
        </div>
    </nav>