<?php
include 'includes/header.php';
// Get admin info
$admin_id = $_SESSION['user_id'];
$admin_query = "SELECT * FROM users WHERE id = '$admin_id'";
$admin_result = mysqli_query($conn, $admin_query);
$admin = mysqli_fetch_assoc($admin_result);

// Get statistics
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users"))['count'];
$total_students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role='student'"))['count'];
$total_instructors = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role='instructor'"))['count'];
$total_admins = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role='admin' AND approved=1"))['count'];
$pending_instructors = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role='instructor' AND approved=0"))['count'];

$total_courses = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM courses"))['count'];
$published_courses = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM courses WHERE status='published'"))['count'];
$draft_courses = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM courses WHERE status='draft'"))['count'];

$total_enrollments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM enrollments"))['count'];
$active_enrollments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM enrollments WHERE status='active'"))['count'];

$total_quizzes = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM quizzes"))['count'];
$total_reviews = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM reviews"))['count'];
?>

<style>
    /* dashboard-specific components */
    .stat-card { border: none; border-radius: 15px; padding: 25px; background: #fff; box-shadow: 0 5px 15px rgba(0,0,0,0.05); transition: 0.3s; height: 100%; }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 12px 25px rgba(0,0,0,0.1); }
    .stat-card .card-body { display: flex; align-items: center; justify-content: space-between; }
    .stat-card h3 { font-size: 2.5rem; font-weight: 700; margin-bottom: 5px; }
    .stat-card p { margin-bottom: 0; font-weight: 500; color: #666; }
    .stat-icon { width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: white; }
    .bg-primary-gradient { background: linear-gradient(135deg, #6c63ff, #8a84ff); }
    .bg-success-gradient { background: linear-gradient(135deg, #2ed573, #7bed9f); }
    .bg-warning-gradient { background: linear-gradient(135deg, #ffa726, #ffb74d); }
    .bg-info-gradient { background: linear-gradient(135deg, #3742fa, #5352ed); }
    .activity-card { background: white; border-radius: 15px; border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.05); transition: 0.3s; }
    .activity-card:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
    .activity-item { padding: 15px; border-bottom: 1px solid #f8f9fa; transition: 0.2s; }
    .activity-item:hover { background-color: #f8f9fa; }
    .activity-item:last-child { border-bottom: none; }
    .activity-avatar { width: 40px; height: 40px; border-radius: 50%; background: var(--primary-color); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; margin-right: 15px; }
    /* welcome header & badges */
    .welcome-header { background: linear-gradient(135deg, #6c63ff, #8a84ff); color: white; padding: 30px; border-radius: 15px; margin-bottom: 30px; box-shadow: 0 8px 20px rgba(108, 99, 255, 0.2); }
    .welcome-header h2 { margin-bottom: 5px; font-weight: 700; }
    .welcome-header small { opacity: 0.9; font-size: 1rem; }
    .badge { font-size: 0.75rem; padding: 6px 12px; border-radius: 50px; font-weight: 600; }
</style>

<!-- main content begins -->
<div class="welcome-header">
        <h2>Welcome back, <?php echo htmlspecialchars($admin['name']); ?>! 👋</h2>
        <small>Administrator Dashboard • <?php echo date('l, F j, Y'); ?></small>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="card-body">
                    <div>
                        <h5 class="card-title text-primary">Total Users</h5>
                        <h3><?php echo $total_users; ?></h3>
                    </div>
                    <div class="stat-icon bg-primary-gradient">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="card-body">
                    <div>
                        <h5 class="card-title text-success">Students</h5>
                        <h3><?php echo $total_students; ?></h3>
                    </div>
                    <div class="stat-icon bg-success-gradient">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="card-body">
                    <div>
                        <h5 class="card-title text-warning">Instructors</h5>
                        <h3><?php echo $total_instructors; ?></h3>
                    </div>
                    <div class="stat-icon bg-warning-gradient">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="card-body">
                    <div>
                        <h5 class="card-title text-info">Pending Instructors</h5>
                        <h3><?php echo $pending_instructors; ?></h3>
                        <small class="text-muted">Need approval</small>
                    </div>
                    <div class="stat-icon bg-info-gradient">
                        <i class="fas fa-user-clock"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Course Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="card-body">
                    <div>
                        <h5 class="card-title text-primary">Total Courses</h5>
                        <h3><?php echo $total_courses; ?></h3>
                        <div class="mt-2">
                            <small class="text-success"><?php echo $published_courses; ?> published</small> |
                            <small class="text-warning"><?php echo $draft_courses; ?> draft</small>
                        </div>
                    </div>
                    <div class="stat-icon bg-primary-gradient">
                        <i class="fas fa-book"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card">
                <div class="card-body">
                    <div>
                        <h5 class="card-title text-success">Enrollments</h5>
                        <h3><?php echo $total_enrollments; ?></h3>
                        <div class="mt-2">
                            <small class="text-primary"><?php echo $active_enrollments; ?> active</small>
                        </div>
                    </div>
                    <div class="stat-icon bg-success-gradient">
                        <i class="fas fa-user-plus"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card">
                <div class="card-body">
                    <div>
                        <h5 class="card-title text-info">Quizzes & Reviews</h5>
                        <h3><?php echo $total_quizzes; ?></h3>
                        <div class="mt-2">
                            <small class="text-warning"><?php echo $total_reviews; ?> reviews</small>
                        </div>
                    </div>
                    <div class="stat-icon bg-info-gradient">
                        <i class="fas fa-question-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <div class="col-md-6">
            <div class="activity-card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-user-check me-2 text-primary"></i>Recent Instructor Requests</h5>
                </div>
                <div class="card-body p-0">
                    <?php
                    $recent_requests = mysqli_query($conn, "SELECT ir.*, u.name, u.email FROM instructor_requests ir JOIN users u ON ir.user_id = u.id WHERE ir.status = 'pending' ORDER BY ir.requested_at DESC LIMIT 5");
                    if(mysqli_num_rows($recent_requests) > 0){
                        while($request = mysqli_fetch_assoc($recent_requests)){
                            $initials = strtoupper(substr($request['name'], 0, 1));
                            echo '<div class="activity-item d-flex align-items-center">';
                            echo '<div class="activity-avatar">' . $initials . '</div>';
                            echo '<div class="flex-grow-1">';
                            echo '<strong class="text-dark">' . htmlspecialchars($request['name']) . '</strong><br>';
                            echo '<small class="text-muted">' . htmlspecialchars($request['email']) . '</small>';
                            echo '</div>';
                            echo '<a href="instructor_requests.php" class="btn btn-sm btn-outline-primary">Review</a>';
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="text-center py-4">';
                        echo '<i class="fas fa-check-circle fa-2x text-success mb-2"></i>';
                        echo '<p class="text-muted mb-0">No pending instructor requests.</p>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="activity-card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-book me-2 text-success"></i>Recent Courses</h5>
                </div>
                <div class="card-body p-0">
                    <?php
                    $recent_courses = mysqli_query($conn, "SELECT c.*, u.name as instructor_name FROM courses c JOIN users u ON c.instructor_id = u.id ORDER BY c.created_at DESC LIMIT 5");
                    if(mysqli_num_rows($recent_courses) > 0){
                        while($course = mysqli_fetch_assoc($recent_courses)){
                            $status_class = $course['status'] == 'published' ? 'success' : 'warning';
                            $status_text = ucfirst($course['status']);
                            echo '<div class="activity-item d-flex align-items-center">';
                            echo '<div class="activity-avatar bg-' . $status_class . '">';
                            echo '<i class="fas fa-book"></i>';
                            echo '</div>';
                            echo '<div class="flex-grow-1">';
                            echo '<strong class="text-dark">' . htmlspecialchars($course['title']) . '</strong><br>';
                            echo '<small class="text-muted">by ' . htmlspecialchars($course['instructor_name']) . '</small>';
                            echo '</div>';
                            echo '<span class="badge bg-' . $status_class . '">' . $status_text . '</span>';
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="text-center py-4">';
                        echo '<i class="fas fa-book fa-2x text-muted mb-2"></i>';
                        echo '<p class="text-muted mb-0">No courses found.</p>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.opacity-75 { opacity: 0.75; }
</style>

<?php include 'includes/footer.php'; ?>