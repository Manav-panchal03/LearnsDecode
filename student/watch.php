<?php 
ob_start();
session_start();
require '../config/config.php'; 

if(!isset($_SESSION['user_id'])){ header("Location: ../login.php"); exit(); }

$user_id = $_SESSION['user_id'];
$course_id = isset($_GET['course_id']) ? mysqli_real_escape_string($conn, $_GET['course_id']) : 0;
$unit_id = isset($_GET['unit_id']) ? mysqli_real_escape_string($conn, $_GET['unit_id']) : 0;

if($unit_id == 0) { header("Location: dashboard.php"); exit(); }

// ERROR FIX: user_id ni jagyae student_id vaparyu che
$enroll_q = mysqli_query($conn, "SELECT id FROM enrollments WHERE student_id = '$user_id' AND course_id = '$course_id' LIMIT 1");
$enroll_data = mysqli_fetch_assoc($enroll_q);
$enrollment_id = $enroll_data['id'] ?? 0;

$unit_info_q = mysqli_query($conn, "SELECT unit_title FROM units WHERE id = '$unit_id'");
$unit_info = mysqli_fetch_assoc($unit_info_q);

// All Lessons for Logic
$lessons_q = mysqli_query($conn, "SELECT * FROM lessons WHERE unit_id = '$unit_id' ORDER BY id ASC");
$lessons_array = [];
while($row = mysqli_fetch_assoc($lessons_q)) {
    $l_id = $row['id'];
    $row['is_done'] = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM lesson_progress WHERE enrollment_id = '$enrollment_id' AND lesson_id = '$l_id' AND is_completed = 1")) > 0;
    $lessons_array[] = $row;
}

$default_lesson = $lessons_array[0] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $unit_info['unit_title'] ?> - LearnsDecode</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://www.youtube.com/iframe_api"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap');
        body { background: #f4f7fe; font-family: 'Plus Jakarta Sans', sans-serif; color: #2b3674; height: 100vh; overflow: hidden; }
        
        .main-container { display: flex; height: calc(100vh - 80px); gap: 20px; padding: 20px; }
        .video-section { flex: 2; display: flex; flex-direction: column; gap: 15px; }
        .player-wrapper { background: #fff; border-radius: 24px; padding: 10px; box-shadow: 0 10px 40px rgba(0,0,0,0.03); }
        .video-container { border-radius: 18px; overflow: hidden; aspect-ratio: 16/9; background: #000; }
        
        .curriculum-sidebar { flex: 0 0 380px; background: #fff; border-radius: 24px; display: flex; flex-direction: column; box-shadow: 0 10px 40px rgba(0,0,0,0.03); }
        .lesson-list { flex: 1; overflow-y: auto; padding: 15px; }
        
        .lesson-card { 
            display: flex; align-items: center; padding: 14px; margin-bottom: 10px;
            text-decoration: none !important; color: #707eae; border-radius: 16px;
            background: #f8fafc; transition: 0.4s; border: 1px solid transparent;
        }
        .lesson-card.active { background: #4318FF; color: #fff !important; }
        .lesson-card.locked { opacity: 0.6; cursor: not-allowed; background: #f1f5f9; }
        
        .btn-done { 
            background: #05cd99; color: #fff; border: none; border-radius: 12px; 
            padding: 12px 30px; font-weight: 700; transition: 0.3s;
        }
        .btn-done:disabled { background: #cbd5e1; cursor: not-allowed; }
        
        .top-nav { height: 80px; background: #fff; display: flex; align-items: center; padding: 0 30px; border-bottom: 1px solid #e9edf7; }
        .fade-in-up { animation: fadeInUp 0.6s ease forwards; }
        @keyframes fadeInUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    </style>
</head>
<body>

<nav class="top-nav">
    <div class="d-flex align-items-center gap-3">
        <a href="dashboard.php" class="btn btn-light rounded-circle shadow-sm"><i class="fas fa-arrow-left"></i></a>
        <h5 class="fw-bold mb-0"><?= $unit_info['unit_title'] ?></h5>
    </div>
</nav>

<div class="main-container">
    <div class="video-section">
        <div class="player-wrapper fade-in-up">
            <div class="video-container">
                <div id="player"></div>
            </div>
        </div>
        
        <div class="card border-0 p-4 shadow-sm fade-in-up">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 id="lessonTitle" class="fw-bold mb-1">Loading...</h4>
                    <p class="text-muted small mb-0" id="statusText">Video watch progress: 0%</p>
                </div>
                <button id="markDone" class="btn btn-done" disabled>
                    <i class="fas fa-lock me-2"></i> Watch Full Video
                </button>
            </div>
        </div>
    </div>

    <div class="curriculum-sidebar fade-in-up">
        <div class="p-4 border-bottom"><h6 class="fw-bold text-primary mb-0">COURSE CURRICULUM</h6></div>
        <div class="lesson-list" id="lessonMenu">
            <?php 
            $previous_done = true; 
            foreach($lessons_array as $index => $l):
                $is_locked = !$previous_done;
            ?>
            <a href="javascript:void(0)" 
               class="lesson-card <?= $is_locked ? 'locked' : 'lesson-trigger' ?>" 
               data-id="<?= $l['id'] ?>" 
               data-url="<?= $l['lesson_url'] ?>" 
               data-title="<?= $l['lesson_title'] ?>"
               onclick="<?= $is_locked ? "Swal.fire('Locked', 'Please complete previous lesson first!', 'warning')" : "" ?>">
                <div class="me-3"><i class="fas <?= $l['is_done'] ? 'fa-check-circle text-success' : ($is_locked ? 'fa-lock' : 'fa-play-circle') ?>"></i></div>
                <div class="flex-grow-1">
                    <div class="fw-600 small"><?= $l['lesson_title'] ?></div>
                </div>
            </a>
            <?php 
                $previous_done = $l['is_done']; 
            endforeach; 
            ?>
        </div>
    </div>
</div>

<script>
let player;
let videoTimer;
let currentLessonID = 0;

function onYouTubeIframeAPIReady() {
    <?php if($default_lesson): ?>
    loadVideo("<?= $default_lesson['id'] ?>", "<?= $default_lesson['lesson_url'] ?>", "<?= $default_lesson['lesson_title'] ?>");
    <?php endif; ?>
}

function extractID(url) {
    const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
    const match = url.match(regExp);
    return (match && match[2].length == 11) ? match[2] : null;
}

function loadVideo(id, url, title) {
    currentLessonID = id;
    const vidID = extractID(url);
    
    if(player && player.loadVideoById) {
        player.loadVideoById(vidID);
    } else {
        player = new YT.Player('player', {
            height: '100%', width: '100%', videoId: vidID,
            playerVars: { 'autoplay': 0, 'rel': 0, 'modestbranding': 1 },
            events: { 'onStateChange': onPlayerStateChange }
        });
    }

    $('#lessonTitle').text(title);
    $('#markDone').prop('disabled', true).html('<i class="fas fa-lock me-2"></i> Watch to Unlock');
    $('.lesson-card').removeClass('active');
    $(`.lesson-trigger[data-id="${id}"]`).addClass('active');
}

function onPlayerStateChange(event) {
    if (event.data == YT.PlayerState.PLAYING) {
        videoTimer = setInterval(checkProgress, 1000);
    } else {
        clearInterval(videoTimer);
    }
}

function checkProgress() {
    let duration = player.getDuration();
    let currentTime = player.getCurrentTime();
    let percent = (currentTime / duration) * 100;
    $('#statusText').text(`Video watch progress: ${Math.round(percent)}%`);

    if (percent > 90 || (duration - currentTime) < 10) {
        $('#markDone').prop('disabled', false).html('<i class="fas fa-check-circle me-2"></i> Mark as Completed');
        clearInterval(videoTimer);
    }
}

$(document).ready(function() {
    $('.lesson-trigger').click(function() {
        loadVideo($(this).data('id'), $(this).data('url'), $(this).data('title'));
    });

    $('#markDone').click(function() {
    const lessonID = currentLessonID;
    const enrollID = <?= $enrollment_id ?>;

    $.post('mark_lesson_done.php', { 
        lesson_id: lessonID, 
        enrollment_id: enrollID 
    }, function(response) {
        // Success alert batavi ne page reload karo
        Swal.fire({ 
            icon: 'success', 
            title: 'Lesson Completed!', 
            showConfirmButton: false, 
            timer: 1500 
        }).then(() => {
            location.reload(); // Reload thase etle biju video unlock thai jase
        });
    });
});
});
</script>
</body>
</html>