<?php
session_start();
require '../config/config.php';

if(!isset($_SESSION['user_id']) || !isset($_GET['attempt_id'])){
    header("Location: my_quizzes.php");
    exit();
}

$attempt_id = (int)$_GET['attempt_id'];
$user_id = $_SESSION['user_id'];

$attempt_q = mysqli_query($conn, "
    SELECT a.*, q.title, q.id as quiz_id, q.total_marks 
    FROM quiz_attempts a
    JOIN quizzes q ON a.quiz_id = q.id
    JOIN enrollments e ON a.enrollment_id = e.id
    WHERE a.id = '$attempt_id' AND e.student_id = '$user_id'
");
$attempt = mysqli_fetch_assoc($attempt_q);

if(!$attempt){
    die("Attempt data not found!");
}

$quiz_id = $attempt['quiz_id'];
$questions_q = mysqli_query($conn, "SELECT * FROM quiz_questions WHERE quiz_id = '$quiz_id'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review: <?= htmlspecialchars($attempt['title']) ?> | LearnsDecode</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <style>
        :root { --primary: #4318ff; --success: #05cd99; --danger: #ee5d50; --bg: #f4f7fe; }
        body { background: var(--bg); font-family: 'Plus Jakarta Sans', sans-serif; color: #2b3674; }

        /* Sticky Glass Header */
        .review-header { 
            position: sticky; top: 0; background: rgba(255, 255, 255, 0.8); 
            backdrop-filter: blur(12px); z-index: 1000; border-bottom: 1px solid #e9edf7; 
            padding: 20px 0; margin-bottom: 40px;
        }

        .review-card { 
            background: white; border-radius: 24px; padding: 30px; 
            margin-bottom: 30px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.03); 
            transition: 0.3s;
        }
        .review-card:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(0,0,0,0.06); }

        .q-badge { 
            width: 35px; height: 35px; background: var(--primary); color: white; 
            border-radius: 10px; display: inline-flex; align-items: center; 
            justify-content: center; font-weight: 800; margin-right: 15px;
        }

        /* Option Styling */
        .option-item { 
            padding: 18px 25px; border-radius: 16px; margin-bottom: 12px; 
            border: 2px solid #f4f7fe; font-weight: 600; display: flex; 
            justify-content: space-between; align-items: center; position: relative;
        }
        
        .correct { 
            background: #05cd9910; border-color: var(--success); color: var(--success); 
            box-shadow: 0 4px 15px rgba(5, 205, 153, 0.1);
        }
        .wrong { 
            background: #ee5d5010; border-color: var(--danger); color: var(--danger);
            animation: shakeX 0.5s; 
        }
        
        .status-icon { font-size: 1.2rem; }

        .score-box { background: var(--primary); color: white; padding: 5px 15px; border-radius: 10px; font-weight: 700; }

        /* Staggered Entrance Animation */
        <?php for($i=1; $i<=25; $i++): ?>
            .item-<?= $i ?> { animation-delay: <?= $i * 0.1 ?>s; }
        <?php endfor; ?>
    </style>
</head>
<body>

<header class="review-header">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-800 mb-0"><?= htmlspecialchars($attempt['title']) ?></h4>
            <span class="text-muted small">Detailed Result Review</span>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="score-box">Score: <?= $attempt['score'] ?> / <?= $attempt['total_marks'] ?></div>
            <a href="my_quizzes.php" class="btn btn-outline-primary btn-sm rounded-pill fw-bold px-4">Exit Review</a>
        </div>
    </div>
</header>

<div class="container pb-5">
    <?php 
    $q_index = 1;
    while($q = mysqli_fetch_assoc($questions_q)): 
        $q_id = $q['id'];
        
        // Student's response
        $resp_q = mysqli_query($conn, "SELECT selected_option_id FROM quiz_responses WHERE attempt_id = '$attempt_id' AND question_id = '$q_id'");
        $resp = mysqli_fetch_assoc($resp_q);
        $selected_id = $resp['selected_option_id'] ?? null;
    ?>
    <div class="review-card animate__animated animate__fadeInUp item-<?= $q_index ?>">
        <div class="d-flex align-items-start mb-4">
            <span class="q-badge"><?= $q_index ?></span>
            <h5 class="fw-bold mb-0 mt-1"><?= htmlspecialchars($q['question_text']) ?></h5>
        </div>
        
        <div class="options-list">
            <?php 
            $options_q = mysqli_query($conn, "SELECT * FROM quiz_options WHERE question_id = '$q_id'");
            while($opt = mysqli_fetch_assoc($options_q)):
                $class = "";
                $icon = "";

                if($opt['is_correct'] == 1){
                    $class = "correct";
                    $icon = '<i class="fas fa-check-circle status-icon"></i>';
                } elseif($opt['id'] == $selected_id) {
                    $class = "wrong";
                    $icon = '<i class="fas fa-times-circle status-icon"></i>';
                }
            ?>
            <div class="option-item <?= $class ?>">
                <span><?= htmlspecialchars($opt['option_text']) ?></span>
                <?= $icon ?>
            </div>
            <?php endwhile; ?>
        </div>

        <?php if($selected_id === null): ?>
            <p class="text-muted small mt-2 ms-2 italic text-danger"><i class="fas fa-info-circle me-1"></i> You did not answer this question.</p>
        <?php endif; ?>
    </div>
    <?php $q_index++; endwhile; ?>

    <div class="text-center mt-5">
        <p class="text-muted mb-4 small">Review finished. You can close this page now.</p>
        <a href="my_quizzes.php" class="btn btn-primary rounded-pill px-5 py-3 fw-bold shadow-lg">Back to My Quizzes</a>
    </div>
</div>

</body>
</html>