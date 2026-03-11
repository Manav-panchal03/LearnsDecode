<?php
session_start();
require '../config/config.php';

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$search = isset($_POST['search']) ? mysqli_real_escape_string($conn, $_POST['search']) : '';
$cat = isset($_POST['cat']) ? mysqli_real_escape_string($conn, $_POST['cat']) : '';

// 1. Query ma JOIN add karyu che jethi category name thi filter thai shake
$query = "SELECT c.*, u.name as instructor_name,
          (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id AND e.student_id = '$user_id') as is_purchased 
          FROM courses c 
          JOIN users u ON c.instructor_id = u.id
          LEFT JOIN categories cat ON c.category_id = cat.id 
          WHERE (c.status = 'published' OR c.status = 'active')
          AND (c.title LIKE '%$search%' OR c.description LIKE '%$search%')";

// 2. Jo category select kari hoy toh ena 'name' thi filter thase
if ($cat != '') {
    $query .= " AND cat.name = '$cat'";
}

$query .= " ORDER BY c.id DESC LIMIT 6";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $purchased = ($row['is_purchased'] > 0);
        $thumb = (!empty($row['thumbnail'])) ? "uploads/thumbnails/".$row['thumbnail'] : "https://via.placeholder.com/400x250";
        ?>
        <div class="col-md-4 animate__animated animate__fadeInUp">
            <div class="card border-0 shadow-sm h-100 hover-card overflow-hidden" style="position: relative; transition: 0.3s;">
                <?php if($purchased): ?>
                    <div style="position: absolute; top: 15px; right: 15px; background: #05cd99; color: white; padding: 5px 15px; border-radius: 12px; font-size: 0.8rem; font-weight: bold; z-index: 5;">
                        <i class="fas fa-check-circle me-1"></i> Enrolled
                    </div>
                <?php endif; ?>
                
                <div style="height: 200px; overflow: hidden;">
                    <img src="<?= $thumb ?>" class="card-img-top w-100 h-100" style="object-fit: cover;">
                </div>
                
                <div class="card-body">
                    <span class="badge bg-light text-primary mb-2"><?= ucfirst($row['level'] ?? 'Beginner') ?></span>
                    <h5 class="card-title fw-bold text-truncate" title="<?= $row['title'] ?>"><?= $row['title'] ?></h5>
                    <p class="text-muted small">By <?= $row['instructor_name'] ?></p>
                    
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <span class="h5 fw-bold text-primary mb-0">
                            <?= ($row['price'] > 0) ? '₹' . number_format($row['price'], 0) : 'Free'; ?>
                        </span>
                        <a href="course_details.php?id=<?= $row['id'] ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                            Explore
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
} else {
    echo '<div class="col-12 text-center py-5"><h5 class="text-muted">No courses found matching your criteria.</h5></div>';
}
?>