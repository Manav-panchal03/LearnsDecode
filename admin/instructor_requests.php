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

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .card { border-radius: 15px; border: none; overflow: hidden; }
    .table-hover tbody tr { transition: all 0.3s; }
    .stats-card { transition: transform 0.3s; border-radius: 15px; }
    .stats-card:hover { transform: translateY(-5px); }
    .badge { padding: 8px 12px; border-radius: 50px; }
    .btn-sm { border-radius: 8px; transition: all 0.2s; }
    .btn-sm:hover { transform: scale(1.05); }
</style>

<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeInDown">
        <h2 class="fw-bold"><i class="fas fa-user-check me-2 text-primary"></i>Instructor Requests</h2>
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

    <div class="card shadow animate__animated animate__fadeInUp">
        <div class="card-header bg-dark text-white p-3">
            <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Pending & Processed Requests</h5>
        </div>
        <div class="card-body p-0">
            <?php if(mysqli_num_rows($requests_result) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Name</th>
                                <th>Email</th>
                                <th>Reason</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Reviewed By</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $delay = 0.1;
                            while($request = mysqli_fetch_assoc($requests_result)): 
                            ?>
                                <tr class="animate__animated animate__fadeIn" style="animation-delay: <?php echo $delay; ?>s">
                                    <td class="ps-4 fw-bold"><?php echo htmlspecialchars($request['name']); ?></td>
                                    <td><?php echo htmlspecialchars($request['email']); ?></td>
                                    <td><small class="text-muted"><?php echo htmlspecialchars($request['request_reason']); ?></small></td>
                                    <td><?php echo date('M d, Y', strtotime($request['requested_at'])); ?></td>
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
                                        <?php if($request['reviewer_name']): ?>
                                            <div class="small">
                                                <strong><?php echo htmlspecialchars($request['reviewer_name']); ?></strong><br>
                                                <span class="text-muted"><?php echo date('M d, Y', strtotime($request['reviewed_at'])); ?></span>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <?php if($request['status'] == 'pending'): ?>
                                            <div class="btn-group">
                                                <form id="req-form-<?php echo $request['id']; ?>" method="POST" class="d-inline">
                                                    <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                                    <input type="hidden" name="action" id="action-<?php echo $request['id']; ?>" value="">
                                                    
                                                    <button type="button" class="btn btn-success btn-sm me-1"
                                                            onclick="confirmRequest(<?php echo $request['id']; ?>, 'approve', 'Approve this instructor request?')">
                                                        <i class="fas fa-check me-1"></i> Approve
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm"
                                                            onclick="confirmRequest(<?php echo $request['id']; ?>, 'reject', 'Reject this instructor request?')">
                                                        <i class="fas fa-times me-1"></i> Reject
                                                    </button>
                                                </form>
                                            </div>
                                        <?php else: ?>
                                            <span class="badge bg-light text-dark border">Completed</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php 
                            $delay += 0.05;
                            endwhile; 
                            ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5 animate__animated animate__fadeIn">
                    <i class="fas fa-user-graduate fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No Instructor Requests</h5>
                    <p class="text-muted px-4">There are currently no instructor approval requests to review.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row mt-4 g-3 animate__animated animate__fadeInUp animate__delay-1s">
        <?php
        $stats = [
            ['Pending', 'warning', "SELECT COUNT(*) as count FROM instructor_requests WHERE status='pending'"],
            ['Approved', 'success', "SELECT COUNT(*) as count FROM instructor_requests WHERE status='approved'"],
            ['Rejected', 'danger', "SELECT COUNT(*) as count FROM instructor_requests WHERE status='rejected'"]
        ];

        foreach($stats as $stat):
            $count = mysqli_fetch_assoc(mysqli_query($conn, $stat[2]))['count'];
        ?>
        <div class="col-md-4">
            <div class="card text-center shadow-sm stats-card border-bottom border-4 border-<?php echo $stat[1]; ?>">
                <div class="card-body py-4">
                    <h6 class="text-muted text-uppercase fw-bold mb-2"><?php echo $stat[0]; ?></h6>
                    <h2 class="display-6 fw-bold text-<?php echo $stat[1]; ?>"><?php echo $count; ?></h2>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function confirmRequest(requestId, actionType, message) {
    let iconType = actionType === 'approve' ? 'question' : 'warning';
    let btnColor = actionType === 'approve' ? '#198754' : '#dc3545';

    Swal.fire({
        title: 'Are you sure?',
        text: message,
        icon: iconType,
        showCancelButton: true,
        confirmButtonColor: btnColor,
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, ' + actionType + '!',
        showClass: { popup: 'animate__animated animate__zoomIn' },
        hideClass: { popup: 'animate__animated animate__zoomOut' }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('action-' + requestId).value = actionType;
            document.getElementById('req-form-' + requestId).submit();
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>