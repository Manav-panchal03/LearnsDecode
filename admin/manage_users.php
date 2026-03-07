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
        $user_role = mysqli_fetch_assoc(mysqli_query($conn, "SELECT role FROM users WHERE id = '$user_id'"))['role'];
        if($user_role != 'admin'){
            mysqli_query($conn, "DELETE FROM users WHERE id = '$user_id'");
            $message = "User deleted successfully!";
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

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-users me-2"></i>Manage Users</h2>
                <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>

            <?php if(isset($message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if(isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>All Users</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive table-3d">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Courses</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($user = mysqli_fetch_assoc($users_result)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php
                                                echo $user['role'] == 'admin' ? 'info' :
                                                     ($user['role'] == 'instructor' ? 'warning' : 'success');
                                            ?> fs-6">
                                                <?php echo ucfirst($user['role']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if($user['approved']): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $user['course_count']; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <?php if($user['approved']): ?>
                                                        <button type="submit" name="action" value="deactivate" class="btn btn-warning btn-sm"
                                                                onclick="return confirm('Are you sure you want to deactivate this user?')">
                                                            <i class="fas fa-ban"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <button type="submit" name="action" value="activate" class="btn btn-success btn-sm"
                                                                onclick="return confirm('Are you sure you want to activate this user?')">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    <?php endif; ?>

                                                    <?php if($user['role'] == 'student'): ?>
                                                        <button type="submit" name="action" value="make_instructor" class="btn btn-info btn-sm"
                                                                onclick="return confirm('Are you sure you want to make this user an instructor?')">
                                                            <i class="fas fa-chalkboard-teacher"></i>
                                                        </button>
                                                    <?php elseif($user['role'] == 'instructor'): ?>
                                                        <button type="submit" name="action" value="remove_instructor" class="btn btn-secondary btn-sm"
                                                                onclick="return confirm('Are you sure you want to remove instructor privileges from this user?')">
                                                            <i class="fas fa-user"></i>
                                                        </button>
                                                    <?php endif; ?>

                                                    <button type="submit" name="action" value="delete" class="btn btn-danger btn-sm"
                                                            onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone!')">
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