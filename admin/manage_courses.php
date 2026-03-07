<?php
include 'includes/header.php';
// Handle course actions
if(isset($_POST['action']) && isset($_POST['course_id'])){
    $course_id = mysqli_real_escape_string($conn, $_POST['course_id']);
    $action = $_POST['action'];

    if($action == 'publish'){
        mysqli_query($conn, "UPDATE courses SET status = 'published' WHERE id = '$course_id'");
        $message = "Course published successfully!";
    } elseif($action == 'unpublish'){
        mysqli_query($conn, "UPDATE courses SET status = 'draft' WHERE id = '$course_id'");
        $message = "Course unpublished successfully!";
    } elseif($action == 'delete'){
        mysqli_query($conn, "DELETE FROM courses WHERE id = '$course_id'");
        $message = "Course deleted successfully!";
    }
}

// Get all courses with instructor info
$courses_query = "SELECT c.*, u.name as instructor_name, cat.name as category_name,
                         COUNT(e.id) as enrollment_count
                  FROM courses c
                  JOIN users u ON c.instructor_id = u.id
                  LEFT JOIN categories cat ON c.category_id = cat.id
                  LEFT JOIN enrollments e ON c.id = e.course_id
                  GROUP BY c.id
                  ORDER BY c.created_at DESC";
$courses_result = mysqli_query($conn, $courses_query);
?>

<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-book me-2"></i>Manage Courses</h2>
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>

            <?php if(isset($message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-book me-2"></i>All Courses</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive table-3d">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Title</th>
                                    <th>Instructor</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Enrollments</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($course = mysqli_fetch_assoc($courses_result)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($course['title']); ?></td>
                                        <td><?php echo htmlspecialchars($course['instructor_name']); ?></td>
                                        <td><?php echo htmlspecialchars($course['category_name'] ?? 'Uncategorized'); ?></td>
                                        <td>
                                            <?php if($course['price'] > 0): ?>
                                                <span class="text-success fw-bold">$<?php echo number_format($course['price'], 2); ?></span>
                                            <?php else: ?>
                                                <span class="text-primary">Free</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $course['status'] == 'published' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($course['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $course['enrollment_count']; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($course['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                                    <?php if($course['status'] == 'draft'): ?>
                                                        <button type="submit" name="action" value="publish" class="btn btn-success btn-sm"
                                                                onclick="return confirm('Are you sure you want to publish this course?')">
                                                            <i class="fas fa-globe"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <button type="submit" name="action" value="unpublish" class="btn btn-warning btn-sm"
                                                                onclick="return confirm('Are you sure you want to unpublish this course?')">
                                                            <i class="fas fa-eye-slash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <button type="submit" name="action" value="delete" class="btn btn-danger btn-sm"
                                                            onclick="return confirm('Are you sure you want to delete this course? This action cannot be undone!')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

<?php include 'includes/footer.php'; ?>