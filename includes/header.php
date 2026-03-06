<!--Aa file ma apne Navbar banavishu 
jema Login thaya pachi "My Dashboard" dekhase ane Login pehla "Login/Register".
-->
<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LearnsDecode - Master Your Skills</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; }
        .navbar { background-color: #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .btn-primary { background-color: #6c63ff; border: none; }
        .btn-primary:hover { background-color: #5751d9; }
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
                <li class="nav-item"><a class="nav-link" href="index.php">Courses</a></li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item"><a class="nav-link fw-bold" href="student/dashboard.php text-primary">My Dashboard</a></li>
                    <li class="nav-item"><a class="btn btn-outline-danger btn-sm ms-lg-3" href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                    <li class="nav-item"><a class="btn btn-primary btn-sm ms-lg-3 text-white" href="register.php">Join for Free</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>