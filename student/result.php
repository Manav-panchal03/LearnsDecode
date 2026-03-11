<?php
session_start();
require '../config/config.php';

if(!isset($_SESSION['user_id']) || !isset($_GET['quiz_id'])){
    header("Location: inbox.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$quiz_id = (int)$_GET['quiz_id'];

$result_q = mysqli_query($conn, "
    SELECT a.*, q.title, q.total_marks 
    FROM quiz_attempts a
    JOIN quizzes q ON a.quiz_id = q.id
    JOIN enrollments e ON a.enrollment_id = e.id
    WHERE a.quiz_id = '$quiz_id' AND e.student_id = '$user_id'
    ORDER BY a.attempted_at DESC LIMIT 1
");

$result = mysqli_fetch_assoc($result_q);

if(!$result){
    die("Result data not found!");
}

$percentage = ($result['score'] / $result['total_marks']) * 100;
$status = ($percentage >= 40) ? 'Pass' : 'Failed';
$theme_color = ($percentage >= 40) ? '#4318ff' : '#ff5252';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Result | LearnsDecode</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <style>
        :root { --primary: <?= $theme_color ?>; --bg: #f4f7fe; }
        body { background: var(--bg); font-family: 'Plus Jakarta Sans', sans-serif; overflow-x: hidden; }

        .result-container { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        
        .result-card { 
            background: white; border-radius: 40px; padding: 50px; 
            text-align: center; box-shadow: 0 25px 50px rgba(0,0,0,0.08); 
            border: none; position: relative; max-width: 600px; width: 100%;
            overflow: hidden;
        }

        /* Floating Animation for Trophy */
        .icon-box {
            font-size: 80px; color: #ffbc00; margin-bottom: 20px;
            animation: floating 3s ease-in-out infinite;
        }

        @keyframes floating {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }

        .score-display {
            background: var(--bg); border-radius: 30px; padding: 30px;
            margin: 30px 0; transition: 0.3s; border: 2px solid transparent;
        }
        .score-display:hover { border-color: var(--primary); transform: scale(1.02); }

        .huge-text { font-size: 4rem; font-weight: 800; color: var(--primary); line-height: 1; }
        
        .stat-card {
            background: #fff; border: 1px solid #e9edf7; padding: 20px;
            border-radius: 20px; transition: 0.3s;
        }
        .stat-card:hover { background: var(--primary); color: white; transform: translateY(-5px); }
        .stat-card:hover .text-muted { color: rgba(255,255,255,0.8) !important; }

        .btn-action {
            padding: 15px 30px; border-radius: 18px; font-weight: 700;
            transition: 0.4s; border: none;
        }
        .btn-primary-custom { background: var(--primary); color: white; box-shadow: 0 10px 20px rgba(67, 24, 255, 0.2); }
        .btn-primary-custom:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(67, 24, 255, 0.3); color: white; }

        /* Background Decorations */
        .circle-deco {
            position: absolute; width: 200px; height: 200px;
            background: var(--primary); opacity: 0.05; border-radius: 50%;
            z-index: 0;
        }
    </style>
</head>
<body>

<div class="result-container">
    <div class="result-card animate__animated animate__zoomIn">
        <div class="circle-deco" style="top: -100px; right: -100px;"></div>
        <div class="circle-deco" style="bottom: -100px; left: -100px;"></div>

        <div class="position-relative" style="z-index: 1;">
            <?php if($percentage >= 40): ?>
                <div class="icon-box"><i class="fas fa-trophy"></i></div>
                <h1 class="fw-800 animate__animated animate__bounceInDown">Grand Success!</h1>
            <?php else: ?>
                <div class="icon-box" style="color: #6c757d;"><i class="fas fa-heart-broken"></i></div>
                <h1 class="fw-800">Keep Practicing!</h1>
            <?php endif; ?>

            <p class="text-muted fs-5">You've successfully completed <br><span class="text-dark fw-bold"><?= htmlspecialchars($result['title']) ?></span></p>

            <div class="score-display animate__animated animate__fadeInUp animate__delay-1s">
                <span class="text-muted fw-600 d-block mb-2">YOUR TOTAL SCORE</span>
                <div class="huge-text"><?= $result['score'] ?></div>
                <div class="fw-bold text-muted">OUT OF <?= $result['total_marks'] ?></div>
            </div>

            <div class="row g-3 mb-5 animate__animated animate__fadeInUp animate__delay-1s">
                <div class="col-6">
                    <div class="stat-card">
                        <small class="text-muted d-block">Accuracy</small>
                        <h3 class="fw-bold mb-0"><?= number_format($percentage, 0) ?>%</h3>
                    </div>
                </div>
                <div class="col-6">
                    <div class="stat-card">
                        <small class="text-muted d-block">Result</small>
                        <h3 class="fw-bold mb-0"><?= $status ?></h3>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-column gap-3 animate__animated animate__fadeInUp animate__delay-2s">
                <a href="inbox.php" class="btn btn-action btn-primary-custom">
                    <i class="fas fa-home me-2"></i> Back to Inbox
                </a>
                <a href="my_quizzes.php" class="btn btn-action btn-light">
                    <i class="fas fa-search me-2"></i> Review Your Answers
                </a>
            </div>

            <div class="mt-4 animate__animated animate__fadeIn animate__delay-3s">
                <small class="text-muted">Completed on <?= date('F j, Y • g:i A', strtotime($result['attempted_at'])) ?></small>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
<script>
    window.onload = function() {
        <?php if($percentage >= 40): ?>
        // Pass thay to confetti udado
        var duration = 3 * 1000;
        var end = Date.now() + duration;

        (function frame() {
            confetti({
                particleCount: 3,
                angle: 60,
                spread: 55,
                origin: { x: 0 },
                colors: ['#4318ff', '#ffbc00', '#00b894']
            });
            confetti({
                particleCount: 3,
                angle: 120,
                spread: 55,
                origin: { x: 1 },
                colors: ['#4318ff', '#ffbc00', '#00b894']
            });

            if (Date.now() < end) {
                requestAnimationFrame(frame);
            }
        }());
        <?php endif; ?>
    };
</script>

</body>
</html>