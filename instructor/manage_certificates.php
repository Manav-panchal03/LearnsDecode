<?php
session_start();
require '../config/config.php';

if(!isset($_SESSION['user_id'])){ 
    header("Location: ../login.php"); 
    exit(); 
}

$inst_id = $_SESSION['user_id']; 

$query = "SELECT r.*, u.name as student_name, c.title as course_title 
          FROM certificate_requests r
          JOIN users u ON r.student_id = u.id
          JOIN courses c ON r.course_id = c.id
          WHERE c.instructor_id = '$inst_id' 
          ORDER BY r.status DESC, r.requested_at DESC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Requests Management | LearnsDecode</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <style>
        body { background: #f4f7fe; font-family: 'Plus Jakarta Sans', sans-serif; color: #2b3674; }
        .glass-header { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px); border-radius: 20px; padding: 30px; margin-bottom: 40px; border: 1px solid rgba(255,255,255,0.3); }
        .req-card { background: white; border-radius: 24px; padding: 20px; border: 1px solid #e9edf7; transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); margin-bottom: 20px; }
        .req-card:hover { transform: scale(1.02); box-shadow: 0 20px 40px rgba(67, 24, 255, 0.08); border-color: #4318ff; }
        .status-badge { padding: 6px 16px; border-radius: 12px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
        .status-pending { background: #fff7e6; color: #ffbc00; }
        .status-approved { background: #e6fffb; color: #05cd99; }
        .btn-issue { background: #2b3674; color: white; border-radius: 14px; transition: 0.3s; border: none; }
        .btn-issue:hover { background: #4318ff; color: white; transform: translateY(-3px); box-shadow: 0 10px 20px rgba(67, 24, 255, 0.2); }
        .btn-view { background: #05cd99; color: white; border-radius: 14px; border: none; }
        .btn-view:hover { background: #04b386; color: white; box-shadow: 0 10px 20px rgba(5, 205, 153, 0.2); }
        .icon-box { width: 50px; height: 50px; border-radius: 15px; background: #f4f7fe; display: flex; align-items: center; justify-content: center; color: #4318ff; font-size: 1.2rem; }
        .btn-back { background: white; color: #2b3674; border: 1px solid #e9edf7; border-radius: 12px; font-weight: 600; transition: 0.3s; }
        .btn-back:hover { background: #f4f7fe; color: #4318ff; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeIn">
        <a href="dashboard.php" class="btn btn-back px-3 py-2">
            <i class="fas fa-chevron-left me-2"></i> Back to Dashboard
        </a>
        <div class="badge bg-white text-primary shadow-sm p-2 px-3 rounded-pill fw-bold">
            Instructor Panel
        </div>
    </div>

    <div class="glass-header animate__animated animate__fadeInDown d-flex justify-content-between align-items-center">
        <div>
            <h1 class="fw-bold mb-1">Certificate Center 🎓</h1>
            <p class="text-muted mb-0">Review and validate student achievements</p>
        </div>
        <div class="text-end">
            <div class="h3 fw-bold text-primary mb-0"><?= mysqli_num_rows($result) ?></div>
            <small class="text-uppercase fw-bold text-muted">Total Requests</small>
        </div>
    </div>

    <div class="row">
        <?php if(mysqli_num_rows($result) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($result)): 
                $is_approved = ($row['status'] == 'approved');
            ?>
            <div class="col-12 animate__animated animate__fadeInUp">
                <div class="req-card d-flex align-items-center shadow-sm">
                    <div class="icon-box me-4">
                        <i class="fas <?= $is_approved ? 'fa-certificate' : 'fa-user-graduate' ?>"></i>
                    </div>
                    
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center mb-1">
                            <h5 class="fw-bold mb-0 me-3 text-dark"><?= $row['student_name'] ?></h5>
                            <span class="status-badge <?= $is_approved ? 'status-approved' : 'status-pending' ?>">
                                <?= $row['status'] ?>
                            </span>
                        </div>
                        <p class="text-muted mb-0">Mastered: <span class="text-primary fw-bold"><?= $row['course_title'] ?></span></p>
                        <small class="text-secondary"><i class="far fa-calendar-alt me-1"></i> <?= date('d M, Y', strtotime($row['requested_at'])) ?></small>
                    </div>

                    <div id="action-box-<?= $row['id'] ?>">
                        <?php if($is_approved): ?>
                            <a href="../uploads/certificates/<?= $row['certificate_pdf'] ?>" target="_blank" class="btn btn-view px-4 py-2 fw-bold">
                                <i class="fas fa-eye me-2"></i> View Certificate
                            </a>
                        <?php else: ?>
                            <button onclick="handleIssue(<?= $row['id'] ?>)" class="btn btn-issue px-4 py-2 fw-bold">
                                <i class="fas fa-magic me-2"></i> Issue Now
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5 mt-5 animate__animated animate__pulse">
                <i class="fas fa-inbox fa-4x text-light mb-3"></i>
                <h5 class="text-muted">All caught up! No new requests.</h5>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

<script>
function handleIssue(requestId) {
    Swal.fire({
        title: 'Ready to Certify?',
        text: "This will generate a superb PDF for the student!",
        icon: 'info',
        showCancelButton: true,
        confirmButtonColor: '#4318ff',
        cancelButtonColor: '#a3aed0',
        confirmButtonText: 'Yes, Issue Certificate!',
        background: '#fff',
        showClass: { popup: 'animate__animated animate__zoomIn' }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Generating Excellence...',
                html: 'Please wait while we craft the PDF.',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
            window.location.href = 'generate_pdf.php?request_id=' + requestId;
        }
    });
}
</script>
</body>
</html>