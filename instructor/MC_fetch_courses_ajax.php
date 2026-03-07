<?php
session_start();
require '../config/config.php';
require_once '../includes/session_utils.php';

// activate instructor role
activateRole('instructor');

// security check
if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'instructor'){
    http_response_code(403);
    exit();
}

$uid = $_SESSION['user_id'];
$search = isset($_POST['search']) ? mysqli_real_escape_string($conn, $_POST['search']) : '';
$status_filter = isset($_POST['status']) ? mysqli_real_escape_string($conn, $_POST['status']) : 'All';

$sql = "SELECT c.*, 
        (SELECT COUNT(*) FROM units WHERE course_id = c.id) as total_sections,
        (SELECT COUNT(l.id) FROM lessons l JOIN units u ON l.unit_id = u.id WHERE u.course_id = c.id) as total_lessons
        FROM courses c 
        WHERE c.instructor_id = $uid";

// Search filter logic
if(!empty($search)){
    $sql .= " AND c.title LIKE '%$search%'";
}

// Category/Status filter logic
if($status_filter != 'All'){
    $status_val = ($status_filter == 'Published') ? 'published' : 'draft';
    $sql .= " AND c.status = '$status_val'";
}

$sql .= " ORDER BY c.id DESC";
$result = mysqli_query($conn, $sql);

// ... ઉપરનો PHP કોડ (session, config, sql) એમનેમ રહેવા દેવો ...
if(mysqli_num_rows($result) > 0){
    while($row = mysqli_fetch_assoc($result)){
        // Status logic
        $isPublished = ($row['status'] == 'published' || $row['status'] == 'active');
        $statusClass = $isPublished ? 'text-success' : 'text-warning';
        $dotClass = $isPublished ? 'bg-success' : 'bg-warning';
        $statusLabel = ucfirst($row['status']);
        ?>
        
        <div class="course-row p-3 shadow-sm d-flex align-items-center justify-content-between animate__animated animate__fadeInUp">
            <div class="d-flex align-items-center gap-3">
                <img src="../uploads/thumbnails/<?= $row['thumbnail'] ?: 'course-default.jpg' ?>" class="course-img shadow-sm">
                <div>
                    <h6 class="fw-bold mb-1"><?= $row['title'] ?></h6>
                    <div class="d-flex gap-3 small text-muted">
                        <span><i class="fas fa-layer-group me-1"></i><?= $row['total_sections'] ?> Units</span>
                        <span><i class="fas fa-play-circle me-1"></i><?= $row['total_lessons'] ?> Lessons</span>
                        <span class="fw-bold text-primary">₹<?= number_format($row['price'], 2) ?></span>
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <div class="d-none d-md-block">
                    <span class="badge bg-light <?= $statusClass ?> rounded-pill px-3 py-2 border shadow-sm" style="font-weight: 500;">
                        <span class="status-dot <?= $dotClass ?>"></span> <?= $statusLabel ?>
                    </span>
                </div>
                
                <div class="actions d-flex gap-1">
                    <a href="view_course.php?id=<?= $row['id'] ?>" class="action-btn btn-edit" title="View">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="add_course.php?edit_id=<?= $row['id'] ?>" class="action-btn btn-edit" title="Edit">
                        <i class="fas fa-pen"></i>
                    </a>
                    <a href="delete_course.php?id=<?= $row['id'] ?>" class="action-btn btn-del" onclick="return confirm('Are you sure you want to delete this course?')" title="Delete">
                        <i class="fas fa-trash"></i>
                    </a>
                </div>
            </div>
        </div>

        <?php
    }
} else {
    echo '<div class="text-center py-5 animate__animated animate__fadeIn">
            <div class="mb-3">
                <i class="fas fa-search text-muted" style="font-size: 4rem; opacity: 0.3;"></i>
            </div>
            <h5 class="fw-bold text-secondary">No Courses Found !</h5>
            <p class="text-muted">Try clearing your search or filter.</p>
            <button class="btn btn-outline-primary btn-sm rounded-pill mt-2" onclick="location.reload()">
                Clear Search
            </button>
        </div>';
}
?>