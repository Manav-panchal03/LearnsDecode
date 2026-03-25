<?php 
ob_start();
session_start();
require '../config/config.php'; 

// Login Check
if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// User Details
$user_q = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'");
$user = mysqli_fetch_assoc($user_q);

// Ketla courses ma enroll che te fetch karo
$courses_q = mysqli_query($conn, "SELECT e.*, c.title, c.thumbnail, c.id AS course_real_id, c.instructor_id,
                                         u.name AS instructor_name,
                                         COALESCE(NULLIF(p.profile_pic, ''), 'default-avatar.png') AS instructor_profile_pic,
                                         COALESCE(NULLIF(p.bio, ''), 'No instructor bio available.') AS instructor_bio,
                                         COALESCE(NULLIF(p.expertise, ''), 'Expertise not listed.') AS instructor_expertise
                                 FROM enrollments e
                                 JOIN courses c ON e.course_id = c.id
                                 LEFT JOIN users u ON c.instructor_id = u.id
                                 LEFT JOIN instructor_profiles p ON u.id = p.user_id
                                 WHERE e.student_id = '$user_id'
                                 ORDER BY e.enrolled_at DESC");

// Progress Function
function getCourseProgress($conn, $enrollment_id, $course_id) {
    $t_q = mysqli_query($conn, "SELECT COUNT(l.id) as total FROM lessons l 
                                JOIN units u ON l.unit_id = u.id 
                                WHERE u.course_id = '$course_id'");
    $total = mysqli_fetch_assoc($t_q)['total'] ?? 0;
    if($total == 0) return 0;

    $c_q = mysqli_query($conn, "SELECT COUNT(id) as done FROM lesson_progress 
                                WHERE enrollment_id = '$enrollment_id' AND is_completed = 1");
    $done = mysqli_fetch_assoc($c_q)['done'] ?? 0;
    return round(($done / $total) * 100);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - LearnsDecode</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f4f7fe; color: #2b3674; }
        .sidebar { width: 280px; height: 100vh; background: #fff; position: fixed; left: 0; top: 0; border-right: 1px solid #e9edf7; z-index: 1000; }
        .main-content { margin-left: 280px; padding: 40px; }
        .nav-link { color: #a3aed0; padding: 15px 25px; border-radius: 12px; margin: 5px 15px; font-weight: 600; transition: 0.3s; text-decoration: none; display: block; }
        .nav-link:hover, .nav-link.active { background: #4318ff; color: white !important; box-shadow: 0px 10px 20px rgba(67, 24, 255, 0.2); }
        .course-card { background: #fff; border-radius: 24px; border: none; overflow: hidden; transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); box-shadow: 0px 20px 40px rgba(0,0,0,0.02); height: 100%; display: flex; flex-direction: column; }
        .course-card:hover { transform: translateY(-10px); box-shadow: 0px 30px 60px rgba(67, 24, 255, 0.1); }
        .thumb-wrapper { height: 180px; overflow: hidden; position: relative; }
        .thumb-wrapper img { width: 100%; height: 100%; object-fit: cover; }
        .progress { height: 8px; background: #f0f2f5; border-radius: 10px; margin: 15px 0; }
        .progress-bar { background: #4318ff; border-radius: 10px; }
        .course-badge { position: absolute; top: 15px; right: 15px; background: rgba(255,255,255,0.9); padding: 5px 12px; border-radius: 10px; font-size: 0.75rem; font-weight: 700; color: #4318ff; }
        .btn-claim { background: #05cd99; color: white; border: none; box-shadow: 0px 10px 20px rgba(5, 205, 153, 0.3); }
        .btn-claim:hover { background: #04b386; color: white; transform: scale(1.02); }
        .btn-requested { background: #ffbc00; color: #fff; border: none; cursor: default !important; }
        @media (max-width: 992px) { .sidebar { display: none; } .main-content { margin-left: 0; } }
    </style>
</head>
<body>

<div class="sidebar d-flex flex-column p-3">
    <div class="text-center my-4"><h3 class="fw-bold text-primary">LearnsDecode</h3></div>
    <nav class="nav flex-column flex-grow-1">
        <a class="nav-link" href="dashboard.php"><i class="fas fa-home me-2"></i> Dashboard</a>
        <a class="nav-link active" href="my_courses.php"><i class="fas fa-book-reader me-2"></i> My Courses</a>
        <a class="nav-link d-flex justify-content-between align-items-center" href="inbox.php"><span><i class="fas fa-envelope me-2"></i> Inbox</span></a>
        <a class="nav-link" href="profile.php"><i class="fas fa-user-circle me-2"></i> Profile</a>
        <a class="nav-link" href="my_quizzes.php"><i class="fas fa-question-circle me-2"></i> My Quizzes</a>
    </nav>
    <div class="p-3 border-top">
        <div class="d-flex align-items-center">
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 45px; height: 45px; font-size: 1.2rem;">
                <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
            </div>
            <div class="ms-3 small">
                <div class="fw-bold text-dark"><?= $user['name'] ?></div>
                <a href="../logout.php" class="text-danger text-decoration-none fw-bold">Logout</a>
            </div>
        </div>
    </div>
</div>

<div class="main-content">
    <header class="mb-5 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold">My Learning Library</h2>
            <p class="text-muted">You are currently enrolled in <?= mysqli_num_rows($courses_q) ?> courses.</p>
        </div>
        <a href="../index.php" class="btn btn-primary rounded-pill px-4 fw-bold shadow">
            <i class="fas fa-plus me-2"></i> New Course
        </a>
    </header>

    <div class="row g-4">
        <?php if(mysqli_num_rows($courses_q) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($courses_q)): 
                $progress = getCourseProgress($conn, $row['id'], $row['course_real_id']);
                $c_id = $row['course_real_id'];
                
                // --- ADDED: Check Certificate Status ---
                $cert_check = mysqli_query($conn, "SELECT status, certificate_pdf FROM certificate_requests WHERE student_id = '$user_id' AND course_id = '$c_id'");
                $cert_data = mysqli_fetch_assoc($cert_check);
                $cert_status = $cert_data['status'] ?? null;
                // ----------------------------------------

                $u_q = mysqli_query($conn, "SELECT id FROM units WHERE course_id = '$c_id' ORDER BY order_no ASC LIMIT 1");
                $u_data = mysqli_fetch_assoc($u_q);
                $first_unit = $u_data['id'] ?? 0;
            ?>
            <div class="col-md-4" data-aos="fade-up">
                <div class="card course-card">
                    <div class="thumb-wrapper">
                        <img src="../uploads/thumbnails/<?= $row['thumbnail'] ?>" alt="Course">
                        <span class="course-badge shadow-sm">
                            <?= $progress == 100 ? '<i class="fas fa-check-circle text-success"></i> Finished' : 'Active' ?>
                        </span>
                    </div>
                    <div class="p-4 flex-grow-1 d-flex flex-column">
                        <h5 class="fw-bold text-dark mb-1 text-truncate"><?= $row['title'] ?></h5>
                        <p class="mb-2"><small class="text-secondary"><a href="javascript:void(0)" class="text-primary text-decoration-none" onclick="openInstructorModal(this)" data-instructor-name="<?= htmlspecialchars($row['instructor_name']) ?>" data-instructor-bio="<?= htmlspecialchars($row['instructor_bio']) ?>" data-instructor-expertise="<?= htmlspecialchars($row['instructor_expertise']) ?>"><?= htmlspecialchars($row['instructor_name'] ?: 'Unknown Instructor') ?></a></small></p>
                        <p class="text-muted small mb-3">Enrolled on <?= date('d M, Y', strtotime($row['enrolled_at'])) ?></p>
                        
                        <div class="d-flex justify-content-between small fw-bold mb-1">
                            <span>Progress</span>
                            <span class="text-primary"><?= $progress ?>%</span>
                        </div>
                        <div class="progress"><div class="progress-bar" style="width: <?= $progress ?>%"></div></div>
                        
                        <div class="d-grid gap-2 mt-auto">
                            <?php if($progress == 100): ?>
                                <?php if($cert_status == 'pending'): ?>
                                    <button class="btn btn-requested rounded-pill fw-bold py-2" disabled>
                                        <i class="fas fa-clock me-2"></i> Requested
                                    </button>
                                <?php elseif($cert_status == 'approved'): ?>
                                    <a href="../uploads/certificates/<?= $cert_data['certificate_pdf'] ?>" target="_blank" class="btn btn-primary rounded-pill fw-bold py-2">
                                        <i class="fas fa-eye me-2"></i> View Certificate
                                    </a>
                                <?php else: ?>
                                    <button id="claim-btn-<?= $c_id ?>" onclick="claimCertificate(<?= $c_id ?>, '<?= addslashes($row['title']) ?>')" class="btn btn-claim rounded-pill fw-bold py-2 animate__animated animate__pulse animate__infinite">
                                        <i class="fas fa-medal me-2"></i> Claim Certificate
                                    </button>
                                <?php endif; ?>
                                
                                <a href="watch.php?course_id=<?= $c_id ?>&unit_id=<?= $first_unit ?>" class="btn btn-outline-primary rounded-pill fw-bold py-2">Watch Again</a>
                            <?php else: ?>
                                <a href="watch.php?course_id=<?= $c_id ?>&unit_id=<?= $first_unit ?>" class="btn btn-primary rounded-pill fw-bold py-2 shadow-sm">Continue Learning</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Instructor Quick Profile Modal -->
<div class="modal fade" id="instructorModal" tabindex="-1" aria-labelledby="instructorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="instructorModalLabel">Instructor Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <h6 id="instructorModalName" class="fw-bold mb-3"></h6>
                <p id="instructorModalExpertise" class="text-muted mb-2"></p>
                <p id="instructorModalBio" class="small"></p>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

<script>
    AOS.init({ duration: 1000, once: true });

    function openInstructorModal(el) {
        var name = el.getAttribute('data-instructor-name') || 'Unknown Instructor';
        var bio = el.getAttribute('data-instructor-bio') || 'No instructor bio available.';
        var expertise = el.getAttribute('data-instructor-expertise') || 'Expertise not listed.';

        document.getElementById('instructorModalName').innerText = name;
        document.getElementById('instructorModalBio').innerText = bio;
        document.getElementById('instructorModalExpertise').innerText = expertise;

        var modal = new bootstrap.Modal(document.getElementById('instructorModal'));
        modal.show();
    }

    function claimCertificate(courseId, courseTitle) {
        confetti({ particleCount: 150, spread: 70, origin: { y: 0.6 }, colors: ['#4318ff', '#05cd99', '#ffbc00'] });

        Swal.fire({
            title: 'Congratulations! 🎓',
            html: `You have successfully completed <b>${courseTitle}</b>! <br> Would you like to request a certificate?`,
            icon: 'success',
            showCancelButton: true,
            confirmButtonColor: '#4318ff',
            confirmButtonText: 'Yes, Send Request!',
            showClass: { popup: 'animate__animated animate__zoomIn' },
            hideClass: { popup: 'animate__animated animate__zoomOut' }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'send_cert_request.php',
                    type: 'GET',
                    data: { course_id: courseId },
                    dataType: 'json',
                    success: function(response) {
                        if(response.status == 'success') {
                            Swal.fire('Requested!', 'Your request has been sent.', 'success').then(() => {
                                location.reload(); // Page reload thase etle "Requested" button avi jase
                            });
                        } else {
                            Swal.fire('Oops!', response.message, 'error');
                        }
                    }
                });
            }
        });
    }
</script>
</body>
</html>