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

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    /* dashboard-specific components */
    .stat-card { border: none; border-radius: 15px; padding: 25px; background: #fff; box-shadow: 0 5px 15px rgba(0,0,0,0.05); border-bottom: 4px solid var(--primary-color); transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
    .stat-card:hover { transform: translateY(-10px); box-shadow: 0 12px 25px rgba(0,0,0,0.1); }
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
    /* course card styles */
    .course-card { background: white; border-radius: 20px; overflow: hidden; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.05); transition: 0.3s; }
    .course-card:hover { transform: translateY(-5px); box-shadow: 0 12px 20px rgba(0,0,0,0.1); }
    .thumb-container { height: 150px; position: relative; }
    .thumb-container img { width: 100%; height: 100%; object-fit: cover; }
    .status-badge { position: absolute; top: 10px; right: 10px; font-size: 0.7rem; padding: 5px 12px; border-radius: 50px; font-weight: 700; }
    .bg-draft { background: #fff4e5; color: #ff9800; }
    .bg-published { background: #e6fffa; color: #38b2ac; }
    .opacity-75 { opacity: 0.75; }
</style>

<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeInDown">
        <h2 class="fw-bold">Administrator Dashboard</h2>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3 animate__animated animate__fadeInUp" style="animation-delay: 0.1s;">
            <div class="stat-card">
                <h6 class="text-muted small text-uppercase">Total Users</h6>
                <h2 class="fw-bold mb-0"><?php echo $total_users; ?></h2>
            </div>
        </div>

        <div class="col-md-3 animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
            <div class="stat-card" style="border-color: #2ed573;">
                <h6 class="text-muted small text-uppercase">Students</h6>
                <h2 class="fw-bold mb-0"><?php echo $total_students; ?></h2>
            </div>
        </div>

        <div class="col-md-3 animate__animated animate__fadeInUp" style="animation-delay: 0.3s;">
            <div class="stat-card" style="border-color: #ffa726;">
                <h6 class="text-muted small text-uppercase">Instructors</h6>
                <h2 class="fw-bold mb-0"><?php echo $total_instructors; ?></h2>
            </div>
        </div>

        <div class="col-md-3 animate__animated animate__fadeInUp" style="animation-delay: 0.4s;">
            <div class="stat-card" style="border-color: #3742fa;">
                <h6 class="text-muted small text-uppercase">Pending Instructors</h6>
                <h2 class="fw-bold mb-0"><?php echo $pending_instructors; ?></h2>
                <small class="text-muted">Need approval</small>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4 animate__animated animate__fadeInLeft" style="animation-delay: 0.5s;">
            <div class="stat-card">
                <h6 class="text-muted small text-uppercase">Total Courses</h6>
                <h2 class="fw-bold mb-0"><?php echo $total_courses; ?></h2>
                <div class="mt-2">
                    <small class="text-success"><?php echo $published_courses; ?> published</small> |
                    <small class="text-warning"><?php echo $draft_courses; ?> draft</small>
                </div>
            </div>
        </div>

        <div class="col-md-4 animate__animated animate__fadeInUp" style="animation-delay: 0.5s;">
            <div class="stat-card" style="border-color: #2ed573;">
                <h6 class="text-muted small text-uppercase">Enrollments</h6>
                <h2 class="fw-bold mb-0"><?php echo $total_enrollments; ?></h2>
                <div class="mt-2">
                    <small class="text-primary"><?php echo $active_enrollments; ?> active</small>
                </div>
            </div>
        </div>

        <div class="col-md-4 animate__animated animate__fadeInRight" style="animation-delay: 0.5s;">
            <div class="stat-card" style="border-color: #3742fa;">
                <h6 class="text-muted small text-uppercase">Quizzes & Reviews</h6>
                <h2 class="fw-bold mb-0"><?php echo $total_quizzes; ?></h2>
                <div class="mt-2">
                    <small class="text-warning"><?php echo $total_reviews; ?> reviews</small>
                </div>
            </div>
        </div>
    </div>

    <h4 class="fw-bold mb-4 animate__animated animate__fadeIn" style="animation-delay: 0.6s;">Recent Courses</h4>
    <div class="row g-4">
        <?php
        $recent_courses = mysqli_query($conn, "SELECT c.*, u.name as instructor_name FROM courses c JOIN users u ON c.instructor_id = u.id ORDER BY c.created_at DESC LIMIT 6");
        $delay = 0.7;
        if(mysqli_num_rows($recent_courses) > 0){
            while($course = mysqli_fetch_assoc($recent_courses)){
                $imgName = (!empty($course['thumbnail'])) ? $course['thumbnail'] : 'course-default.jpg';
                $imgPath = "../uploads/thumbnails/" . $imgName;
                $status = $course['status'];
                $badgeClass = ($status == 'published' || $status == 'active') ? 'bg-published' : 'bg-draft';
                $statusText = ($status == 'published' || $status == 'active') ? 'Published' : 'Draft';
        ?>
        <div class="col-md-4 animate__animated animate__zoomIn" style="animation-delay: <?= $delay ?>s;">
            <div class="course-card">
                <div class="thumb-container">
                    <img src="<?= $imgPath ?>" class="img-fluid" alt="Course Thumbnail" onerror="this.src='../uploads/thumbnails/course-default.jpg'">
                    <span class="status-badge <?= $badgeClass ?>"><?= strtoupper($statusText) ?></span>
                </div>
                <div class="p-3">
                    <h6 class="fw-bold text-truncate mb-1"><?= htmlspecialchars($course['title']) ?></h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <p class="text-muted small mb-0">by <?= htmlspecialchars($course['instructor_name']) ?></p>
                        <a href="manage_courses.php" class="text-primary small text-decoration-none">Manage <i class="fas fa-arrow-right ms-1" style="font-size: 10px;"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <?php
                $delay += 0.1;
            }
        } else {
        ?>
        <div class="col-12 text-center py-5 bg-white rounded-4 shadow-sm animate__animated animate__fadeIn">
            <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
            <h5>No courses found.</h5>
            <p class="text-muted">Courses will appear here once instructors start creating them.</p>
        </div>
        <?php } ?>
    </div>

    <div class="row mt-5">
        <div class="col-md-6 animate__animated animate__slideInLeft" style="animation-delay: 1s;">
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
                            echo '<div class="activity-item d-flex align-items-center animate__animated animate__fadeIn">';
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

        <div class="col-md-6 animate__animated animate__slideInRight" style="animation-delay: 1s;">
            <div class="activity-card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-book me-2 text-success"></i>Recent Courses Activity</h5>
                </div>
                <div class="card-body p-0">
                    <?php
                    $recent_courses_list = mysqli_query($conn, "SELECT c.*, u.name as instructor_name FROM courses c JOIN users u ON c.instructor_id = u.id ORDER BY c.created_at DESC LIMIT 5");
                    if(mysqli_num_rows($recent_courses_list) > 0){
                        while($course = mysqli_fetch_assoc($recent_courses_list)){
                            $status_class = $course['status'] == 'published' ? 'success' : 'warning';
                            $status_text = ucfirst($course['status']);
                            echo '<div class="activity-item d-flex align-items-center animate__animated animate__fadeIn">';
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

<?php include 'includes/footer.php'; ?>