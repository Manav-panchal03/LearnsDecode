<?php
session_start();
require '../config/config.php';

$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Course details fetch karo
$course_q = mysqli_query($conn, "SELECT c.*, u.name as instructor_name FROM courses c 
                                JOIN users u ON c.instructor_id = u.id 
                                WHERE c.id = $course_id");
$course = mysqli_fetch_assoc($course_q);

if (!$course) { echo "Course not found!"; exit; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $course['title'] ?> | Preview</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; }
        
        /* Landing Page Styles */
        .course-header { background: #1e1e2d; color: white; padding: 60px 0; }
        .unit-card { border: none; margin-bottom: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .landing-lesson-item { padding: 12px 15px; border-bottom: 1px solid #eee; display: flex; align-items: center; cursor: pointer; transition: 0.3s; }
        .landing-lesson-item:hover { background: #f1f1f1; }

        /* Preview Mode (Learning) Styles - Initially Hidden */
        #learning-mode { display: none; padding-top: 20px; }
        .video-container { background: #000; border-radius: 12px; overflow: hidden; position: relative; padding-top: 56.25%; }
        .video-container iframe, .video-container video { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }
        .curriculum-scroll { max-height: 70vh; overflow-y: auto; }
        .lesson-item { cursor: pointer; transition: 0.2s; border-radius: 8px; margin-bottom: 5px; }
        .lesson-item.active { background-color: #e7f1ff !important; border-left: 4px solid #0d6efd; }
        
        /* Mode Toggle Button Styling */
        .btn-preview-toggle { position: fixed; bottom: 20px; right: 20px; z-index: 1000; border-radius: 50px; padding: 12px 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-dark sticky-top">
    <div class="container">
        <a class="navbar-brand" href="manage_courses.php"><i class="fas fa-arrow-left me-2"></i> Dashboard</a>
        <button class="btn btn-outline-info btn-sm" onclick="toggleMode()" id="mode-btn">
            <i class="fas fa-play me-1"></i> Start Preview Mode
        </button>
    </div>
</nav>

<div id="landing-page">
    <header class="course-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <span class="badge bg-primary mb-2">Instructor View</span>
                    <h1 class="fw-bold"><?= $course['title'] ?></h1>
                    <p class="lead text-white-50"><?= substr(strip_tags($course['description']), 0, 150) ?></p>
                    <div class="d-flex align-items-center mt-4">
                        <img src="https://ui-avatars.com/api/?name=<?= $course['instructor_name'] ?>&background=random" class="rounded-circle me-2" width="40">
                        <span>Created by <strong><?= $course['instructor_name'] ?></strong></span>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <div class="card shadow border-0 overflow-hidden">
                        <img src="../uploads/thumbnails/<?= $course['thumbnail'] ?: 'course-default.jpg' ?>" class="card-img-top">
                        <div class="card-body bg-white text-dark">
                            <h2 class="fw-bold text-primary">₹<?= $course['price'] ?></h2>
                            <button class="btn btn-primary w-100 btn-lg" onclick="toggleMode()">Preview Lessons</button>
                        </div>
                    </div>
                </div>
                <div class="card border-0 shadow-sm p-4 mt-4" style="border-radius: 15px;">
                    <h5 class="fw-bold mb-3" style="font-size: 1.1rem;">This course includes:</h5>
                    
                    <?php
                    // dynamically count lessons and files
                    $counts_q = mysqli_query($conn, "SELECT 
                        SUM(CASE WHEN content_type = 'video' THEN 1 ELSE 0 END) as video_count,
                        SUM(CASE WHEN content_type = 'file' THEN 1 ELSE 0 END) as file_count
                        FROM lessons WHERE unit_id IN (SELECT id FROM units WHERE course_id = $course_id)");
                    $counts = mysqli_fetch_assoc($counts_q);
                    ?>

                    <ul class="list-unstyled mb-0" style="font-size: 0.9rem;">
                        <li class="mb-3 d-flex align-items-center">
                            <div class="icon-box me-3 text-primary bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; min-width: 30px;">
                                <i class="fas fa-video fa-sm"></i>
                            </div>
                            <span><?= ($counts['video_count'] > 0) ? $counts['video_count'] : '0' ?> On-demand video</span>
                        </li>
                        
                        <li class="mb-3 d-flex align-items-center">
                            <div class="icon-box me-3 text-success bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; min-width: 30px;">
                                <i class="fas fa-file-download fa-sm"></i>
                            </div>
                            <span><?= ($counts['file_count'] > 0) ? $counts['file_count'] : '0' ?> Downloadable resources</span>
                        </li>
                        
                        <li class="mb-3 d-flex align-items-center">
                            <div class="icon-box me-3 text-warning bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; min-width: 30px;">
                                <i class="fas fa-infinity fa-sm"></i>
                            </div>
                            <span>Full lifetime access</span>
                        </li>
                        
                        <li class="mb-3 d-flex align-items-center">
                            <div class="icon-box me-3 text-info bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; min-width: 30px;">
                                <i class="fas fa-mobile-alt fa-sm"></i>
                            </div>
                            <span>Access on mobile and TV</span>
                        </li>

                        <li class="mb-0 d-flex align-items-center">
                            <div class="icon-box me-3 text-danger bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; min-width: 30px;">
                                <i class="fas fa-certificate fa-sm"></i>
                            </div>
                            <span>Certificate of completion</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <div class="container my-5">
        <div class="row">
            <div class="col-md-8">
                <h3 class="fw-bold mb-4">Course Content</h3>
                <div class="accordion" id="curriculumAccordion">
                    <?php
                    $units_q = mysqli_query($conn, "SELECT * FROM units WHERE course_id = $course_id ORDER BY id ASC");
                    $index = 0;
                    while($unit = mysqli_fetch_assoc($units_q)):
                        $index++; $u_id = $unit['id'];
                    ?>
                    <div class="accordion-item unit-card">
                        <h2 class="accordion-header">
                            <button class="accordion-button <?= ($index > 1) ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#unit-<?= $u_id ?>">
                                <strong><?= htmlspecialchars($unit['unit_title']) ?></strong>
                            </button>
                        </h2>
                        <div id="unit-<?= $u_id ?>" class="accordion-collapse collapse <?= ($index == 1) ? 'show' : '' ?>" data-bs-parent="#curriculumAccordion">
                            <div class="accordion-body p-0">
                                <?php
                                $lessons_q = mysqli_query($conn, "SELECT * FROM lessons WHERE unit_id = $u_id ORDER BY id ASC");
                                while($lesson = mysqli_fetch_assoc($lessons_q)):
                                ?>
                                <div class="landing-lesson-item" onclick="switchToLesson('<?= $lesson['lesson_url'] ?>', '<?= $lesson['content_type'] ?>', '<?= addslashes($lesson['lesson_title']) ?>', 'lesson-idx-<?= $lesson['id'] ?>')">
                                    <i class="fas fa-play-circle text-muted me-3"></i>
                                    <span><?= htmlspecialchars($lesson['lesson_title']) ?></span>
                                    <span class="ms-auto text-muted small"><i class="fas fa-lock-open me-1"></i> Preview</span>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="learning-mode">
    <div class="container-fluid px-4">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="bg-white p-3 rounded-3 shadow-sm mb-4">
                    <div id="media-display" class="video-container mb-3">
                        <div class="position-absolute top-50 start-50 translate-middle text-center text-white">
                            <i class="fas fa-play-circle fa-4x mb-2"></i>
                            <p>Select a lesson to preview content</p>
                        </div>
                    </div>
                    <h4 id="active-lesson-title" class="fw-bold"><?= $course['title'] ?></h4>
                    <hr>
                    <p class="text-muted"><?= nl2br($course['description']) ?></p>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">Lessons</h5>
                        <button class="btn btn-sm btn-light" onclick="toggleMode()">Exit Preview</button>
                    </div>
                    <div class="card-body p-2 curriculum-scroll">
                        <div class="accordion accordion-flush" id="previewAccordion">
                            <?php
                            mysqli_data_seek($units_q, 0); // Restart units loop
                            while($unit = mysqli_fetch_assoc($units_q)):
                                $u_id = $unit['id'];
                            ?>
                            <div class="accordion-item border-0">
                                <h2 class="accordion-header">
                                    <button class="accordion-button bg-light py-2 small fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#preview-unit-<?= $u_id ?>">
                                        <?= htmlspecialchars($unit['unit_title']) ?>
                                    </button>
                                </h2>
                                <div id="preview-unit-<?= $u_id ?>" class="accordion-collapse collapse show">
                                    <div class="accordion-body p-1">
                                        <?php
                                        $lessons_q = mysqli_query($conn, "SELECT * FROM lessons WHERE unit_id = $u_id ORDER BY id ASC");
                                        while($lesson = mysqli_fetch_assoc($lessons_q)):
                                            $l_title = addslashes($lesson['lesson_title']);
                                            $l_url = $lesson['lesson_url'];
                                            $l_type = $lesson['content_type'];
                                        ?>
                                        <div class="lesson-item p-3 d-flex align-items-center bg-white border-bottom lesson-idx-<?= $lesson['id'] ?>" 
                                            onclick="playLesson('<?= $l_url ?>', '<?= $l_type ?>', '<?= $l_title ?>', this)">
                                            <i class="fas <?= ($l_type == 'video') ? 'fa-play-circle text-primary' : 'fa-file-pdf text-danger' ?> me-3"></i>
                                            <span class="small fw-medium"><?= htmlspecialchars($lesson['lesson_title']) ?></span>
                                        </div>
                                        <?php endwhile; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Toggle between Landing Page and Preview Mode
function toggleMode() {
    const landing = document.getElementById('landing-page');
    const learning = document.getElementById('learning-mode');
    const btn = document.getElementById('mode-btn');

    if (landing.style.display !== 'none') {
        landing.style.display = 'none';
        learning.style.display = 'block';
        btn.innerHTML = '<i class="fas fa-times me-1"></i> Exit Preview';
        btn.className = 'btn btn-outline-danger btn-sm';
    } else {
        landing.style.display = 'block';
        learning.style.display = 'none';
        btn.innerHTML = '<i class="fas fa-play me-1"></i> Start Preview Mode';
        btn.className = 'btn btn-outline-info btn-sm';
    }
}

// Function to play lesson from any mode
function playLesson(url, type, title, element) {
    const display = document.getElementById('media-display');
    const titleHeader = document.getElementById('active-lesson-title');
    
    document.querySelectorAll('.lesson-item').forEach(el => el.classList.remove('active'));
    if(element) element.classList.add('active');
    
    titleHeader.innerText = title;

    if (type === 'video') {
        let videoId = extractYouTubeId(url);
        if (videoId) {
            display.innerHTML = `<iframe src="https://www.youtube.com/embed/${videoId}?autoplay=1" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>`;
        } else {
            display.innerHTML = `<video controls autoplay class="w-100 h-100"><source src="../${url}" type="video/mp4"></video>`;
        }
    } else {
        let fullPath = url.startsWith('http') ? url : '../' + url;
        display.innerHTML = `<iframe src="${fullPath}" width="100%" height="600px"></iframe>`;
    }
}

// Switch from Landing to a specific lesson in Learning Mode
function switchToLesson(url, type, title, className) {
    toggleMode(); // Switch to learning mode
    setTimeout(() => {
        const element = document.querySelector('.' + className);
        playLesson(url, type, title, element);
    }, 100);
}

function extractYouTubeId(url) {
    const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
    const match = url.match(regExp);
    return (match && match[2].length === 11) ? match[2] : null;
}
</script>

</body>
</html>