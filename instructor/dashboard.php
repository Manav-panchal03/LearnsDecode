<?php
session_start();
require '../config/config.php';
require_once '../includes/session_utils.php';

// activate instructor role (keeps other roles intact)
activateRole('instructor');

// Security Check
if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'instructor'){
    header("Location: ../login.php");
    exit();
}

$instructor_id = $_SESSION['user_id'];

// --- DYNAMIC STATS FETCH ---
$total_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM courses WHERE instructor_id = $instructor_id");
$total_data = mysqli_fetch_assoc($total_q);

$pub_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM courses WHERE instructor_id = $instructor_id AND (status = 'published' OR status = 'active')");
$pub_data = mysqli_fetch_assoc($pub_q);

// --- FETCH ALL COURSES ---
$courses_query = mysqli_query($conn, "SELECT * FROM courses WHERE instructor_id = $instructor_id ORDER BY id DESC");

// Instructor Profile fetch 
$profile_q = mysqli_query($conn ,"SELECT u.name, u.email, p.* FROM users u 
                                LEFT JOIN instructor_profiles p ON u.id = p.user_id 
                                WHERE u.id = $instructor_id");
$profile_data = mysqli_fetch_assoc($profile_q);

$is_profile_complete = ($profile_data && !empty($profile_data['expertise']) && !empty($profile_data['bio']));

$p_img = ($profile_data && !empty($profile_data['profile_pic']) && $profile_data['profile_pic'] != 'default-avatar.png') 
        ? "../uploads/profile/".$profile_data['profile_pic'] 
        : "https://ui-avatars.com/api/?name=".urlencode($profile_data['name'])."&background=6c63ff&color=fff";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Instructor Panel | LearnsDecode</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        :root {
            --sidebar-width: 280px;
            --primary-color: #6c63ff;
            --dark-bg: #1e1e2d;
        }

        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; overflow-x: hidden; }

        /* --- SIDEBAR & CONTENT (Your existing CSS - Unchanged) --- */
        #sidebar { width: var(--sidebar-width); height: 100vh; position: fixed; left: 0; top: 0; background: var(--dark-bg); color: #a2a3b7; transition: all 0.3s ease-in-out; z-index: 1000; box-shadow: 4px 0 10px rgba(0,0,0,0.1); }
        #sidebar.active { left: calc(-1 * var(--sidebar-width)); }
        .sidebar-header { padding: 30px 20px; text-align: center; background: rgba(0,0,0,0.2); }
        .nav-links { padding: 20px 0; }
        .nav-links a { padding: 12px 25px; display: flex; align-items: center; color: #a2a3b7; text-decoration: none; transition: 0.2s; border-left: 4px solid transparent; }
        .nav-links a i { width: 30px; font-size: 1.1rem; }
        .nav-links a:hover, .nav-links a.active { background: #2b2b40; color: #ffffff; border-left: 4px solid var(--primary-color); }
        #content { width: calc(100% - var(--sidebar-width)); margin-left: var(--sidebar-width); transition: all 0.3s ease-in-out; min-height: 100vh; }
        #content.active { width: 100%; margin-left: 0; }
        .navbar-custom { background: #ffffff; padding: 15px 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        #sidebarCollapse { background: var(--primary-color); border: none; color: white; padding: 5px 12px; border-radius: 5px; }

        /* --- NEW COURSE CARD UI (Your existing CSS - Unchanged) --- */
        .stat-card { border: none; border-radius: 15px; padding: 25px; background: #fff; box-shadow: 0 5px 15px rgba(0,0,0,0.05); border-bottom: 4px solid var(--primary-color); }
        .course-card { background: white; border-radius: 20px; overflow: hidden; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.05); transition: 0.3s; }
        .course-card:hover { transform: translateY(-5px); box-shadow: 0 12px 20px rgba(0,0,0,0.1); }
        .thumb-container { height: 150px; position: relative; }
        .thumb-container img { width: 100%; height: 100%; object-fit: cover; }
        .status-badge { position: absolute; top: 10px; right: 10px; font-size: 0.7rem; padding: 5px 12px; border-radius: 50px; font-weight: 700; }
        .bg-draft { background: #fff4e5; color: #ff9800; }
        .bg-published { background: #e6fffa; color: #38b2ac; }
        .profile-container { position: relative; display: inline-block; cursor: pointer; transition: 0.3s; }
        .profile-container:hover { transform: scale(1.05); }
        .status-badge-dot { 
            position: absolute; bottom: 2px; right: 2px; 
            width: 18px; height: 18px; border-radius: 50%; 
            border: 2px solid white; display: flex; 
            align-items: center; justify-content: center; font-size: 9px; color: white;
        }
        .bg-incomplete { background: #ff4757; box-shadow: 0 0 5px rgba(255, 71, 87, 0.5); }
        .bg-complete { background: #2ed573; box-shadow: 0 0 5px rgba(46, 213, 115, 0.5); }
    </style>
</head>
<body>

<nav id="sidebar">
    <div class="sidebar-header">
        <h3 class="fw-bold text-white mb-0">Learns<span style="color:var(--primary-color)">Decode</span></h3>
        <small>Instructor Workspace</small>
    </div>
    <div class="nav-links">
        <a href="dashboard.php" class="active"><i class="fas fa-th-large"></i> Dashboard</a>
        <a href="add_course.php"><i class="fas fa-plus-square"></i> Create Course</a>
        <a href="manage_courses.php"><i class="fas fa-book-open"></i> My Courses</a>
        <a href="add_quiz.php"><i class="fas fa-question-circle"></i>Create Quizzes</a>
        <a href="manage_quizzes.php"><i class="fas fa-tasks"></i> Manage Quizzes</a>
        <a href="broadcast_center.php"><i class="fas fa-envelope"></i> Broadcast Center </a>
        <a href="manage_students.php"><i class="fas fa-users"></i> Students</a>
        <a href="manage_certificates.php"><i class="fas fa-certificate"></i> Certificates</a>
        <a href="earnings.php"><i class="fas fa-wallet"></i> Earnings</a>
        <a href="analytics.php"><i class="fas fa-chart-line"></i> Analytics</a>
        <hr style="border-color: rgba(255,255,255,0.1)">
        <a href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/logout.php" class="text-danger"><i class="fas fa-power-off"></i> Logout</a>
    </div>
</nav>

<div id="content">
    <nav class="navbar navbar-custom d-flex justify-content-between">
        <button type="button" id="sidebarCollapse">
            <i class="fas fa-align-left"></i>
        </button>
        <div class="user-info d-flex align-items-center">
            <div class="text-end me-3 d-none d-sm-block">
                <div class="fw-bold lh-1"><?= $profile_data['name']; ?></div>
                <small class="text-muted" style="font-size: 11px;">Instructor</small>
            </div>
            <div class="profile-container" data-bs-toggle="modal" data-bs-target="#profileModal">
                <img src="<?= $p_img ?>" class="rounded-circle shadow-sm border" width="45" height="45" style="object-fit: cover;">
                <?php if($is_profile_complete): ?>
                    <div class="status-badge-dot bg-complete"><i class="fas fa-check"></i></div>
                <?php else: ?>
                    <div class="status-badge-dot bg-incomplete"><i class="fas fa-exclamation"></i></div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container-fluid p-4">
        <div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-down">
            <h2 class="fw-bold">Instructor Dashboard</h2>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-3" data-aos="zoom-in" data-aos-delay="100">
                <div class="stat-card">
                    <h6 class="text-muted small text-uppercase">Total Courses</h6>
                    <h2 class="fw-bold mb-0"><?= sprintf("%02d", $total_data['total']); ?></h2>
                </div>
            </div>
            <div class="col-md-3" data-aos="zoom-in" data-aos-delay="200">
                <div class="stat-card" style="border-color: #38b2ac;">
                    <h6 class="text-muted small text-uppercase">Published</h6>
                    <h2 class="fw-bold mb-0"><?= sprintf("%02d", $pub_data['total']); ?></h2>
                </div>
            </div>
        </div>

        <h4 class="fw-bold mb-4" data-aos="fade-right">Your Content</h4>
        <div class="row g-4">
            <?php if(mysqli_num_rows($courses_query) > 0): ?>
                <?php while($course = mysqli_fetch_assoc($courses_query)): ?>
                <div class="col-md-4" data-aos="fade-up">
                    <div class="course-card">
                        <div class="thumb-container">
                            <?php 
                                $imgName = (!empty($course['thumbnail'])) ? $course['thumbnail'] : 'course-default.jpg';
                                $imgPath = "../uploads/thumbnails/" . $imgName;
                                $status = $course['status'];
                                $badgeClass = ($status == 'published' || $status == 'active') ? 'bg-published' : 'bg-draft';
                                $statusText = ($status == 'published' || $status == 'active') ? 'Published' : 'Draft';
                            ?>
                            <img src="<?= $imgPath ?>" class="img-fluid" alt="Course Thumbnail" onerror="this.src='../uploads/thumbnails/course-default.jpg'">
                            <span class="status-badge <?= $badgeClass ?>"><?= strtoupper($statusText) ?></span>
                        </div>
                        <div class="p-3">
                            <h6 class="fw-bold text-truncate mb-1"><?= $course['title'] ?></h6>
                            <div class="d-flex justify-content-between align-items-center">
                                <p class="text-muted small mb-0">Price: ₹<?= number_format($course['price'], 2) ?></p>
                                <a href="manage_courses.php" class="text-primary small text-decoration-none">Manage <i class="fas fa-arrow-right ms-1" style="font-size: 10px;"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5 bg-white rounded-4 shadow-sm" data-aos="zoom-in">
                    <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                    <h5>No courses found.</h5>
                    <p class="text-muted">Start by creating your first course today!</p>
                    <a href="add_course.php" class="btn btn-primary rounded-pill">Create Course</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="profileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold">Instructor Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="update_profile_logic.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4 text-center">
                    <div class="position-relative d-inline-block mb-3">
                        <img src="<?= $p_img ?>" id="previewImg" class="rounded-circle border" width="100" height="100" style="object-fit:cover;">
                        <label for="pic-upload" class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:30px; height:30px; cursor:pointer;">
                            <i class="fas fa-camera fa-xs"></i>
                        </label>
                        <input type="file" id="pic-upload" name="profile_pic" class="d-none" onchange="previewFile(this)">
                    </div>
                    
                    <div class="text-start">
                        <div class="mb-3">
                            <label class="small fw-bold text-muted">Full Name</label>
                            <input type="text" class="form-control bg-light border-0" value="<?= $profile_data['name'] ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold">Expertise (e.g. Full Stack Developer)</label>
                            <input type="text" name="expertise" class="form-control" value="<?= $profile_data['expertise'] ?? '' ?>" placeholder="Your professional title" required>
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold">About / Bio</label>
                            <textarea name="bio" class="form-control" rows="4" placeholder="Briefly describe your experience..." required><?= $profile_data['bio'] ?? '' ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="submit" name="update_profile" class="btn btn-primary w-100 rounded-pill py-2 fw-bold">Update Profile</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<script>
    AOS.init({ duration: 800, once: true });

    function previewFile(input){
        var file = input.files[0];
        if(file){
            var reader = new FileReader();
            reader.onload = function(){ document.getElementById("previewImg").src = reader.result; }
            reader.readAsDataURL(file);
        }
    }

    $(document).ready(function () {
        $('#sidebarCollapse').on('click', function () {
            $('#sidebar').toggleClass('active');
            $('#content').toggleClass('active');
        });
    });
</script>

<?php if(isset($_GET['status']) && $_GET['status'] == 'profile_updated'): ?>
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="liveToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-check-circle me-2"></i> Profile updated successfully!
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>
<script>
    $(document).ready(function(){
        var toastEl = document.getElementById('liveToast');
        var toast = new bootstrap.Toast(toastEl);
        toast.show();
    });
</script>
<?php endif; ?>

</body>
</html>