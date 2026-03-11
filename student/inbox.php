<?php 
session_start();
require '../config/config.php'; 

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Jyare student aa page par ave, tyare badha messages ne 'Read' mark kari nakhiye
mysqli_query($conn, "UPDATE inbox_messages b 
             JOIN enrollments e ON b.course_id = e.course_id 
             SET b.is_read = 1 
             WHERE e.student_id = '$user_id'");

// User profile fetch
$user_q = mysqli_query($conn, "SELECT name FROM users WHERE id = '$user_id'");
$user = mysqli_fetch_assoc($user_q);

// Notification Count
$unread_q = mysqli_query($conn, "
    SELECT COUNT(*) as unread_count 
    FROM inbox_messages b
    JOIN enrollments e ON b.course_id = e.course_id
    WHERE e.student_id = '$user_id' AND b.is_read = 0
");
$unread_data = mysqli_fetch_assoc($unread_q);
$unread_count = $unread_data['unread_count'] ?? 0;

// Fetch broadcasts
$broadcasts_q = mysqli_query($conn, "
    SELECT b.*, c.title as course_title, u.name as instructor_name 
    FROM inbox_messages b
    JOIN courses c ON b.course_id = c.id
    JOIN enrollments e ON c.id = e.course_id
    JOIN users u ON b.sender_id = u.id
    WHERE e.student_id = '$user_id'
    ORDER BY b.created_at DESC
");

// Count types for empty states
$counts = ['msg' => 0, 'material' => 0, 'quiz' => 0, 'all' => 0];
$messages = [];
while($row = mysqli_fetch_assoc($broadcasts_q)) {
    $messages[] = $row;
    $counts[$row['msg_type']]++;
    $counts['all']++;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inbox | LearnsDecode</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f4f7fe; color: #2b3674; }
        .sidebar { width: 280px; height: 100vh; background: #fff; position: fixed; border-right: 1px solid #e9edf7; z-index: 1000; }
        .main-content { margin-left: 280px; padding: 40px; }
        .nav-link { color: #a3aed0; padding: 15px 25px; border-radius: 12px; margin: 5px 15px; font-weight: 600; transition: 0.3s; text-decoration: none; display: block; }
        .nav-link:hover, .nav-link.active { background: #4318ff; color: white !important; box-shadow: 0px 10px 20px rgba(67, 24, 255, 0.2); }

        .msg-card { background: #fff; border-radius: 20px; border: none; transition: 0.3s; margin-bottom: 20px; border-left: 5px solid #4318ff; position: relative; }
        .msg-card:hover { transform: scale(1.01); box-shadow: 0px 20px 40px rgba(0,0,0,0.05); }
        
        .unread-dot { position: absolute; top: 15px; right: 15px; width: 12px; height: 12px; background: #ff4757; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 10px rgba(255, 71, 87, 0.5); }
        
        .type-badge { padding: 5px 15px; border-radius: 50px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .bg-msg { background: rgba(67, 24, 255, 0.1); color: #4318ff; }
        .bg-material { background: rgba(0, 184, 148, 0.1); color: #00b894; }
        .bg-quiz { background: rgba(255, 118, 117, 0.1); color: #ff7675; }
        
        .tab-btn { border: none; background: none; padding: 10px 20px; font-weight: 700; color: #a3aed0; border-bottom: 3px solid transparent; }
        .tab-btn.active { color: #4318ff; border-bottom: 3px solid #4318ff; }
        
        .notif-count { background: #ff4757; color: white; font-size: 10px; padding: 2px 8px; border-radius: 50px; }

        /* Empty state styling */
        .empty-section { display: none; text-align: center; padding: 60px; background: #fff; border-radius: 20px; border: 2px dashed #e9edf7; }
        .empty-icon { font-size: 50px; color: #cbd5e0; margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="sidebar d-flex flex-column p-3 shadow-sm">
    <div class="text-center my-4"><h3 class="fw-bold text-primary">LearnsDecode</h3></div>
    <nav class="nav flex-column flex-grow-1">
        <a class="nav-link" href="dashboard.php"><i class="fas fa-home me-2"></i> Dashboard</a>
        <a class="nav-link" href="my_courses.php"><i class="fas fa-book-reader me-2"></i> My Courses</a>
        <a class="nav-link active d-flex justify-content-between align-items-center" href="inbox.php">
            <span><i class="fas fa-envelope me-2"></i> Inbox</span>
            <?php if($unread_count > 0): ?>
                <span class="notif-count animate__animated animate__pulse animate__infinite"><?= $unread_count ?></span>
            <?php endif; ?>
        </a>
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
    <header class="mb-5 animate__animated animate__fadeInDown">
        <h2 class="fw-bold mb-0">Student Inbox 📥</h2>
        <p class="text-muted">Stay updated with your instructors.</p>
    </header>

    <div class="d-flex mb-4 border-bottom animate__animated animate__fadeIn">
        <button class="tab-btn active" onclick="filterMsg('all', this)">All Updates</button>
        <button class="tab-btn" onclick="filterMsg('msg', this)">Messages</button>
        <button class="tab-btn" onclick="filterMsg('material', this)">Materials</button>
        <button class="tab-btn" onclick="filterMsg('quiz', this)">Quizzes</button>
    </div>

    <div class="row" id="broadcast-container">
        <?php foreach($messages as $row): 
            $type = $row['msg_type'];
            $icon = ($type == 'quiz') ? 'fa-stopwatch' : (($type == 'material') ? 'fa-file-download' : 'fa-comment-dots');
            $color_class = "bg-$type";
        ?>
        <div class="col-12 msg-item animate__animated animate__fadeInUp" data-type="<?= $type ?>">
            <div class="card msg-card p-4 shadow-sm">
                <?php if($row['is_read'] == 0): ?> <div class="unread-dot"></div> <?php endif; ?>
                <div class="d-flex justify-content-between align-items-start">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle p-3 me-3 <?= $color_class ?> d-flex align-items-center justify-content-center" style="width:50px; height:50px;">
                            <i class="fas <?= $icon ?> fa-lg"></i>
                        </div>
                        <div>
                            <span class="type-badge <?= $color_class ?> mb-1 d-inline-block"><?= $type ?></span>
                            <h5 class="fw-bold mb-1"><?= $row['course_title'] ?></h5>
                            <small class="text-muted">By <?= $row['instructor_name'] ?> • <?= date('d M, h:i A', strtotime($row['created_at'])) ?></small>
                        </div>
                    </div>
                    
                    <?php if($type == 'material' && !empty($row['attachment_path'])): ?>
                        <a href="../uploads/materials/<?= $row['attachment_path'] ?>" class="btn btn-primary rounded-pill px-4" download>Download <i class="fas fa-download ms-2"></i></a>
                    <?php elseif($type == 'quiz'): 
                        $quiz_id = $row['quiz_id'];
                        // Check if student already attempted this quiz
                        $attempt_check = mysqli_query($conn, "
                            SELECT id FROM quiz_attempts 
                            WHERE quiz_id = '$quiz_id' 
                            AND enrollment_id = (SELECT id FROM enrollments WHERE student_id = '$user_id' AND course_id = '{$row['course_id']}' LIMIT 1)
                        ");

                        if(mysqli_num_rows($attempt_check) > 0): ?>
                            <span class="badge bg-success rounded-pill px-4 py-2" style="font-size: 0.9rem;">
                                Completed <i class="fas fa-check-circle ms-1"></i>
                            </span>
                        <?php else: ?>
                            <a href="take_quiz.php?id=<?= $quiz_id ?>" class="btn btn-danger rounded-pill px-4">Attempt Quiz <i class="fas fa-arrow-right ms-2"></i></a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <hr>
                <p class="mb-0 text-dark" style="white-space: pre-wrap;"><?= $row['content'] ?></p>
            </div>
        </div>
        <?php endforeach; ?>

        <div id="empty-all" class="empty-section col-12" style="<?= ($counts['all'] == 0) ? 'display:block;' : '' ?>">
            <div class="empty-icon"><i class="fas fa-inbox"></i></div>
            <h5>No updates yet!</h5>
        </div>
        <div id="empty-msg" class="empty-section col-12">
            <div class="empty-icon"><i class="fas fa-comment-slash"></i></div>
            <h5>No messages found.</h5>
        </div>
        <div id="empty-material" class="empty-section col-12">
            <div class="empty-icon"><i class="fas fa-file-excel"></i></div>
            <h5>No materials uploaded.</h5>
        </div>
        <div id="empty-quiz" class="empty-section col-12">
            <div class="empty-icon"><i class="fas fa-hourglass-end"></i></div>
            <h5>No quizzes available.</h5>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function filterMsg(type, btn) {
        $('.tab-btn').removeClass('active');
        $(btn).addClass('active');
        $('.empty-section').hide();

        if(type === 'all') {
            $('.msg-item').show();
            if($('.msg-item').length === 0) $('#empty-all').show();
        } else {
            $('.msg-item').hide();
            let items = $('.msg-item[data-type="' + type + '"]');
            if(items.length > 0) {
                items.show();
            } else {
                $('#empty-' + type).show(); 
            }
        }
    }
</script>
</body>
</html>