<?php 
ob_start();
session_start(); // Login status check karva mate session_start() must che
require 'config/config.php'; 
include 'includes/header.php'; 

$course_id = mysqli_real_escape_string($conn, $_GET['id']);

// 1. Course & Instructor Info Fetch
$course_q = mysqli_query($conn, "SELECT c.*, u.name as instructor_name FROM courses c JOIN users u ON c.instructor_id = u.id WHERE c.id = '$course_id'");
$course = mysqli_fetch_assoc($course_q);

// 2. Units Fetch
$units_res = mysqli_query($conn, "SELECT * FROM units WHERE course_id = '$course_id' ORDER BY order_no ASC");

// Login status check karo
$is_logged_in = isset($_SESSION['user_id']) ? 'true' : 'false';

// Enrollment check karo
$is_enrolled = false;
if ($is_logged_in === 'true') {
    $user_id = $_SESSION['user_id'];
    $enrolled_check = mysqli_query($conn, "SELECT id FROM enrollments WHERE student_id = '$user_id' AND course_id = '$course_id'");
    $is_enrolled = mysqli_num_rows($enrolled_check) > 0;
}
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

<div class="container py-5">
    <div class="row g-4">
        <div class="col-lg-8" data-aos="fade-right">
            <h1 class="fw-bold mb-3"><?= $course['title'] ?></h1>
            <div class="d-flex align-items-center mb-5">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold me-2 shadow" style="width:45px; height:45px;">
                    <?= strtoupper(substr($course['instructor_name'], 0, 1)) ?>
                </div>
                <span>Instructor: <b class="text-dark"><?= $course['instructor_name'] ?></b></span>
            </div>

            <h4 class="fw-bold mb-4">Course Content</h4>
            <div class="accordion shadow-sm rounded-4 overflow-hidden" id="curriculum">
                <?php 
                $u_count = 1;
                while($unit = mysqli_fetch_assoc($units_res)): 
                    $u_id = $unit['id'];
                    $lessons_res = mysqli_query($conn, "SELECT * FROM lessons WHERE unit_id = '$u_id'");
                ?>
                <div class="accordion-item border-0 border-bottom">
                    <h2 class="accordion-header">
                        <button class="accordion-button <?= ($u_count > 1) ? 'collapsed' : '' ?> fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#u<?= $u_id ?>">
                            <?= $unit['unit_title'] ?>
                        </button>
                    </h2>
                    <div id="u<?= $u_id ?>" class="accordion-collapse collapse <?= ($u_count == 1) ? 'show' : '' ?>" data-bs-parent="#curriculum">
                        <div class="accordion-body p-0">
                            <ul class="list-group list-group-flush">
                                <?php while($lesson = mysqli_fetch_assoc($lessons_res)): 
                                    $link = $lesson['lesson_url'] ?? $lesson['video_link'] ?? ""; 
                                ?>
                                    <li class="list-group-item d-flex justify-content-between p-3 align-items-center">
                                        <div><i class="fas fa-play-circle me-2 text-muted"></i> <?= $lesson['lesson_title'] ?></div>
                                        
                                        <?php if($u_count == 1): ?>
                                            <a href="javascript:void(0)" class="btn btn-sm btn-outline-primary fw-bold px-3 rounded-pill" onclick="changeVideo('<?= $link ?>')">
                                                Preview
                                            </a>
                                        <?php else: ?>
                                            <i class="fas fa-lock text-muted small"></i>
                                        <?php endif; ?>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php $u_count++; endwhile; ?>
            </div>
        </div>

        <div class="col-lg-4" data-aos="fade-left">
            <div class="sticky-top" style="top: 100px;">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div id="sidebarMedia" class="bg-black position-relative" style="height: 230px;">
                        <img id="mainThumb" src="uploads/thumbnails/<?= $course['thumbnail'] ?>" class="w-100 h-100" style="object-fit: cover; transition: all 0.5s ease;">
                        <iframe id="videoPlayer" class="d-none w-100 h-100" src="" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
                    </div>

                    <div class="card-body p-4 text-center">
                        <h2 class="fw-bold text-primary mb-3">₹<?= number_format($course['price'], 0) ?></h2>
                        
                        <?php if ($is_enrolled): ?>
                        <button disabled class="btn btn-success w-100 py-3 rounded-3 fw-bold shadow-sm mb-3">
                            You already purchased it
                        </button>
                        <?php else: ?>
                        <button onclick="checkLoginBeforeEnroll()" class="btn btn-primary w-100 py-3 rounded-3 fw-bold shadow-sm mb-3">
                            Enroll Now
                        </button>
                        <?php endif; ?>
                        
                        <p class="small text-muted mb-0">Select "Preview" in curriculum to watch.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({ duration: 1000, once: true }); // Animation settings

    const isLoggedIn = <?= $is_logged_in ?>;
    const courseId = "<?= $course_id ?>";

    function checkLoginBeforeEnroll() {
        if (!isLoggedIn) {
            Swal.fire({
                title: 'Login Required!',
                text: "Please login to enroll in the course.",
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-sign-in-alt"></i> Login Now',
                cancelButtonText: 'Cancel',
                showClass: {
                    popup: 'animate__animated animate__fadeInDown'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutUp'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'login.php?redirect=course_details.php?id=' + courseId;
                }
            });
        } else {
            window.location.href = 'checkout.php?id=' + courseId;
        }
    }

    function changeVideo(url) {
        if(!url) {
            Swal.fire('Oops!', 'Preview link nathi mali.', 'error');
            return;
        }
        const thumb = document.getElementById('mainThumb');
        const player = document.getElementById('videoPlayer');

        thumb.style.opacity = '0';
        setTimeout(() => {
            thumb.classList.add('d-none');
            player.classList.remove('d-none');
            
            let finalUrl = url;
            if(url.includes('youtube.com') || url.includes('youtu.be')) {
                let videoId = url.split('v=')[1] || url.split('/').pop();
                if(videoId.includes('&')) videoId = videoId.split('&')[0];
                finalUrl = "https://www.youtube.com/embed/" + videoId + "?autoplay=1";
            }
            player.src = finalUrl;
        }, 500);
    }
</script>

<style>
    .accordion-button:not(.collapsed) { background: #f8f9ff; color: #0d6efd; border-radius: 10px; }
    .btn-primary { transition: transform 0.3s ease; }
    .btn-primary:hover { transform: translateY(-3px); }
</style>

<?php include 'includes/footer.php'; ?>