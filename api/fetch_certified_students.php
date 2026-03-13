<?php
require '../config/config.php';

$search = isset($_POST['search']) ? mysqli_real_escape_string($conn, $_POST['search']) : '';

$query = "SELECT u.name, u.email, c.title as course_name, cr.issued_at, cr.certificate_pdf 
          FROM certificate_requests cr
          JOIN users u ON cr.student_id = u.id
          JOIN courses c ON cr.course_id = c.id
          WHERE (u.name LIKE '%$search%' OR c.title LIKE '%$search%')
          AND cr.status = 'approved' 
          ORDER BY cr.issued_at DESC";

$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $delay = 100;
    while ($row = mysqli_fetch_assoc($result)) {
        $compl_date = date('d M, Y', strtotime($row['issued_at']));
        $cert_link = "../uploads/certificates/" . $row['certificate_pdf'];
        
        // Final protection against quotes breaking JS
        $js_name = htmlspecialchars(addslashes($row['name']), ENT_QUOTES);
        $js_course = htmlspecialchars(addslashes($row['course_name']), ENT_QUOTES);
        $js_email = htmlspecialchars($row['email'], ENT_QUOTES);
        ?>
        <div class="col-md-4 col-lg-3" data-aos="zoom-in" data-aos-delay="<?= $delay ?>">
            <div class="card cert-card h-100 border-0 shadow-sm rounded-4 p-4 text-center" 
                 onclick="showStudentInfo('<?= $js_name ?>', '<?= $js_course ?>', '<?= $compl_date ?>', '<?= $js_email ?>', '<?= $cert_link ?>')">
                
                <div class="avatar-circle mb-3">
                    <?= strtoupper(substr($row['name'], 0, 1)) ?>
                </div>
                
                <h6 class="fw-bold text-dark mt-2 mb-1 text-truncate"><?= $row['name'] ?></h6>
                <p class="text-muted small mb-0 text-truncate"><?= $row['course_name'] ?></p>
                
                <hr class="my-3 opacity-50">
                
                <div class="d-flex justify-content-between align-items-center">
                    <span class="badge bg-light text-success rounded-pill">Certified</span>
                    <small class="text-muted" style="font-size: 0.7rem;"><?= $compl_date ?></small>
                </div>
            </div>
        </div>
        <?php
        $delay += 50;
    }
} else {
    echo '<div class="col-12 text-center py-5">No certified students found.</div>';
}
?>