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
            --sidebar-width: 240px;
            --primary-color: #6c63ff;
            --dark-bg: #1e1e2d;
        }

        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; overflow-x: hidden; overflow-y: scroll; }

        /* --- SIDEBAR & CONTENT --- */
        #sidebar { width: var(--sidebar-width); height: calc(100vh - 80px); position: fixed; left: 0; top: 80px; background: #f4f7f6; color: #333; transition: all 0.3s ease-in-out; z-index: 2000; box-shadow: 2px 0 5px rgba(0,0,0,0.05); border-right: 1px solid #e9ecef; }
        #sidebar.active { left: calc(-1 * var(--sidebar-width)); }
        .sidebar-header { padding: 30px 20px; text-align: center; background: #ffffff; border-bottom: 1px solid #e9ecef; color: #495057; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .nav-links { padding: 20px 0; background: #ffffff; margin: 10px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .nav-links a { padding: 12px 25px; display: flex; align-items: center; color: #495057; text-decoration: none; transition: 0.2s; border-left: 4px solid transparent; border-radius: 0 4px 4px 0; margin: 2px 0; }
        .nav-links a i { width: 30px; font-size: 1.1rem; color: #6c757d; }
        .nav-links a:hover, .nav-links a.active { background: #f8f9fa; color: #007bff; border-left: 4px solid #007bff; }
        #content { width: calc(100% - var(--sidebar-width)); margin-left: var(--sidebar-width); transition: all 0.3s ease-in-out; min-height: 100vh; padding: 30px; padding-top: 80px; }
        #content.active { width: 100%; margin-left: 0; }
        .navbar-custom { background: #ffffff; padding: 15px 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .table-3d { box-shadow: 0 4px 8px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden; }
        #sidebarCollapse { background: var(--primary-color); border: none; color: white; padding: 5px 12px; border-radius: 5px; }
    </style>
</head>
<body>

<!-- <nav class="navbar navbar-custom d-flex justify-content-between">
    <button id="sidebarCollapse" class="btn btn-outline-secondary btn-sm me-2"><i class="fas fa-bars"></i></button>
    <a class="navbar-brand fw-bold text-primary" href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/admin/dashboard.php">LearnsDecode Admin</a>
    <div class="d-flex gap-2 align-items-center">
        <a class="btn btn-outline-secondary btn-sm" href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/admin/profile.php"><i class="fas fa-user-circle me-1"></i>Profile</a>
        <a class="btn btn-outline-danger btn-sm" href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/logout.php" onclick="return confirm('Are you sure you want to logout?')"><i class="fas fa-sign-out-alt me-1"></i>Logout</a>
    </div>
</nav> -->

<nav class="navbar navbar-custom d-flex justify-content-between">

    <div class="d-flex align-items-center">
        <button id="sidebarCollapse" class="btn btn-outline-secondary btn-sm me-2">
            <i class="fas fa-bars"></i>
        </button>

        <a class="navbar-brand fw-bold text-primary mb-0"
           href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/admin/dashboard.php">
           LearnsDecode Admin
        </a>
    </div>

    <div class="d-flex gap-2 align-items-center">
        <a class="btn btn-outline-secondary btn-sm"
           href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/admin/profile.php">
           <i class="fas fa-user-circle me-1"></i>Profile
        </a>

        <a class="btn btn-outline-danger btn-sm"
           href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/logout.php"
           onclick="return confirm('Are you sure you want to logout?')">
           <i class="fas fa-sign-out-alt me-1"></i>Logout
        </a>
    </div>

</nav>

<div id="sidebar">
    <div class="sidebar-header">
        <h5>Admin Panel</h5>
    </div>
    <div class="nav-links">
        <a href="dashboard.php" class="<?= $activePage == 'dashboard.php' ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="manage_users.php" class="<?= $activePage == 'manage_users.php' ? 'active' : '' ?>">
            <i class="fas fa-users"></i> Manage Users
        </a>
        <a href="manage_courses.php" class="<?= $activePage == 'manage_courses.php' ? 'active' : '' ?>">
            <i class="fas fa-book"></i> Manage Courses
        </a>
        <a href="instructor_requests.php" class="<?= $activePage == 'instructor_requests.php' ? 'active' : '' ?>">
            <i class="fas fa-user-check"></i> Instructor Requests
        </a>
        <a href="manage_categories.php" class="<?= $activePage == 'manage_categories.php' ? 'active' : '' ?>">
            <i class="fas fa-tags"></i> Categories
        </a>
        <a href="reports.php" class="<?= $activePage == 'reports.php' ? 'active' : '' ?>">
            <i class="fas fa-chart-bar"></i> Reports
        </a>
    </div>
</div>

<div id="content">