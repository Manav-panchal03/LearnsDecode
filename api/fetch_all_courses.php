<?php
session_start();
require '../config/config.php';

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$search = isset($_POST['search']) ? mysqli_real_escape_string($conn, $_POST['search']) : '';

// Query remains the same
$query = "SELECT c.*, u.name as instructor_name, 
          (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id AND e.student_id = '$user_id') as is_purchased 
          FROM courses c 
          JOIN users u ON c.instructor_id = u.id
          WHERE (c.status = 'published' OR c.status = 'active') 
          AND (c.title LIKE '%$search%' OR c.description LIKE '%$search%')
          ORDER BY c.id DESC";

$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $delay = 100; // Animation delay
    while ($row = mysqli_fetch_assoc($result)) {
        $purchased = ($row['is_purchased'] > 0);
        $thumb = (!empty($row['thumbnail'])) ? "uploads/thumbnails/".$row['thumbnail'] : "https://via.placeholder.com/400x250";
        ?>
        <div class="col-lg-3 col-md-4 col-sm-6" data-aos="fade-up" data-aos-delay="<?= $delay ?>">
            <div class="card h-100 hover-card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="course-thumb-container" style="height: 180px; overflow: hidden; position: relative;">
                    <?php if($purchased): ?>
                        <div style="position: absolute; top: 10px; right: 10px; background: #05cd99; color: white; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; z-index: 5;">
                           <i class="fas fa-check"></i> Enrolled
                        </div>
                    <?php endif; ?>
                    <img src="<?= $thumb ?>" class="w-100 h-100" style="object-fit: cover;">
                </div>
                
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between mb-2">
                        <small class="text-primary fw-bold"><?= ucfirst($row['level'] ?? 'All Levels') ?></small>
                        <?php if($purchased): ?>
                            <small class="text-success fw-bold" style="font-size: 11px;">Already Purchased</small>
                        <?php endif; ?>
                    </div>
                    
                    <h6 class="fw-bold mb-1" style="height: 45px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;"><?= $row['title'] ?></h6>
                    <p class="text-muted small mb-3">By <?= $row['instructor_name'] ?></p>
                    
                    <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                        <span class="h6 fw-bold mb-0 text-dark">₹<?= number_format($row['price'], 0) ?></span>
                        <a href="course_details.php?id=<?= $row['id'] ?>" class="btn btn-sm <?= $purchased ? 'btn-success' : 'btn-outline-primary' ?> rounded-pill px-3 fw-bold">
                            Explore
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php
        $delay += 100; 
    }
} else {
    echo '<div class="col-12 text-center py-5"><p class="text-muted">No courses found matching your search.</p></div>';
}
?>