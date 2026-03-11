<?php
include 'includes/header.php';
// Handle user actions (activate/deactivate/delete/make instructor)
if(isset($_POST['action']) && isset($_POST['user_id'])){
    $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    $action = $_POST['action'];

    if($action == 'activate'){
        mysqli_query($conn, "UPDATE users SET approved = 1 WHERE id = '$user_id'");
        $message = "User activated successfully!";
    } elseif($action == 'deactivate'){
        mysqli_query($conn, "UPDATE users SET approved = 0 WHERE id = '$user_id'");
        $message = "User deactivated successfully!";
    } elseif($action == 'make_instructor'){
        mysqli_query($conn, "UPDATE users SET role = 'instructor', approved = 1 WHERE id = '$user_id'");
        $message = "User promoted to instructor successfully!";
    } elseif($action == 'remove_instructor'){
        mysqli_query($conn, "UPDATE users SET role = 'student', approved = 1 WHERE id = '$user_id'");
        $message = "User demoted to student successfully!";
    } elseif($action == 'delete'){
        // Only delete if not admin
        $user_role_q = mysqli_query($conn, "SELECT role FROM users WHERE id = '$user_id'");
        $user_role_data = mysqli_fetch_assoc($user_role_q);
        $user_role = $user_role_data['role'];

        if($user_role != 'admin'){
            // 1. Pehla related requests delete karo jethi Foreign Key error na ave
            mysqli_query($conn, "DELETE FROM instructor_requests WHERE user_id = '$user_id'");
            
            // 2. Have main user ne delete karo
            mysqli_query($conn, "DELETE FROM users WHERE id = '$user_id'");
            
            $message = "User and related requests deleted successfully!";
        } else {
            $error = "Cannot delete administrators!";
        }
    }
}

$admin_id = $_SESSION['user_id']; // Get current admin ID to exclude from list
// Get all users with stats
$users_query = "SELECT u.*, COUNT(c.id) as course_count FROM users u LEFT JOIN courses c ON u.id = c.instructor_id WHERE u.id != '$admin_id' GROUP BY u.id ORDER BY u.created_at DESC";
$users_result = mysqli_query($conn, $users_query);
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* smooth transitions */
    .table-responsive { overflow: hidden; }
    tr { transition: all 0.3s ease; }
    .btn-group .btn { transition: transform 0.2s; }
    .btn-group .btn:hover { transform: scale(1.1); z-index: 5; }
    .card { border-radius: 15px; border: none; overflow: hidden; }
    .badge { padding: 8px 12px; border-radius: 50px; }
</style>

<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeInDown">
        <h2 class="fw-bold"><i class="fas fa-users me-2 text-primary"></i>Manage Users</h2>
        <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>

    <?php if(isset($message)): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '<?php echo $message; ?>',
                timer: 3000,
                showConfirmButton: false,
                showClass: { popup: 'animate__animated animate__fadeInUp' }
            });
        </script>
    <?php endif; ?>

    <?php if(isset($error)): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '<?php echo $error; ?>',
                showClass: { popup: 'animate__animated animate__shakeX' }
            });
        </script>
    <?php endif; ?>

    <div class="card shadow animate__animated animate__fadeInUp">
        <div class="card-header bg-primary text-white p-3">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>All Registered Users</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Courses</th>
                            <th>Joined</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $delay = 0.1;
                        while($user = mysqli_fetch_assoc($users_result)): 
                        ?>
                            <tr class="animate__animated animate__fadeIn" style="animation-delay: <?php echo $delay; ?>s">
                                <td class="ps-4 fw-bold"><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="badge bg-<?php
                                        echo $user['role'] == 'admin' ? 'info' :
                                             ($user['role'] == 'instructor' ? 'warning' : 'success');
                                    ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if($user['approved']): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold text-center"><?php echo $user['course_count']; ?></td>
                                <td class="text-muted small"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td class="text-end pe-4">
                                    <div class="btn-group shadow-sm" role="group">
                                        <form id="form-<?php echo $user['id']; ?>" method="POST" class="d-inline">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="action" id="action-<?php echo $user['id']; ?>" value="">

                                            <?php if($user['approved']): ?>
                                                <button type="button" class="btn btn-warning btn-sm" 
                                                        onclick="confirmAction(<?php echo $user['id']; ?>, 'deactivate', 'Deactivate this user?')">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-success btn-sm"
                                                        onclick="confirmAction(<?php echo $user['id']; ?>, 'activate', 'Activate this user?')">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php endif; ?>

                                            <?php if($user['role'] == 'student'): ?>
                                                <button type="button" class="btn btn-info btn-sm text-white"
                                                        onclick="confirmAction(<?php echo $user['id']; ?>, 'make_instructor', 'Promote to Instructor?')">
                                                    <i class="fas fa-chalkboard-teacher"></i>
                                                </button>
                                            <?php elseif($user['role'] == 'instructor'): ?>
                                                <button type="button" class="btn btn-secondary btn-sm"
                                                        onclick="confirmAction(<?php echo $user['id']; ?>, 'remove_instructor', 'Demote to Student?')">
                                                    <i class="fas fa-user"></i>
                                                </button>
                                            <?php endif; ?>

                                            <button type="button" class="btn btn-danger btn-sm"
                                                    onclick="confirmAction(<?php echo $user['id']; ?>, 'delete', 'Delete this user forever? This cannot be undone!')">
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
function confirmAction(userId, actionType, message) {
    let btnColor = actionType === 'delete' ? '#d33' : (actionType === 'activate' ? '#28a745' : '#3085d6');
    
    Swal.fire({
        title: 'Are you sure?',
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: btnColor,
        cancelButtonColor: '#6e7881',
        confirmButtonText: 'Yes, proceed!',
        showClass: {
            popup: 'animate__animated animate__zoomIn'
        },
        hideClass: {
            popup: 'animate__animated animate__zoomOut'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('action-' + userId).value = actionType;
            document.getElementById('form-' + userId).submit();
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>