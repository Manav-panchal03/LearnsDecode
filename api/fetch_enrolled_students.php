<?php
require '../config/config.php';

$search = isset($_POST['search']) ? mysqli_real_escape_string($conn, $_POST['search']) : '';

$query = "SELECT u.name, u.email, c.title as course_name, e.enrolled_at, e.payment_status, e.transaction_id, e.amount 
          FROM enrollments e
          JOIN users u ON e.student_id = u.id
          JOIN courses c ON e.course_id = c.id
          WHERE (u.name LIKE '%$search%' OR c.title LIKE '%$search%' OR u.email LIKE '%$search%')
          ORDER BY e.enrolled_at DESC";

$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $delay = 100;
    while ($row = mysqli_fetch_assoc($result)) {
        $date = date('d M, Y', strtotime($row['enrolled_at']));
        $status_color = ($row['payment_status'] == 'completed') ? '#05cd99' : '#ffb800';
        
        $js_name = htmlspecialchars(addslashes($row['name']), ENT_QUOTES);
        $js_course = htmlspecialchars(addslashes($row['course_name']), ENT_QUOTES);
        ?>
        <div class="col-md-6 col-lg-4" data-aos="zoom-in" data-aos-delay="<?= $delay ?>">
            <div class="card enroll-card shadow-sm p-4" 
                 onclick="viewEnrollDetails('<?= $js_name ?>', '<?= $row['email'] ?>', '<?= $js_course ?>', '<?= $date ?>', '<?= $row['amount'] ?>', '<?= $row['transaction_id'] ?>', '<?= $row['payment_status'] ?>')" 
                 style="cursor: pointer;">
                
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 50px; height: 50px; font-size: 1.2rem;">
                        <?= strtoupper(substr($row['name'], 0, 1)) ?>
                    </div>
                    <div class="ms-3">
                        <h6 class="fw-bold mb-0 text-dark"><?= $row['name'] ?></h6>
                        <small class="text-muted"><?= $row['email'] ?></small>
                    </div>
                </div>

                <div class="mb-3">
                    <span class="course-tag text-truncate d-inline-block" style="max-width: 100%;">
                        <i class="fas fa-graduation-cap me-1"></i> <?= $row['course_name'] ?>
                    </span>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                    <div class="small">
                        <span class="payment-status" style="background: <?= $status_color ?>"></span>
                        <span class="text-muted"><?= ucfirst($row['payment_status']) ?></span>
                    </div>
                    <div class="fw-bold text-dark">₹<?= $row['amount'] ?></div>
                </div>
                
                <div class="mt-2 text-end">
                    <small class="text-muted" style="font-size: 0.7rem;"><i class="far fa-clock me-1"></i><?= $date ?></small>
                </div>
            </div>
        </div>
        <?php
        $delay += 50;
    }
} else {
    echo '<div class="col-12 text-center py-5">
            <img src="../assets/img/no-data.svg" style="width: 150px; opacity: 0.5;">
            <p class="text-muted mt-3">No enrollments found.</p>
          </div>';
}
?>