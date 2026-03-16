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

// User details fetch
$user_q = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'");
$user = mysqli_fetch_assoc($user_q);

// Enrollment Check
$resume_q = mysqli_query($conn, "SELECT e.*, c.title, c.thumbnail FROM enrollments e 
                                JOIN courses c ON e.course_id = c.id 
                                WHERE e.student_id = '$user_id' ORDER BY e.enrolled_at DESC LIMIT 1");
$resume_course = mysqli_fetch_assoc($resume_q);
$enrollment_id = $resume_course['id'] ?? 0;

// --- SMART RESUME LOGIC ---
$resume_url = "javascript:void(0);"; 
$has_enrollment = ($resume_course) ? true : false;

if($has_enrollment) {
    $c_id = $resume_course['course_id'];
    
    // 1. Find the first unit that has incomplete lessons
    $find_unit_q = mysqli_query($conn, "SELECT u.id FROM units u 
        WHERE u.course_id = '$c_id' 
        AND u.id NOT IN (
            SELECT l.unit_id FROM lessons l
            JOIN lesson_progress lp ON l.id = lp.lesson_id
            WHERE lp.enrollment_id = '$enrollment_id' AND lp.is_completed = 1
            GROUP BY l.unit_id
            HAVING COUNT(lp.id) = (SELECT COUNT(id) FROM lessons WHERE unit_id = l.unit_id)
        )
        ORDER BY u.order_no ASC LIMIT 1");

    $unit_to_resume = mysqli_fetch_assoc($find_unit_q);
    
    if($unit_to_resume) {
        $r_unit_id = $unit_to_resume['id'];
        $resume_url = "watch.php?course_id=$c_id&unit_id=$r_unit_id";
    } else {
        // 2. If all completed, go to first unit
        $first_unit_q = mysqli_query($conn, "SELECT id FROM units WHERE course_id = '$c_id' ORDER BY order_no ASC LIMIT 1");
        $first_unit = mysqli_fetch_assoc($first_unit_q);
        $r_unit_id = $first_unit['id'] ?? 0;
        $resume_url = "watch.php?course_id=$c_id&unit_id=$r_unit_id";
    }
}

function getCourseProgress($conn, $enrollment_id, $course_id) {
    $t_q = mysqli_query($conn, "SELECT COUNT(l.id) as total FROM lessons l 
                                JOIN units u ON l.unit_id = u.id 
                                WHERE u.course_id = '$course_id'");
    $total_res = mysqli_fetch_assoc($t_q);
    $total = $total_res['total'] ?? 0;
    if($total == 0) return 0;
    $c_q = mysqli_query($conn, "SELECT COUNT(id) as done FROM lesson_progress 
                                WHERE enrollment_id = '$enrollment_id' AND is_completed = 1");
    $done_res = mysqli_fetch_assoc($c_q);
    $done = $done_res['done'] ?? 0;
    return round(($done / $total) * 100);
}

$current_progress = $resume_course ? getCourseProgress($conn, $enrollment_id, $resume_course['course_id']) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LearnsDecode - Smart Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f4f7fe; color: #2b3674; overflow-x: hidden; }
        .sidebar { width: 280px; height: 100vh; background: #fff; position: fixed; left: 0; top: 0; border-right: 1px solid #e9edf7; z-index: 1000; transition: 0.3s; }
        .main-content { margin-left: 280px; padding: 40px; transition: 0.3s; }
        .nav-link { color: #a3aed0; padding: 15px 25px; border-radius: 12px; margin: 5px 15px; font-weight: 600; transition: 0.3s; }
        .nav-link:hover, .nav-link.active { background: #4318ff; color: white !important; box-shadow: 0px 10px 20px rgba(67, 24, 255, 0.2); }
        .resume-card { background: linear-gradient(135deg, #4318ff 0%, #707eae 100%); border: none; border-radius: 30px; color: white; overflow: hidden; position: relative; padding: 40px; }
        .resume-card img { opacity: 0.2; position: absolute; right: -30px; top: -30px; width: 300px; transform: rotate(-10deg); pointer-events: none; }
        .unit-box { border-radius: 20px; border: none; transition: 0.3s; background: #fff; box-shadow: 0px 15px 35px rgba(0,0,0,0.03); }
        .unit-locked { opacity: 0.7; filter: grayscale(1); cursor: not-allowed; pointer-events: none; background: #f8fafc; }
        .progress { height: 10px; border-radius: 10px; background: rgba(255,255,255,0.2); }
        .hover-up:hover { transform: translateY(-5px); transition: 0.3s; }
        @media (max-width: 992px) { .sidebar { left: -280px; } .main-content { margin-left: 0; } }
    </style>
</head>
<body>

<div class="sidebar d-flex flex-column p-3 shadow-sm">
    <div class="text-center my-4">
        <h3 class="fw-bold text-primary">LearnsDecode</h3>
    </div>
    <nav class="nav flex-column flex-grow-1">
        <a class="nav-link active" href="dashboard.php"><i class="fas fa-home me-2"></i> Dashboard</a>
        <a class="nav-link" href="my_courses.php"><i class="fas fa-book-reader me-2"></i> My Courses</a>
        <a class="nav-link d-flex justify-content-between align-items-center" href="inbox.php">
            <span><i class="fas fa-envelope me-2"></i> Inbox</span>
            <!-- <span class="badge bg-danger rounded-pill">2</span> -->
        </a>
        <a class="nav-link" href="profile.php"><i class="fas fa-user-circle me-2"></i> Profile</a>
        <a class="nav-link" href="my_quizzes.php"><i class="fas fa-question-circle me-2"></i> My Quizzes</a>
        <a class="nav-link" href="../index.php"><i class="fas fa-arrow-left me-2"></i> Back to Home </a>
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
    <header class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold mb-0">Hello, <?= explode(' ', $user['name'])[0] ?>! </h2>
            <p class="text-muted">Nice to see you again in your learning journey.</p>
        </div>
        <div class="badge bg-white text-primary rounded-pill p-3 shadow-sm border">
            <i class="fas fa-calendar-alt me-2"></i> <?= date('d M, Y') ?>
        </div>
    </header>

    <?php if($resume_course): ?>
    <section class="mb-5" data-aos="fade-up">
        <div class="resume-card shadow-lg">
            <img src="../uploads/thumbnails/<?= $resume_course['thumbnail'] ?>" alt="Course Thumbnail">
            <div class="position-relative" style="z-index: 2;">
                <span class="badge bg-white text-primary mb-3 px-3 py-2 rounded-pill fw-bold">RESUME LEARNING</span>
                <h1 class="fw-bold mb-3"><?= $resume_course['title'] ?></h1>
                
                <div class="col-md-5 mb-4">
                    <div class="d-flex justify-content-between mb-2 small fw-bold">
                        <span>Course Progress</span>
                        <span><?= $current_progress ?>% Complete</span>
                    </div>
                    <div class="progress shadow-sm">
                        <div class="progress-bar bg-white rounded-pill animate__animated animate__slideInLeft" style="width: <?= $current_progress ?>%"></div>
                    </div>
                </div>
                
                <a href="<?= $resume_url ?>" onclick="<?= !$has_enrollment ? 'noResumeAlert()' : '' ?>" class="btn btn-light btn-lg rounded-pill px-5 fw-bold text-primary shadow hover-up">
                    Continue Watching <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <section class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold text-dark">Course Curriculum</h4>
            <span class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill" style="background: rgba(67, 24, 255, 0.1);">Step-by-step Learning</span>
        </div>

        <div class="row g-4">
            <?php 
            if($resume_course) {
                $c_id = $resume_course['course_id'];
                $units_q = mysqli_query($conn, "SELECT * FROM units WHERE course_id = '$c_id' ORDER BY order_no ASC");
                $prev_unit_completed = true; 

                while($u = mysqli_fetch_assoc($units_q)): 
                    $u_id = $u['id'];
                    $total_l_q = mysqli_query($conn, "SELECT COUNT(id) as total FROM lessons WHERE unit_id = '$u_id'");
                    $total_l = mysqli_fetch_assoc($total_l_q)['total'] ?? 0;

                    $done_l_q = mysqli_query($conn, "SELECT COUNT(lp.id) as done FROM lesson_progress lp 
                                                    JOIN lessons l ON lp.lesson_id = l.id 
                                                    WHERE lp.enrollment_id = '$enrollment_id' 
                                                    AND l.unit_id = '$u_id' AND lp.is_completed = 1");
                    $done_l = mysqli_fetch_assoc($done_l_q)['done'] ?? 0;

                    $isDone = ($total_l > 0 && $total_l == $done_l);
                    $isLocked = !$prev_unit_completed;
            ?>
            <div class="col-md-6" data-aos="zoom-in-up">
                <div class="card unit-box p-4 shadow-sm <?= $isLocked ? 'unit-locked' : '' ?>">
                    <?php if($isLocked): ?> 
                        <div class="position-absolute top-0 end-0 m-3 text-muted"><i class="fas fa-lock"></i></div>
                    <?php endif; ?>
                    
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle p-3 me-3 shadow-sm <?= $isDone ? 'bg-success text-white' : ($isLocked ? 'bg-secondary text-white' : 'bg-primary text-white') ?>" style="width: 55px; height: 55px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas <?= $isDone ? 'fa-check' : 'fa-play' ?>"></i>
                        </div>
                        <div><h5 class="fw-bold mb-0"><?= $u['unit_title'] ?></h5></div>
                    </div>
                    
                    <div class="mt-2">
                        <?php if($isDone): ?>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="alert alert-success py-2 px-3 border-0 rounded-pill mb-0 small">
                                    <i class="fas fa-medal me-1"></i> Completed!
                                </div>
                                <a href="watch.php?course_id=<?= $c_id ?>&unit_id=<?= $u_id ?>" class="btn btn-sm btn-outline-success rounded-pill px-4 fw-bold">
                                    Review Lessons <i class="fas fa-redo ms-1" style="font-size: 0.8rem;"></i>
                                </a>
                            </div>
                        <?php elseif($isLocked): ?>
                            <p class="text-muted small mb-0"><i class="fas fa-info-circle me-1"></i> Finish previous unit to unlock.</p>
                        <?php else: ?>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-primary small fw-bold"><i class="fas fa-tasks me-1"></i> <?= $done_l ?> / <?= $total_l ?> Done</div>
                                <a href="watch.php?course_id=<?= $c_id ?>&unit_id=<?= $u_id ?>" class="btn btn-sm btn-primary rounded-pill px-4 shadow-sm fw-bold">Start Learning</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php 
                $prev_unit_completed = $isDone; 
                endwhile; 
            } else {
                echo "<div class='col-12 text-center py-5'>
                        <h5 class='fw-bold'>No Active Courses Found</h5>
                        <a href='../index.php' class='btn btn-primary rounded-pill mt-3'>Explore Courses</a>
                      </div>";
            }
            ?>
        </div>

        <?php if($resume_course && $current_progress == 100): 
            
            // 1. Check if user already reviewed this course
        $c_id = $resume_course['course_id'];
        $user_id = $_SESSION['user_id'];
        $rev_check_q = mysqli_query($conn, "SELECT id FROM reviews WHERE course_id = '$c_id' AND user_id = '$user_id'");
        
        // 2. Card tyare j dekhadvo jyare review na apyo hoy
        if(mysqli_num_rows($rev_check_q) == 0):
            
            ?>
    <section class="mb-5 mt-3 animate__animated animate__bounceInUp">
        <div class="card border-0 shadow-lg p-4 text-center" style="border-radius: 30px; background: #fff;">
            <div class="mb-3">
                <span class="badge bg-success-soft text-success px-4 py-2 rounded-pill" style="background: rgba(25, 135, 84, 0.1);">
                    🎉 CONGRATULATIONS!
                </span>
            </div>
            <h3 class="fw-bold">You've Completed the Course!</h3>
            <p class="text-muted">How was your learning experience with LearnsDecode? Your feedback helps us grow.</p>
            
            <form id="ratingForm" class="mt-3">
                <input type="hidden" name="course_id" value="<?= $resume_course['course_id'] ?>">
                
                <div class="star-rating-wrapper mb-3">
                    <div class="star-rating">
                        <input type="radio" id="star-5" name="rating" value="5" required /><label for="star-5" class="fas fa-star"></label>
                        <input type="radio" id="star-4" name="rating" value="4" /><label for="star-4" class="fas fa-star"></label>
                        <input type="radio" id="star-3" name="rating" value="3" /><label for="star-3" class="fas fa-star"></label>
                        <input type="radio" id="star-2" name="rating" value="2" /><label for="star-2" class="fas fa-star"></label>
                        <input type="radio" id="star-1" name="rating" value="1" /><label for="star-1" class="fas fa-star"></label>
                    </div>
                </div>

                <div class="col-md-8 mx-auto mb-4">
                    <textarea name="comment" class="form-control border-0 bg-light p-3" rows="3" placeholder="Share your thoughts about this course..." style="border-radius: 15px;"></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-sm hover-up">
                    Submit Review <i class="fas fa-paper-plane ms-2"></i>
                </button>
            </form>
        </div>
    </section>

    <style>
        .star-rating { direction: rtl; display: inline-block; }
        .star-rating input { display: none; }
        .star-rating label { color: #e9edf7; font-size: 2.5rem; cursor: pointer; transition: 0.3s; padding: 0 5px; }
        .star-rating label:hover, .star-rating label:hover ~ label, .star-rating input:checked ~ label { color: #ffc107; transform: scale(1.1); }
    </style>
    <?php endif; 
    endif; ?>
    </section>
</div>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    AOS.init({ duration: 800, once: true });

    function noResumeAlert() {
        Swal.fire({
            title: 'No Active Course! 🔍',
            text: 'Your feedback is saved successfully !',
            icon: 'info',
            confirmButtonColor: '#4318ff'
        });
    }

    const urlParams = new URLSearchParams(window.location.search);
    if(urlParams.get('order') === 'success') {
        Swal.fire({ title: 'Successfully Enrolled! 🚀', icon: 'success', confirmButtonColor: '#4318ff' });
    }
</script>

<script>
// --- RATING SUBMISSION WORKFLOW ---
const ratingForm = document.getElementById('ratingForm');
if(ratingForm) {
    ratingForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('save_review.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                Swal.fire({
                    title: 'Thank You! 🌟',
                    text: 'Tamari rating successfully save thai gai che.',
                    icon: 'success',
                    confirmButtonColor: '#4318ff'
                }).then(() => location.reload());
            } else {
                Swal.fire({ title: 'Oops!', text: data.message, icon: 'warning' });
            }
        });
    });
}
</script>
</body>
</html>