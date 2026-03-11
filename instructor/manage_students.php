<?php
session_start();
require '../config/config.php';
require_once '../includes/session_utils.php';

activateRole('instructor');
$instructor_id = $_SESSION['user_id'];

// Profile fetch for Top Nav
$profile_q = mysqli_query($conn ,"SELECT u.name, p.profile_pic FROM users u LEFT JOIN instructor_profiles p ON u.id = p.user_id WHERE u.id = $instructor_id");
$profile_data = mysqli_fetch_assoc($profile_q);
$p_img = (!empty($profile_data['profile_pic']) && $profile_data['profile_pic'] != 'default-avatar.png') 
        ? "../uploads/profile/".$profile_data['profile_pic'] 
        : "https://ui-avatars.com/api/?name=".urlencode($profile_data['name'])."&background=6c63ff&color=fff";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Enrolled Students | LearnsDecode</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        /* PIC 1 MUJAB EXACT STYLE */
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

        /* LIST CARD STYLE */
        .student-card { background: white; border-radius: 20px; padding: 30px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .badge-course { background: #f8f9fa; color: #333; border: 1px solid #eee; font-weight: 500; }
    </style>
</head>
<body>

<nav id="sidebar">
    <div class="sidebar-header">
        <h3 class="fw-bold text-white mb-0">Learns<span style="color:var(--primary-color)">Decode</span></h3>
        <small>Instructor Workspace</small>
    </div>
    <div class="nav-links">
        <a href="dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
        <a href="add_course.php"><i class="fas fa-plus-square"></i> Create Course</a>
        <a href="manage_courses.php"><i class="fas fa-book-open"></i> My Courses</a>
        <a href="add_quiz.php"><i class="fas fa-question-circle"></i> Create Quizzes</a>
        <a href="manage_quizzes.php"><i class="fas fa-tasks"></i> Manage Quizzes</a>
        <a href="broadcast_center.php"><i class="fas fa-envelope"></i> Broadcast Center</a>
        <a href="manage_students.php"><i class="fas fa-users"></i> Students</a>
        <a href="manage_certificates.php"><i class="fas fa-certificate"></i> Certificates</a>
        
        <hr style="border-color: rgba(255,255,255,0.1)">
        <a href="../logout.php" class="text-danger"><i class="fas fa-power-off"></i> Logout</a>
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
            <img src="<?= $p_img ?>" class="rounded-circle shadow-sm border" width="45" height="45" style="object-fit: cover;">
        </div>
    </nav>

    <div class="container-fluid p-4">
        <h2 class="fw-bold mb-4" data-aos="fade-down">Enrolled Students 🎓</h2>
        
        <div class="student-card" data-aos="fade-up">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0">Student</th>
                            <th class="border-0">Enrolled Course</th>
                            <th class="border-0">Enrollment Date</th>
                            <th class="border-0">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $enroll_sql = "SELECT u.name, u.email, c.title as course_title, e.enrolled_at 
                                      FROM enrollments e
                                      JOIN users u ON e.student_id = u.id
                                      JOIN courses c ON e.course_id = c.id
                                      WHERE c.instructor_id = $instructor_id
                                      ORDER BY e.enrolled_at DESC";
                        
                        $enroll_res = mysqli_query($conn, $enroll_sql);
                        if(mysqli_num_rows($enroll_res) > 0):
                            while($row = mysqli_fetch_assoc($enroll_res)):
                        ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle me-3 d-flex align-items-center justify-content-center bg-light rounded-circle" style="width: 45px; height: 45px; font-weight: bold; color: var(--primary-color); border: 1px solid #eee;">
                                        <?= strtoupper(substr($row['name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold"><?= $row['name'] ?></div>
                                        <small class="text-muted"><?= $row['email'] ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge badge-course px-3 py-2"><?= $row['course_title'] ?></span></td>
                            <td><span class="text-muted"><?= date('d M, Y', strtotime($row['enrolled_at'])) ?></span></td>
                            <td><span class="badge bg-success-subtle text-success border border-success-subtle px-3">Active</span></td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="4" class="text-center py-5 text-muted">No students enrolled yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({ duration: 800, once: true });
    $('#sidebarCollapse').on('click', function () {
        $('#sidebar').toggleClass('active');
        $('#content').toggleClass('active');
    });
</script>
</body>
</html>