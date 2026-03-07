<?php
include 'includes/header.php';
$admin_id = $_SESSION['user_id'];

// Handle approval/rejection actions
if(isset($_POST['action']) && isset($_POST['request_id'])){
    $request_id = mysqli_real_escape_string($conn, $_POST['request_id']);
    $action = $_POST['action'];

    if($action == 'approve'){
        // Update user approved status and role to instructor
        $update_user = "UPDATE users SET approved = 1, role = 'instructor' WHERE id = (SELECT user_id FROM instructor_requests WHERE id = '$request_id')";
        mysqli_query($conn, $update_user);

        // Update request status
        $update_request = "UPDATE instructor_requests SET status = 'approved', reviewed_at = NOW(), reviewed_by = '$admin_id' WHERE id = '$request_id'";
        mysqli_query($conn, $update_request);

        $message = "Instructor request approved successfully!";
    } elseif($action == 'reject'){
        // Update user approved status (keep as 0) and role back to student
        $update_user = "UPDATE users SET approved = 0, role = 'student' WHERE id = (SELECT user_id FROM instructor_requests WHERE id = '$request_id')";
        mysqli_query($conn, $update_user);

        // Update request status
        $update_request = "UPDATE instructor_requests SET status = 'rejected', reviewed_at = NOW(), reviewed_by = '$admin_id' WHERE id = '$request_id'";
        mysqli_query($conn, $update_request);

        $message = "Instructor request rejected!";
    }
}

// Get all instructor requests
$requests_query = "SELECT ir.*, u.name, u.email, reviewer.name as reviewer_name
                   FROM instructor_requests ir
                   JOIN users u ON ir.user_id = u.id
                   LEFT JOIN users reviewer ON ir.reviewed_by = reviewer.id
                   ORDER BY ir.requested_at DESC";
$requests_result = mysqli_query($conn, $requests_query);
?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Instructor Approval Requests</h2>
                <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>

            <?php if(isset($message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Pending & Processed Instructor Requests</h5>
                </div>
                <div class="card-body">
                    <?php if(mysqli_num_rows($requests_result) > 0): ?>
                        <div class="table-responsive table-3d">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Request Reason</th>
                                        <th>Requested Date</th>
                                        <th>Status</th>
                                        <th>Reviewed By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($request = mysqli_fetch_assoc($requests_result)): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($request['name']); ?></td>
                                            <td><?php echo htmlspecialchars($request['email']); ?></td>
                                            <td><?php echo htmlspecialchars($request['request_reason']); ?></td>
                                            <td><?php echo date('M d, Y H:i', strtotime($request['requested_at'])); ?></td>
                                            <td>
                                                <?php
                                                $status_class = '';
                                                switch($request['status']){
                                                    case 'pending': $status_class = 'warning'; break;
                                                    case 'approved': $status_class = 'success'; break;
                                                    case 'rejected': $status_class = 'danger'; break;
                                                }
                                                ?>
                                                <span class="badge bg-<?php echo $status_class; ?>">
                                                    <?php echo ucfirst($request['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo $request['reviewer_name'] ? htmlspecialchars($request['reviewer_name']) : '-'; ?>
                                                <?php if($request['reviewed_at']): ?>
                                                    <br><small class="text-muted"><?php echo date('M d, Y H:i', strtotime($request['reviewed_at'])); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($request['status'] == 'pending'): ?>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                                        <button type="submit" name="action" value="approve" class="btn btn-success btn-sm me-1"
                                                                onclick="return confirm('Are you sure you want to approve this instructor request? The user will become an instructor.')">
                                                            <i class="fas fa-check"></i> Approve
                                                        </button>
                                                        <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm"
                                                                onclick="return confirm('Are you sure you want to reject this instructor request?')">
                                                            <i class="fas fa-times"></i> Reject
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="text-muted">Processed</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-user-graduate fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Instructor Requests</h5>
                            <p class="text-muted">There are currently no instructor approval requests to review.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Statistics -->
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Pending Requests</h5>
                            <h3 class="text-warning">
                                <?php
                                $pending_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM instructor_requests WHERE status='pending'"))['count'];
                                echo $pending_count;
                                ?>
                            </h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Approved</h5>
                            <h3 class="text-success">
                                <?php
                                $approved_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM instructor_requests WHERE status='approved'"))['count'];
                                echo $approved_count;
                                ?>
                            </h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Rejected</h5>
                            <h3 class="text-danger">
                                <?php
                                $rejected_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM instructor_requests WHERE status='rejected'"))['count'];
                                echo $rejected_count;
                                ?>
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>