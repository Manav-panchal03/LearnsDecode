<?php
require '../config/config.php';

$search = isset($_POST['search']) ? mysqli_real_escape_string($conn, $_POST['search']) : '';

$query = "SELECT u.id, u.name, u.email,
                 GROUP_CONCAT(c.title ORDER BY e.enrolled_at DESC SEPARATOR '|||') AS course_names,
                 GROUP_CONCAT(e.enrolled_at ORDER BY e.enrolled_at DESC SEPARATOR '|||') AS enrolled_dates,
                 GROUP_CONCAT(e.amount ORDER BY e.enrolled_at DESC SEPARATOR '|||') AS amounts,
                 GROUP_CONCAT(e.transaction_id ORDER BY e.enrolled_at DESC SEPARATOR '|||') AS txids,
                 GROUP_CONCAT(e.payment_status ORDER BY e.enrolled_at DESC SEPARATOR '|||') AS statuses,
                 COUNT(e.id) AS total_courses
          FROM enrollments e
          JOIN users u ON e.student_id = u.id
          JOIN courses c ON e.course_id = c.id
          WHERE (u.name LIKE '%$search%' OR c.title LIKE '%$search%' OR u.email LIKE '%$search%')
          GROUP BY u.id
          ORDER BY MAX(e.enrolled_at) DESC";

$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $delay = 100;
    while ($row = mysqli_fetch_assoc($result)) {
        $course_names = explode('|||', $row['course_names']);
        $enrolled_dates = explode('|||', $row['enrolled_dates']);
        $amounts = explode('|||', $row['amounts']);
        $txids = explode('|||', $row['txids']);
        $statuses = explode('|||', $row['statuses']);
        $latest_date = date('d M, Y', strtotime($enrolled_dates[0] ?? 'now'));
        $total_amount = array_sum($amounts);

        $details = [
            'courses' => $course_names,
            'dates' => $enrolled_dates,
            'amounts' => $amounts,
            'txids' => $txids,
            'statuses' => $statuses
        ];
        $details_json = htmlspecialchars(json_encode($details, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
        $js_name = htmlspecialchars($row['name'], ENT_QUOTES);
        $js_email = htmlspecialchars($row['email'], ENT_QUOTES);
        ?>
        <div class="col-md-6 col-lg-4" data-aos="zoom-in" data-aos-delay="<?= $delay ?>">
            <div class="card enroll-card shadow-sm p-4"
                 data-name="<?= $js_name ?>"
                 data-email="<?= $js_email ?>"
                 data-details="<?= $details_json ?>"
                 onclick="viewEnrollDetails(this)"
                 style="cursor: pointer;">

                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 50px; height: 50px; font-size: 1.2rem;">
                        <?= strtoupper(substr($row['name'], 0, 1)) ?>
                    </div>
                    <div class="ms-3">
                        <h6 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($row['name']) ?></h6>
                        <small class="text-muted"><?= htmlspecialchars($row['email']) ?></small>
                    </div>
                </div>

                <div class="mb-3">
                    <?php foreach ($course_names as $course_name): ?>
                        <span class="course-tag text-truncate d-inline-block me-1 mb-1" style="max-width: calc(100% - 20px);">
                            <i class="fas fa-graduation-cap me-1"></i> <?= htmlspecialchars($course_name) ?>
                        </span>
                    <?php endforeach; ?>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                    <div class="small text-muted"><?= count($course_names) ?> course<?= count($course_names) === 1 ? '' : 's' ?> enrolled</div>
                    <div class="fw-bold text-dark">₹<?= number_format($total_amount, 2) ?></div>
                </div>
                
                <div class="mt-2 text-end">
                    <small class="text-muted" style="font-size: 0.7rem;"><i class="far fa-clock me-1"></i><?= $latest_date ?></small>
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