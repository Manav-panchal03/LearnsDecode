<?php
session_start();
require '../config/config.php';

// Security Check
if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'instructor'){
    header("Location: ../login.php");
    exit();
}

$instructor_id = $_SESSION['user_id'];

/// Fetch all courses with unit count and lesson count
$sql = "SELECT c.*, 
        (SELECT COUNT(*) FROM units WHERE course_id = c.id) as total_sections,
        (SELECT COUNT(l.id) FROM lessons l 
        JOIN units u ON l.unit_id = u.id 
        WHERE u.course_id = c.id) as total_lessons
        FROM courses c 
        WHERE c.instructor_id = $instructor_id 
        ORDER BY c.id DESC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query Failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Courses | LearnsDecode</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        :root { --primary: #6c63ff; --dark: #1e1e2d; }
        body { 
        background-color: #f4f7f6; 
        font-family: 'Poppins', sans-serif !important; /* Force Poppins everywhere */
        color: #2d3748;
    }

    /* Heading mismatch fix */
    h2, h4, h5, h6, .fw-bold {
        font-family: 'Poppins', sans-serif !important;
        font-weight: 700; /* Dashboard જેવી જાડાઈ આપવા માટે */
        letter-spacing: -0.2px;
    }

    /* Text color consistent with Pic 1 */
    .text-muted {
        color: #a2a3b7 !important;
        font-size: 0.85rem;
    }

    /* Card title font size fix */
    .course-row h6 {
        font-size: 1.1rem;
        margin-bottom: 4px;
        color: #1e1e2d;
    }
        
        /* Glassmorphism Search Bar */
        .search-box {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            border: 1px solid rgba(255,255,255,0.3);
        }

        /* Animated Course Card */
        .course-row {
            transition: all 0.3s ease;
            border-radius: 15px;
            background: white;
            margin-bottom: 15px;
            border: 1px solid transparent;
        }
        /* Card Hover Glow Effect */
.course-row:hover {
    transform: translateX(10px) translateY(-2px);
    box-shadow: 0 15px 30px rgba(108, 99, 255, 0.15);
    border-left: 5px solid var(--primary);
    background: linear-gradient(to right, #ffffff, #f8f9ff);
}

/* Staggered Animation for List Items */
.course-list .course-row {
    animation: fadeInUp 0.5s ease backwards;
}

/* Delay for each item to create a 'wave' effect */
<?php for($i=1; $i<=10; $i++): ?>
.course-list .course-row:nth-child(<?= $i ?>) { animation-delay: <?= $i * 0.1 ?>s; }
<?php endfor; ?>

        .course-img {
            width: 100px; height: 60px;
            object-fit: cover; border-radius: 10px;
        }

        .action-btn {
            width: 40px; height: 40px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center; justify-content: center;
            transition: 0.2s;
            text-decoration: none;
        }
        .btn-edit { background: #eef2ff; color: var(--primary); }
        .btn-edit:hover { background: var(--primary); color: white; }
        
        .btn-del { background: #fff1f1; color: #ff4757; }
        .btn-del:hover { background: #ff4757; color: white; }

        /* Status Badge Animation */
        .status-dot {
            height: 10px; width: 10px;
            border-radius: 50%; display: inline-block;
            margin-right: 5px;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(108, 99, 255, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(108, 99, 255, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(108, 99, 255, 0); }
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-5 animate__animated animate__fadeInDown">
        <div class="d-flex align-items-center gap-3 mb-2">
    <a href="dashboard.php" class="btn btn-light rounded-circle shadow-sm d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; transition: 0.3s;">
        <i class="fas fa-arrow-left text-primary"></i>
    </a>

    <div>
        <h2 class="fw-bold m-0">Manage Your <span class="text-primary">Courses</span></h2>
        <p class="text-muted m-0">Edit, update, and track your content</p>
    </div>
</div>
        
        <a href="add_course.php" class="btn btn-primary px-4 rounded-pill shadow">
            <i class="fas fa-plus me-2"></i>New Course
        </a>
    </div>

    <div class="search-box mb-4 animate__animated animate__fadeInUp">
        <div class="row g-3">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="searchInput" class="form-control border-0 bg-transparent" placeholder="Search your courses...">
                </div>
            </div>
            <div class="col-md-4 text-end">
                <select id="statusFilter" class="form-select border-0 bg-light rounded-pill">
                    <option value="All">All Categories</option>
                    <option value="Published">Published</option>
                    <option value="Draft">Draft</option>
                </select>
            </div>
        </div>
    </div>

    <div id="courseContainer" class="course-list animate__animated animate__fadeInUp animate__delay-1s">
        <?php if(mysqli_num_rows($result) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
                <div class="course-row p-3 shadow-sm d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <img src="../uploads/thumbnails/<?= $row['thumbnail'] ?: 'course-default.jpg' ?>" class="course-img shadow-sm">
                        <div>
                            <h6 class="fw-bold mb-1"><?= $row['title'] ?></h6>
                            <div class="d-flex gap-3 small text-muted">
                                <span><i class="fas fa-layer-group me-1"></i><?= $row['total_sections'] ?> Sections</span>
                                <span><i class="fas fa-play-circle me-1"></i><?= $row['total_lessons'] ?> Lessons</span>
                                <span><i class="fas fa-tag me-1"></i>₹<?= $row['price'] ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-4">
                        <div class="text-end d-none d-md-block">
                            <?php if($row['status'] == 'published' || $row['status'] == 'active'): ?>
                                <span class="badge bg-light text-success rounded-pill px-3">
                                    <span class="status-dot bg-success"></span> Published
                                </span>
                            <?php else: ?>
                                <span class="badge bg-light text-warning rounded-pill px-3">
                                    <span class="status-dot bg-warning"></span> Draft
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="actions">
                            <a href="view_course.php?id=<?= $row['id'] ?>" class="action-btn btn-edit me-1" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="add_course.php?edit_id=<?= $row['id'] ?>" class="action-btn btn-edit me-1" title="Edit">
                                <i class="fas fa-pen"></i>
                            </a>
                            <a href="delete_course.php?id=<?= $row['id'] ?>" 
                                class="action-btn btn-del" 
                                onclick="return confirm('Are you sure you want to delete this course? All related units and lessons will be lost!')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="text-center py-5">
                <img src="https://illustrations.popsy.co/purple/searching.svg" width="200" class="mb-3">
                <h5>No courses found. Start creating!</h5>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function(){
        // Function to fetch data
        function loadCourses(){
            let search = $('#searchInput').val();
            let status = $('#statusFilter').val();

            $.ajax({
                url: 'MC_fetch_courses_ajax.php',
                method: 'POST',
                data: {search: search, status: status},
                success: function(response){
                    $('#courseContainer').html(response);
                }
            });
        }

        // Real-time Search: જેમ જેમ ટાઈપ કરશો તેમ
        $('#searchInput').on('keyup', function(){
            loadCourses();
        });

        // Filter Change: ડ્રોપડાઉન બદલાય ત્યારે
        $('#statusFilter').on('change', function(){
            loadCourses();
        });

        // Initial load
        loadCourses();
    });
</script>
</body>
</html>