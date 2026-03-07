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

// Quiz and review statistics
$stats['engagement'] = [
    'quizzes' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM quizzes"))['count'],
    'quiz_attempts' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM quiz_attempts"))['count'],
    'reviews' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM reviews"))['count'],
    'avg_rating' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(rating) as avg FROM reviews"))['avg']
];

// Revenue statistics (if applicable)
$stats['revenue'] = [
    'potential' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(c.price * e.enrollment_count) as total
                                                           FROM courses c
                                                           CROSS JOIN (SELECT COUNT(*) as enrollment_count FROM enrollments) e"))['total'] ?? 0
];

// Recent activity (last 30 days)
$stats['recent'] = [
    'new_users' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"))['count'],
    'new_courses' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM courses WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"))['count'],
    'new_enrollments' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM enrollments WHERE enrolled_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"))['count']
];
?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Reports & Analytics</h2>
                <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>

            <!-- User Statistics -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>User Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-2">
                            <h3 class="text-primary"><?php echo $stats['users']['total']; ?></h3>
                            <p class="mb-0">Total Users</p>
                        </div>
                        <div class="col-md-2">
                            <h3 class="text-success"><?php echo $stats['users']['students']; ?></h3>
                            <p class="mb-0">Students</p>
                        </div>
                        <div class="col-md-2">
                            <h3 class="text-warning"><?php echo $stats['users']['instructors']; ?></h3>
                            <p class="mb-0">Instructors</p>
                        </div>
                        <div class="col-md-2">
                            <h3 class="text-info"><?php echo $stats['users']['admins']; ?></h3>
                            <p class="mb-0">Admins</p>
                        </div>
                        <div class="col-md-2">
                            <h3 class="text-danger"><?php echo $stats['users']['pending_admins']; ?></h3>
                            <p class="mb-0">Pending Admins</p>
                        </div>
                        <div class="col-md-2">
                            <h3 class="text-secondary"><?php echo $stats['users']['active_users']; ?></h3>
                            <p class="mb-0">Active Users</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Course Statistics -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-book me-2"></i>Course Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <h3 class="text-primary"><?php echo $stats['courses']['total']; ?></h3>
                            <p class="mb-0">Total Courses</p>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-success"><?php echo $stats['courses']['published']; ?></h3>
                            <p class="mb-0">Published</p>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-warning"><?php echo $stats['courses']['draft']; ?></h3>
                            <p class="mb-0">Draft</p>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-info">$<?php echo number_format($stats['courses']['avg_price'] ?? 0, 2); ?></h3>
                            <p class="mb-0">Avg Price</p>
                        </div>
                    </div>
                    <hr>
                    <div class="row text-center">
                        <div class="col-md-6">
                            <h4 class="text-success"><?php echo $stats['courses']['free']; ?></h4>
                            <p class="mb-0">Free Courses</p>
                        </div>
                        <div class="col-md-6">
                            <h4 class="text-warning"><?php echo $stats['courses']['paid']; ?></h4>
                            <p class="mb-0">Paid Courses</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enrollment & Engagement -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Enrollment Statistics</h5>
                        </div>
                        <div class="card-body text-center">
                            <h3 class="text-primary"><?php echo $stats['enrollments']['total']; ?></h3>
                            <p class="mb-2">Total Enrollments</p>
                            <div class="row">
                                <div class="col-6">
                                    <h5 class="text-success"><?php echo $stats['enrollments']['active']; ?></h5>
                                    <small>Active</small>
                                </div>
                                <div class="col-6">
                                    <h5 class="text-warning"><?php echo $stats['enrollments']['completed']; ?></h5>
                                    <small>Completed</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-warning text-white">
                            <h5 class="mb-0"><i class="fas fa-star me-2"></i>Engagement Statistics</h5>
                        </div>
                        <div class="card-body text-center">
                            <div class="row">
                                <div class="col-6">
                                    <h4 class="text-primary"><?php echo $stats['engagement']['quizzes']; ?></h4>
                                    <p class="mb-0">Quizzes</p>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-success"><?php echo $stats['engagement']['reviews']; ?></h4>
                                    <p class="mb-0">Reviews</p>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-6">
                                    <h5 class="text-info"><?php echo $stats['engagement']['quiz_attempts']; ?></h5>
                                    <small>Quiz Attempts</small>
                                </div>
                                <div class="col-6">
                                    <h5 class="text-warning"><?php echo number_format($stats['engagement']['avg_rating'] ?? 0, 1); ?>★</h5>
                                    <small>Avg Rating</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity (30 days) -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Recent Activity (Last 30 Days)</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <h3 class="text-primary"><?php echo $stats['recent']['new_users']; ?></h3>
                            <p class="mb-0">New Users</p>
                        </div>
                        <div class="col-md-4">
                            <h3 class="text-success"><?php echo $stats['recent']['new_courses']; ?></h3>
                            <p class="mb-0">New Courses</p>
                        </div>
                        <div class="col-md-4">
                            <h3 class="text-warning"><?php echo $stats['recent']['new_enrollments']; ?></h3>
                            <p class="mb-0">New Enrollments</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Categories -->
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Top Categories</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive table-3d">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Courses</th>
                                    <th>Enrollments</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $categories_query = "SELECT cat.name, cat.icon, COUNT(c.id) as course_count,
                                                           COUNT(e.id) as enrollment_count
                                                    FROM categories cat
                                                    LEFT JOIN courses c ON cat.id = c.category_id
                                                    LEFT JOIN enrollments e ON c.id = e.course_id
                                                    GROUP BY cat.id
                                                    ORDER BY enrollment_count DESC";
                                $categories_result = mysqli_query($conn, $categories_query);
                                while($category = mysqli_fetch_assoc($categories_result)):
                                ?>
                                    <tr>
                                        <td>
                                            <i class="<?php echo htmlspecialchars($category['icon']); ?> me-2"></i>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </td>
                                        <td><?php echo $category['course_count']; ?></td>
                                        <td><?php echo $category['enrollment_count']; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>