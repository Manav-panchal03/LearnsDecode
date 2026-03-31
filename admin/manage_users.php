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
// Get instructors and admins with stats and profile details
$instructors_query = "SELECT u.*, COUNT(c.id) as course_count, COALESCE(p.bio, '') as profile_bio, COALESCE(p.expertise, '') as profile_expertise FROM users u LEFT JOIN courses c ON u.id = c.instructor_id LEFT JOIN instructor_profiles p ON p.user_id = u.id WHERE u.id != '$admin_id' AND u.role IN ('admin','instructor') GROUP BY u.id ORDER BY u.created_at DESC";
$instructors_result = mysqli_query($conn, $instructors_query);

// Get students with stats
$students_query = "SELECT u.*, COUNT(c.id) as course_count FROM users u LEFT JOIN courses c ON u.id = c.instructor_id WHERE u.id != '$admin_id' AND u.role = 'student' GROUP BY u.id ORDER BY u.created_at DESC";
$students_result = mysqli_query($conn, $students_query);
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

    .detail-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.55);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 2100;
    }
    .detail-modal-overlay.active {
        display: flex;
    }
    .detail-modal-content {
        background: #ffffff;
        border-radius: 1rem;
        width: min(520px, 95%);
        max-width: 520px;
        padding: 1.75rem;
        box-shadow: 0 30px 60px rgba(33,37,41,0.17);
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(108,117,125,0.18);
    }
    .detail-modal-content::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(59,130,246,0.08), transparent 40%),
                    radial-gradient(circle at top left, rgba(59,130,246,0.12), transparent 25%);
        pointer-events: none;
    }
    .detail-modal-close {
        position: absolute;
        top: 0.85rem;
        right: 0.85rem;
        border: none;
        background: rgba(33,37,41,0.06);
        width: 2.4rem;
        height: 2.4rem;
        border-radius: 50%;
        font-size: 1.25rem;
        color: #1f2937;
        cursor: pointer;
        transition: transform 0.2s ease, background 0.2s ease;
    }
    .detail-modal-close:hover {
        transform: scale(1.05);
        background: rgba(33,37,41,0.12);
    }
    .detail-modal-content h5 {
        margin-bottom: 1rem;
        color: #111827;
        font-weight: 700;
    }
    .detail-modal-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 1rem;
        padding-bottom: 0.65rem;
        border-bottom: 1px solid rgba(148,163,184,0.16);
    }
    .detail-modal-row:last-child {
        margin-bottom: 0;
        border-bottom: none;
        padding-bottom: 0;
    }
    .detail-modal-label {
        font-weight: 700;
        color: #475569;
    }
    .detail-modal-value {
        color: #0f172a;
        text-align: right;
        max-width: 55%;
        word-break: break-word;
    }

    .detail-modal-content .detail-modal-row,
    .detail-modal-content h5 {
        opacity: 0;
    }
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

    <div class="row g-4">
        <div class="col-12">
            <div class="card shadow animate__animated animate__fadeInUp">
                <div class="card-header bg-primary text-white p-3">
                    <h5 class="mb-0"><i class="fas fa-user-tie me-2"></i>Instructors & Admins</h5>
                </div>
                <div class="card-body p-3">
                    <div class="mb-3">
                        <input id="search-instructors" type="text" class="form-control" placeholder="Search instructors & admins...">
                    </div>
                    <div class="table-responsive">
                        <table id="instructors-table" class="table table-hover align-middle mb-0">
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
                                while($user = mysqli_fetch_assoc($instructors_result)): 
                                ?>
                                    <tr class="animate__animated animate__fadeIn" style="animation-delay: <?php echo $delay; ?>s">
                                        <td class="ps-4 fw-bold">
                                            <button type="button" class="btn btn-link p-0 text-decoration-none text-primary instructor-detail-btn"
                                                data-name="<?php echo htmlspecialchars($user['name'], ENT_QUOTES); ?>"
                                                data-bio="<?php echo htmlspecialchars($user['profile_bio'] ?: 'No bio available.', ENT_QUOTES); ?>"
                                                data-expertise="<?php echo htmlspecialchars($user['profile_expertise'] ?: 'Expertise not specified.', ENT_QUOTES); ?>">
                                                <?php echo htmlspecialchars($user['name']); ?>
                                            </button>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php
                                                echo $user['role'] == 'admin' ? 'info' : 'warning';
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

                                                    <?php if($user['role'] == 'instructor'): ?>
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

        <div class="col-12">
            <div class="card shadow animate__animated animate__fadeInUp">
                <div class="card-header bg-secondary text-white p-3">
                    <h5 class="mb-0"><i class="fas fa-user-graduate me-2"></i>Students</h5>
                </div>
                <div class="card-body p-3">
                    <div class="mb-3">
                        <input id="search-students" type="text" class="form-control" placeholder="Search students...">
                    </div>
                    <div class="table-responsive">
                        <table id="students-table" class="table table-hover align-middle mb-0">
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
                                while($user = mysqli_fetch_assoc($students_result)): 
                                ?>
                                    <tr class="animate__animated animate__fadeIn" style="animation-delay: <?php echo $delay; ?>s">
                                        <td class="ps-4 fw-bold"><?php echo htmlspecialchars($user['name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <span class="badge bg-success">
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

                                                    <button type="button" class="btn btn-info btn-sm text-white"
                                                        onclick="confirmAction(<?php echo $user['id']; ?>, 'make_instructor', 'Promote to Instructor?')">
                                                        <i class="fas fa-chalkboard-teacher"></i>
                                                    </button>

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
    </div>
</div>

<div id="detail-modal" class="detail-modal-overlay animate__animated">
    <div class="detail-modal-content animate__animated animate__zoomIn">
        <button type="button" class="detail-modal-close" aria-label="Close details">&times;</button>
        <h5>Instructor Details</h5>
        <div id="detail-content">
            <div class="detail-modal-row">
                <span class="detail-modal-label">Name</span>
                <span class="detail-modal-value" id="detail-name"></span>
            </div>
            <div class="detail-modal-row">
                <span class="detail-modal-label">Expertise</span>
                <span class="detail-modal-value" id="detail-expertise"></span>
            </div>
            <div class="detail-modal-row">
                <span class="detail-modal-label">Bio</span>
                <span class="detail-modal-value" id="detail-bio"></span>
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

function filterTable(tableId, query) {
    const filter = query.trim().toLowerCase();
    const table = document.getElementById(tableId);
    if (!table) return;

    table.querySelectorAll('tbody tr').forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
}

const instructorsSearch = document.getElementById('search-instructors');
const studentsSearch = document.getElementById('search-students');

if (instructorsSearch) {
    instructorsSearch.addEventListener('input', function() {
        filterTable('instructors-table', this.value);
    });
}

if (studentsSearch) {
    studentsSearch.addEventListener('input', function() {
        filterTable('students-table', this.value);
    });
}

const detailModal = document.getElementById('detail-modal');
const detailClose = document.querySelector('.detail-modal-close');
const detailFields = {
    name: document.getElementById('detail-name'),
    expertise: document.getElementById('detail-expertise'),
    bio: document.getElementById('detail-bio')
};
const detailRows = document.querySelectorAll('.detail-modal-row');
const detailTitle = document.querySelector('.detail-modal-content h5');

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

const detailContent = document.querySelector('.detail-modal-content');

function animateDetailText() {
    detailTitle.classList.remove('animate__fadeInDown', 'animate__delay-1s');
    detailTitle.classList.add('animate__animated', 'animate__fadeInDown');
    detailRows.forEach((row, index) => {
        row.classList.remove('animate__fadeInUp', 'animate__delay-1s');
        row.classList.add('animate__animated', 'animate__fadeInUp', 'animate__faster');
        row.style.animationDelay = `${0.08 * index}s`;
    });
}

function clearDetailTextAnimation() {
    detailTitle.classList.remove('animate__animated', 'animate__fadeInDown', 'animate__delay-1s');
    detailRows.forEach(row => {
        row.classList.remove('animate__animated', 'animate__fadeInUp', 'animate__faster');
        row.style.animationDelay = '';
    });
}

function openDetailModal(button) {
    detailFields.name.innerHTML = escapeHtml(button.dataset.name);
    detailFields.expertise.innerHTML = escapeHtml(button.dataset.expertise || 'Expertise not specified.');
    detailFields.bio.innerHTML = escapeHtml(button.dataset.bio || 'No bio available.');

    detailModal.classList.add('active', 'animate__animated', 'animate__fadeIn');
    detailContent.classList.remove('animate__zoomOutDown');
    detailContent.classList.add('animate__animated', 'animate__zoomInDown', 'animate__faster');
    animateDetailText();
}

function closeDetailModal() {
    detailContent.classList.remove('animate__zoomInDown');
    detailContent.classList.add('animate__animated', 'animate__zoomOutDown', 'animate__faster');
    detailModal.classList.remove('animate__fadeIn');
    clearDetailTextAnimation();
    setTimeout(() => {
        detailModal.classList.remove('active');
    }, 250);
}

document.querySelectorAll('.instructor-detail-btn').forEach(button => {
    button.addEventListener('click', function() {
        openDetailModal(this);
    });
});

if (detailClose) {
    detailClose.addEventListener('click', closeDetailModal);
}

if (detailModal) {
    detailModal.addEventListener('click', function(event) {
        if (event.target === detailModal) {
            closeDetailModal();
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>