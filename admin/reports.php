<?php
include 'includes/header.php';
// Get comprehensive statistics
$stats = [];

// User statistics
$stats['users'] = [
    'total' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users"))['count'],
    'students' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role='student'"))['count'],
    'instructors' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role='instructor'"))['count'],
    'admins' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role='admin' AND approved=1"))['count'],
    'pending_admins' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role='admin' AND approved=0"))['count'],
    'active_users' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE approved=1"))['count']
];

// Course statistics
$stats['courses'] = [
    'total' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM courses"))['count'],
    'published' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM courses WHERE status='published'"))['count'],
    'draft' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM courses WHERE status='draft'"))['count'],
    'free' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM courses WHERE price=0"))['count'],
    'paid' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM courses WHERE price>0"))['count'],
    'avg_price' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(price) as avg FROM courses WHERE price>0"))['avg']
];

// Enrollment statistics
$stats['enrollments'] = [
    'total' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM enrollments"))['count'],
    'active' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM enrollments WHERE status='active'"))['count'],
    'completed' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM enrollments WHERE status='completed'"))['count']
];

// Engagement statistics
$stats['engagement'] = [
    'quizzes' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM quizzes"))['count'],
    'quiz_attempts' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM quiz_attempts"))['count'],
    'reviews' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM reviews"))['count'],
    'avg_rating' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(rating) as avg FROM reviews"))['avg']
];

// Potential Revenue
$revenue_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(c.price) as total FROM courses c JOIN enrollments e ON c.id = e.course_id"));
$total_potential = $revenue_data['total'] ?? 0;
$stats['revenue'] = [
    'potential' => $total_potential,
    'admin_share' => $total_potential * 0.30,
    'instructor_share' => $total_potential * 0.70
];

// Recent activity
$stats['recent'] = [
    'new_users' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"))['count'],
    'new_courses' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM courses WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"))['count'],
    'new_enrollments' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM enrollments WHERE enrolled_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"))['count']
];
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    .analytics-card { border: none; border-radius: 15px; transition: transform 0.3s ease, box-shadow 0.3s ease; background: #fff; }
    .analytics-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
    .stat-icon { width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; border-radius: 12px; font-size: 1.5rem; }
    .progress-thin { height: 6px; }
    .rating-trigger { transition: all 0.3s ease; padding: 10px; border-radius: 15px; cursor: pointer; }
    .rating-trigger:hover { background: rgba(255, 193, 7, 0.05); transform: scale(1.05); }
</style>

<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeInDown">
        <div>
            <h2 class="fw-bold text-dark"><i class="fas fa-chart-line me-2 text-primary"></i>Reports & Analytics</h2>
            <p class="text-muted">Detailed insight into your platform's growth</p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm"><i class="fas fa-chevron-left me-2"></i>Dashboard</a>
    </div>

    <div class="row mb-4 g-3">
        <div class="col-md-3 animate__animated animate__zoomIn" style="animation-delay: 0.1s;">
            <div class="card analytics-card p-3 shadow-sm">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-primary text-white me-3"><i class="fas fa-users"></i></div>
                    <div><h4 class="mb-0 fw-bold"><?php echo $stats['users']['total']; ?></h4><small class="text-muted">Total Users</small></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 animate__animated animate__zoomIn" style="animation-delay: 0.2s;">
            <div class="card analytics-card p-3 shadow-sm">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-success text-white me-3"><i class="fas fa-book-open"></i></div>
                    <div><h4 class="mb-0 fw-bold"><?php echo $stats['courses']['total']; ?></h4><small class="text-muted">Total Courses</small></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 animate__animated animate__zoomIn" style="animation-delay: 0.3s;">
            <div class="card analytics-card p-3 shadow-sm">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-warning text-white me-3"><i class="fas fa-user-check"></i></div>
                    <div><h4 class="mb-0 fw-bold"><?php echo $stats['enrollments']['total']; ?></h4><small class="text-muted">Total Enrollments</small></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 animate__animated animate__zoomIn" style="animation-delay: 0.4s;">
            <div class="card analytics-card p-3 shadow-sm">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-info text-white me-3"><i class="fas fa-hand-holding-usd"></i></div>
                    <div><h4 class="mb-0 fw-bold">₹<?php echo number_format($stats['revenue']['admin_share'], 2); ?></h4><small class="text-muted">Revenue (30% share)</small></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7 mb-4 animate__animated animate__fadeInLeft">
            <div class="card analytics-card shadow-sm p-4 h-100">
                <h5 class="fw-bold mb-4">User Distribution</h5>
                <canvas id="userTypeChart" height="200"></canvas>
            </div>
        </div>
        <div class="col-lg-5 mb-4 animate__animated animate__fadeInRight">
            <div class="card analytics-card shadow-sm p-4 h-100">
                <h5 class="fw-bold mb-4">Growth (Last 30 Days)</h5>
                <div class="mb-4">
                    <div class="d-flex justify-content-between small mb-1"><span>New Students</span><span class="fw-bold text-primary">+<?php echo $stats['recent']['new_users']; ?></span></div>
                    <div class="progress progress-thin"><div class="progress-bar bg-primary" style="width: 75%"></div></div>
                </div>
                <div class="mb-4">
                    <div class="d-flex justify-content-between small mb-1"><span>New Courses</span><span class="fw-bold text-success">+<?php echo $stats['recent']['new_courses']; ?></span></div>
                    <div class="progress progress-thin"><div class="progress-bar bg-success" style="width: 60%"></div></div>
                </div>
                <div class="mb-4">
                    <div class="d-flex justify-content-between small mb-1"><span>New Enrollments</span><span class="fw-bold text-warning">+<?php echo $stats['recent']['new_enrollments']; ?></span></div>
                    <div class="progress progress-thin"><div class="progress-bar bg-warning" style="width: 85%"></div></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4 animate__animated animate__fadeInUp">
            <div class="card analytics-card shadow-sm overflow-hidden h-100">
                <div class="card-header bg-dark text-white p-3"><h5 class="mb-0 fw-bold">Engagement Statistics</h5></div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-4 border-end">
                            <h3 class="text-primary fw-bold"><?php echo $stats['engagement']['quizzes']; ?></h3>
                            <p class="text-muted small">Total Quizzes</p>
                        </div>
                        <div class="col-6 mb-4">
                            <h3 class="text-success fw-bold"><?php echo $stats['engagement']['reviews']; ?></h3>
                            <p class="text-muted small">Total Reviews</p>
                        </div>
                        <div class="col-6 border-end">
                            <h3 class="text-info fw-bold"><?php echo $stats['engagement']['quiz_attempts']; ?></h3>
                            <p class="text-muted small">Quiz Attempts</p>
                        </div>
                        <div class="col-6 rating-trigger" data-bs-toggle="modal" data-bs-target="#courseRatingModal">
                            <h3 class="text-warning fw-bold mb-0"><?php echo number_format($stats['engagement']['avg_rating'] ?? 0, 1); ?>★</h3>
                            <p class="text-muted small mb-0">Avg Course Rating</p>
                            <span class="badge bg-warning text-dark mt-1 animate__animated animate__pulse animate__infinite" style="font-size: 0.6rem;"><i class="fas fa-eye me-1"></i> Tap to see</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4 animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
            <div class="card analytics-card shadow-sm h-100">
                <div class="card-header bg-primary text-white p-3 d-flex justify-content-between">
                    <h5 class="mb-0 fw-bold">Top Performing Categories</h5>
                    <i class="fas fa-trophy"></i>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr><th class="ps-3">Category</th><th>Courses</th><th>Enrollments</th></tr>
                        </thead>
                        <tbody>
                            <?php
                            $categories_query = "SELECT cat.name, cat.icon, COUNT(c.id) as course_count, COUNT(e.id) as enrollment_count 
                                                FROM categories cat LEFT JOIN courses c ON cat.id = c.category_id 
                                                LEFT JOIN enrollments e ON c.id = e.course_id GROUP BY cat.id 
                                                ORDER BY enrollment_count DESC LIMIT 4";
                            $categories_result = mysqli_query($conn, $categories_query);
                            while($category = mysqli_fetch_assoc($categories_result)):
                            ?>
                            <tr>
                                <td class="ps-3 fw-bold"><i class="<?php echo $category['icon']; ?> text-primary me-2"></i><?php echo htmlspecialchars($category['name']); ?></td>
                                <td><span class="badge bg-light text-dark rounded-pill"><?php echo $category['course_count']; ?></span></td>
                                <td class="fw-bold text-success"><?php echo $category['enrollment_count']; ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="courseRatingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg animate__animated animate__zoomIn" style="border-radius: 25px;">
            <div class="modal-header border-0 p-4">
                <h5 class="fw-bold mb-0"><i class="fas fa-star text-warning me-2"></i>Detailed Course Ratings</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 pt-0">
                <div class="table-responsive" style="max-height: 400px;">
                    <table class="table align-middle border-0">
                        <tbody>
                            <?php
                            $course_ratings_query = "SELECT c.title, AVG(r.rating) as avg_r, COUNT(r.id) as total_r FROM courses c 
                                                   LEFT JOIN reviews r ON c.id = r.course_id GROUP BY c.id HAVING total_r > 0 ORDER BY avg_r DESC";
                            $res = mysqli_query($conn, $course_ratings_query);
                            while($cr = mysqli_fetch_assoc($res)):
                                $percentage = ($cr['avg_r'] / 5) * 100;
                            ?>
                            <tr class="animate__animated animate__fadeInUp">
                                <td style="width: 40%;"><h6 class="fw-bold mb-0 text-truncate"><?= $cr['title'] ?></h6><small class="text-muted"><?= $cr['total_r'] ?> Reviews</small></td>
                                <td><div class="d-flex align-items-center"><div class="progress flex-grow-1 me-3" style="height: 8px; border-radius: 10px; background: #eee;"><div class="progress-bar bg-warning" style="width: <?= $percentage ?>%; border-radius: 10px;"></div></div><span class="fw-bold text-dark small"><?= round($cr['avg_r'], 1) ?></span></div></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const ctx = document.getElementById('userTypeChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Students', 'Instructors', 'Admins'],
        datasets: [{
            data: [<?php echo $stats['users']['students']; ?>, <?php echo $stats['users']['instructors']; ?>, <?php echo $stats['users']['admins']; ?>],
            backgroundColor: ['rgba(54, 162, 235, 0.7)', 'rgba(75, 192, 192, 0.7)', 'rgba(255, 206, 86, 0.7)'],
            borderWidth: 1, borderRadius: 8
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } } }
});
</script>
<?php include 'includes/footer.php'; ?>