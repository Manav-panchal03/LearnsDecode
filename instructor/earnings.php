<?php
session_start();
require '../config/config.php';
require_once '../includes/session_utils.php';

// activate instructor role
activateRole('instructor');

// Security Check
if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'instructor'){
    header("Location: ../login.php");
    exit();
}

$instructor_id = $_SESSION['user_id'];

/// --- DYNAMIC EARNINGS STATS ---
// COUNT(e.id) use karyu che jethi ambiguity dur thai jay
$stats_q = mysqli_query($conn, "SELECT SUM(e.instructor_revenue) as total_earned, COUNT(e.id) as total_sales 
                                FROM enrollments e 
                                JOIN courses c ON e.course_id = c.id 
                                WHERE c.instructor_id = $instructor_id AND e.payment_status = 'completed'");

if (!$stats_q) {
    die("Query Failed: " . mysqli_error($conn));
}

$stats_data = mysqli_fetch_assoc($stats_q);

// Instructor Profile fetch for Top Bar
$profile_q = mysqli_query($conn ,"SELECT u.name, p.profile_pic FROM users u 
                                LEFT JOIN instructor_profiles p ON u.id = p.user_id 
                                WHERE u.id = $instructor_id");
$profile_data = mysqli_fetch_assoc($profile_q);

$p_img = (!empty($profile_data['profile_pic']) && $profile_data['profile_pic'] != 'default-avatar.png') 
        ? "../uploads/profile/".$profile_data['profile_pic'] 
        : "https://ui-avatars.com/api/?name=".urlencode($profile_data['name'])."&background=6c63ff&color=fff";

// Course list for filter dropdown
$courses_filter_q = mysqli_query($conn, "SELECT id, title FROM courses WHERE instructor_id = $instructor_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Earnings | LearnsDecode</title>
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

        /* --- SIDEBAR (Exact Dashboard Match) --- */
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

        /* --- STAT CARDS (Exact Match) --- */
        .stat-card { border: none; border-radius: 15px; padding: 25px; background: #fff; box-shadow: 0 5px 15px rgba(0,0,0,0.05); border-bottom: 4px solid var(--primary-color); transition: 0.3s; }
        .stat-card:hover { transform: translateY(-5px); }

        /* --- TABLE & FILTER UI --- */
        .revenue-card { background: white; border-radius: 20px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow: hidden; }
        .filter-section { background: white; border-radius: 15px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); margin-bottom: 25px; }
        .table thead { background: #f8f9fa; }
        .table th { border: none; padding: 15px 20px; color: #555; font-size: 13px; text-transform: uppercase; }
        .table td { padding: 18px 20px; vertical-align: middle; border-color: #f1f1f1; }
        .amount-green { color: #2ecc71; font-weight: 700; }
        .form-control, .form-select { border-radius: 10px; border: 1px solid #e0e0e0; padding: 10px 15px; }
        .form-control:focus { border-color: var(--primary-color); box-shadow: none; }
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
        <a href="broadcast_center.php"><i class="fas fa-envelope"></i> Broadcast Center </a>
        <a href="manage_students.php"><i class="fas fa-users"></i> Students</a>
        <a href="manage_certificates.php"><i class="fas fa-certificate"></i> Certificates</a>
        <a href="earnings.php" class="active"><i class="fas fa-user-circle"></i> Earnings</a>
        <a href="analytics.php"><i class="fas fa-chart-line"></i> Analytics</a>
        <hr style="border-color: rgba(255,255,255,0.1)">
        <a href="../logout.php" class="text-danger"><i class="fas fa-power-off"></i> Logout</a>
    </div>
</nav>

<div id="content">
    <nav class="navbar navbar-custom d-flex justify-content-between">
        <button type="button" id="sidebarCollapse">
            <i class="fas fa-align-left"></i>
        </button>
        <div class="user-info d-flex align-items-center">
            <div class="text-end me-3 d-none d-sm-block">
                <div class="fw-bold lh-1"><?= $profile_data['name']; ?></div>
                <small class="text-muted" style="font-size: 11px;">Instructor</small>
            </div>
            <img src="<?= $p_img ?>" class="rounded-circle shadow-sm border" width="45" height="45">
        </div>
    </nav>

    <div class="container-fluid p-4">
        <div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-down">
            <h2 class="fw-bold">Financial Analytics</h2>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-4" data-aos="zoom-in" data-aos-delay="100">
                <div class="stat-card">
                    <h6 class="text-muted small text-uppercase">Net Earnings</h6>
                    <h2 class="fw-bold mb-0">₹<?= number_format($stats_data['total_earned'] ?? 0, 2); ?></h2>
                </div>
            </div>
            <div class="col-md-4" data-aos="zoom-in" data-aos-delay="200">
                <div class="stat-card" style="border-color: #38b2ac;">
                    <h6 class="text-muted small text-uppercase">Total Sales</h6>
                    <h2 class="fw-bold mb-0"><?= sprintf("%02d", $stats_data['total_sales'] ?? 0); ?></h2>
                </div>
            </div>
            <div class="col-md-4" data-aos="zoom-in" data-aos-delay="300">
                <div class="stat-card" style="border-color: #ff9800;">
                    <h6 class="text-muted small text-uppercase">Commission Rate</h6>
                    <h2 class="fw-bold mb-0">70%</h2>
                </div>
            </div>
        </div>

        <div class="filter-section" data-aos="fade-up">
            <div class="row g-3">
                <div class="col-md-5">
                    <input type="text" id="searchStudent" class="form-control" placeholder="Search student name...">
                </div>
                <div class="col-md-4">
                    <select id="courseFilter" class="form-select">
                        <option value="">All Courses</option>
                        <?php while($c = mysqli_fetch_assoc($courses_filter_q)): ?>
                            <option value="<?= $c['id'] ?>"><?= $c['title'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <!-- <button class="btn btn-primary w-100 rounded-3 py-2 fw-bold" onclick="loadEarnings()">
                        <i class="fas fa-filter me-2"></i> Apply Filter
                    </button> -->
                </div>
            </div>
        </div>

        <div class="revenue-card" data-aos="fade-up">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Price</th>
                            <th>Your Earning</th>
                            <th class="text-end">Date</th>
                        </tr>
                    </thead>
                    <tbody id="earningsTableBody">
                        </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<script>
    AOS.init({ duration: 800, once: true });

    function loadEarnings() {
        // Table ma loading effect dekhadv mate (Optional)
        $('#earningsTableBody').css('opacity', '0.5');
        
        let student = $('#searchStudent').val();
        let courseId = $('#courseFilter').val();

        $.ajax({
            url: '../api/fetch_instructor_earnings.php', // Path barobar che ke nai check karjo
            type: 'POST',
            data: { 
                student: student, 
                course_id: courseId 
            },
            success: function(response) {
                $('#earningsTableBody').html(response).css('opacity', '1');
            },
            error: function() {
                $('#earningsTableBody').html("<tr><td colspan='5' class='text-center text-danger'>Error loading data.</td></tr>");
            }
        });
    }

    $(document).ready(function () {
        // Initial Load
        loadEarnings();

        // 1. REAL-TIME SEARCH: Student na naam par type karta j search thase
        $('#searchStudent').on('input', function() {
            loadEarnings();
        });

        // 2. COURSE FILTER: Dropdown change karta j search thase
        $('#courseFilter').on('change', function() {
            loadEarnings();
        });

        // Sidebar Toggle Logic
        $('#sidebarCollapse').on('click', function () {
            $('#sidebar, #content').toggleClass('active');
        });
    });
</script>
</body>
</html>