<?php 
session_start();
require_once '../config/config.php';

// Login Check
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'instructor'){
    header("Location: ../login.php");
    exit();
}

$inst_id = $_SESSION['user_id'];

// --- COURSE SELECTION LOGIC ---
$selected_course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

// All courses fetch karo dropdown mate
$all_courses_q = mysqli_query($conn, "SELECT id, title FROM courses WHERE instructor_id = '$inst_id'");

// Analytics Query (Dynamic based on selection)
$where_clause = "WHERE c.instructor_id = '$inst_id'";
if($selected_course_id > 0) {
    $where_clause .= " AND c.id = '$selected_course_id'";
}

$stats_q = mysqli_query($conn, "SELECT 
    COUNT(r.id) as total_reviews, 
    AVG(r.rating) as avg_rating,
    (SELECT COUNT(id) FROM courses WHERE instructor_id = '$inst_id') as total_courses
    FROM reviews r 
    JOIN courses c ON r.course_id = c.id 
    $where_clause");
$stats = mysqli_fetch_assoc($stats_q);

$avg_rating = round($stats['avg_rating'], 1) ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics | LearnsDecode</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        body { 
            background: #f4f7fe; 
            font-family: 'Poppins', sans-serif; 
            color: #2b3674; 
        }
        .stat-card { border: none; border-radius: 20px; transition: 0.3s; background: #fff; }
        .stat-card:hover { transform: translateY(-10px); box-shadow: 0 15px 30px rgba(67, 24, 255, 0.1); }
        .icon-box { width: 60px; height: 60px; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
        .review-row { border-radius: 15px; background: #fff; margin-bottom: 15px; transition: 0.3s; border-left: 5px solid #4318ff; }
        .form-select { border-radius: 12px; padding: 10px 20px; border: 1px solid #e0e5f2; font-weight: 500; }
        .btn-back { background: #fff; color: #4318ff; border: none; border-radius: 12px; padding: 10px 20px; font-weight: 600; transition: 0.3s; }
        .btn-back:hover { background: #4318ff; color: #fff; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeInDown">
        <div>
            <a href="dashboard.php" class="btn btn-back shadow-sm mb-3">
                <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
            </a>
            <h2 class="fw-bold">Course Analytics</h2>
        </div>
        
        <div class="col-md-4">
            <form id="courseFilterForm">
                <label class="small fw-bold text-muted mb-2">FILTER BY COURSE</label>
                <select name="course_id" class="form-select shadow-sm" onchange="this.form.submit()">
                    <option value="0">All Courses (Overall)</option>
                    <?php while($c = mysqli_fetch_assoc($all_courses_q)): ?>
                        <option value="<?= $c['id'] ?>" <?= ($selected_course_id == $c['id']) ? 'selected' : '' ?>>
                            <?= $c['title'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </form>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-4 animate__animated animate__zoomIn">
            <div class="card stat-card p-4 shadow-sm">
                <div class="d-flex align-items-center">
                    <div class="icon-box bg-primary text-white shadow"><i class="fas fa-star"></i></div>
                    <div class="ms-4">
                        <p class="text-muted mb-0 small fw-bold">AVG RATING</p>
                        <h2 class="fw-bold mb-0"><?= $avg_rating ?></h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 animate__animated animate__zoomIn" style="animation-delay: 0.1s;">
            <div class="card stat-card p-4 shadow-sm">
                <div class="d-flex align-items-center">
                    <div class="icon-box bg-success text-white shadow"><i class="fas fa-comment-dots"></i></div>
                    <div class="ms-4">
                        <p class="text-muted mb-0 small fw-bold">TOTAL REVIEWS</p>
                        <h2 class="fw-bold mb-0"><?= $stats['total_reviews'] ?></h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 animate__animated animate__zoomIn" style="animation-delay: 0.2s;">
            <div class="card stat-card p-4 shadow-sm">
                <div class="d-flex align-items-center">
                    <div class="icon-box bg-warning text-dark shadow"><i class="fas fa-graduation-cap"></i></div>
                    <div class="ms-4">
                        <p class="text-muted mb-0 small fw-bold">COURSES</p>
                        <h2 class="fw-bold mb-0"><?= $stats['total_courses'] ?></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7 animate__animated animate__fadeInLeft">
            <div class="card border-0 shadow-sm p-4" style="border-radius: 20px;">
                <h5 class="fw-bold mb-4">Rating Distribution</h5>
                <div style="height: 300px; position: relative;">
                    <canvas id="ratingChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-5 animate__animated animate__fadeInRight">
            <div class="card border-0 shadow-sm p-4" style="border-radius: 20px; min-height: 400px;">
                <h5 class="fw-bold mb-4">Student Feedback</h5>
                <div style="max-height: 350px; overflow-y: auto; padding-right: 5px;">
                    <?php
                    $rev_list_q = mysqli_query($conn, "SELECT r.*, u.name as s_name, c.title as c_title 
                                                    FROM reviews r 
                                                    JOIN users u ON r.user_id = u.id 
                                                    JOIN courses c ON r.course_id = c.id 
                                                    $where_clause 
                                                    ORDER BY r.created_at DESC");
                    if(mysqli_num_rows($rev_list_q) > 0):
                        while($r = mysqli_fetch_assoc($rev_list_q)):
                    ?>
                    <div class="review-row p-3 shadow-sm mb-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="fw-bold mb-0"><?= $r['s_name'] ?></h6>
                                <span class="badge bg-light text-primary small mb-2"><?= $r['c_title'] ?></span>
                            </div>
                            <small class="text-muted"><?= date('d M', strtotime($r['created_at'])) ?></small>
                        </div>
                        <div class="text-warning mb-1">
                            <?php for($i=1; $i<=5; $i++) echo ($i<=$r['rating']) ? '<i class="fas fa-star" style="font-size: 12px;"></i>' : '<i class="far fa-star" style="font-size: 12px;"></i>'; ?>
                        </div>
                        <p class="small text-muted mb-0">"<?= $r['comment'] ?>"</p>
                    </div>
                    <?php endwhile; else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-box-open fa-3x text-light mb-3"></i>
                            <p class="text-muted">No reviews found for this selection.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // --- Rating Distribution Data ---
    <?php
    $dist_q = mysqli_query($conn, "SELECT rating, COUNT(*) as count FROM reviews r JOIN courses c ON r.course_id = c.id $where_clause GROUP BY rating");
    $counts = [1=>0, 2=>0, 3=>0, 4=>0, 5=>0];
    while($d = mysqli_fetch_assoc($dist_q)) { $counts[$d['rating']] = $d['count']; }
    ?>

    const ctx = document.getElementById('ratingChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar', // Bar chart vadhare saru lagse comparison mate
        data: {
            labels: ['1 Star', '2 Stars', '3 Stars', '4 Stars', '5 Stars'],
            datasets: [{
                label: 'Number of Reviews',
                data: [<?= implode(',', $counts) ?>],
                backgroundColor: ['#ff4d4d', '#ffa64d', '#ffdb4d', '#4318ff', '#05cd99'],
                borderRadius: 10,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { display: false } },
                x: { grid: { display: false } }
            }
        }
    });

    // Alert on Filter
    const urlParams = new URLSearchParams(window.location.search);
    if(urlParams.has('course_id')) {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        });
        Toast.fire({ icon: 'info', title: 'Analytics Filtered' });
    }
</script>
</body>
</html>