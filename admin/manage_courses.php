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

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .card { border-radius: 15px; border: none; }
    tr { transition: all 0.3s ease; }
    .btn-group .btn { transition: transform 0.2s; }
    .btn-group .btn:hover { transform: scale(1.1); z-index: 5; }
    .table-responsive { border-radius: 0 0 15px 15px; }
    .badge { padding: 8px 12px; border-radius: 50px; }
</style>

<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeInDown">
        <h2 class="fw-bold"><i class="fas fa-book me-2 text-success"></i>Manage Courses</h2>
        <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>

    <?php if(isset($message)): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Done!',
                text: '<?php echo $message; ?>',
                timer: 2500,
                showConfirmButton: false,
                showClass: { popup: 'animate__animated animate__backInRight' }
            });
        </script>
    <?php endif; ?>

    <div class="card shadow animate__animated animate__fadeInUp">
        <div class="card-header bg-success text-white p-3">
            <h5 class="mb-0"><i class="fas fa-list-ul me-2"></i>Course Inventory</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive table-3d">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Title</th>
                            <th>Instructor</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Enrollments</th>
                            <th>Created</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $delay = 0.1;
                        while($course = mysqli_fetch_assoc($courses_result)): 
                        ?>
                            <tr class="animate__animated animate__fadeIn" style="animation-delay: <?php echo $delay; ?>s">
                                <td class="ps-4 fw-bold"><?php echo htmlspecialchars($course['title']); ?></td>
                                <td><i class="fas fa-user-tie me-1 text-muted"></i> <?php echo htmlspecialchars($course['instructor_name']); ?></td>
                                <td><span class="text-muted"><?php echo htmlspecialchars($course['category_name'] ?? 'Uncategorized'); ?></span></td>
                                <td>
                                    <?php if($course['price'] > 0): ?>
                                        <span class="text-success fw-bold">₹<?php echo number_format($course['price'], 2); ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-primary-subtle text-primary">Free</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $course['status'] == 'published' ? 'success' : 'warning text-dark'; ?>">
                                        <?php echo ucfirst($course['status']); ?>
                                    </span>
                                </td>
                                <td class="text-center"><i class="fas fa-users me-1 text-info"></i> <?php echo $course['enrollment_count']; ?></td>
                                <td class="small text-muted"><?php echo date('M d, Y', strtotime($course['created_at'])); ?></td>
                                <td class="text-end pe-4">
                                    <div class="btn-group shadow-sm" role="group">
                                        <form id="course-form-<?php echo $course['id']; ?>" method="POST" class="d-inline">
                                            <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                            <input type="hidden" name="action" id="action-<?php echo $course['id']; ?>" value="">

                                            <?php if($course['status'] == 'draft'): ?>
                                                <button type="button" class="btn btn-success btn-sm"
                                                        onclick="confirmCourseAction(<?php echo $course['id']; ?>, 'publish', 'Do you want to make this course live for students?')">
                                                    <i class="fas fa-globe"></i>
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-warning btn-sm"
                                                        onclick="confirmCourseAction(<?php echo $course['id']; ?>, 'unpublish', 'Move this course back to drafts?')">
                                                    <i class="fas fa-eye-slash"></i>
                                                </button>
                                            <?php endif; ?>

                                            <button type="button" class="btn btn-danger btn-sm"
                                                    onclick="confirmCourseAction(<?php echo $course['id']; ?>, 'delete', 'Are you sure? This will permanently delete the course!')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php 
                        $delay += 0.05;
                        endwhile; 
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function confirmCourseAction(courseId, actionType, message) {
    let iconType = actionType === 'delete' ? 'error' : 'question';
    let confirmBtnText = actionType === 'delete' ? 'Yes, Delete it!' : 'Yes, Proceed!';
    let confirmBtnColor = actionType === 'delete' ? '#d33' : (actionType === 'publish' ? '#198754' : '#ffc107');

    Swal.fire({
        title: 'Action Confirmation',
        text: message,
        icon: iconType,
        showCancelButton: true,
        confirmButtonColor: confirmBtnColor,
        cancelButtonColor: '#6e7881',
        confirmButtonText: confirmBtnText,
        showClass: {
            popup: 'animate__animated animate__zoomIn'
        },
        hideClass: {
            popup: 'animate__animated animate__fadeOutDown'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('action-' + courseId).value = actionType;
            document.getElementById('course-form-' + courseId).submit();
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>