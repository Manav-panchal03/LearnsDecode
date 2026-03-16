<?php
session_start();
require '../config/config.php';

if(!isset($_SESSION['user_id']) || !isset($_GET['id'])){
    header("Location: inbox.php");
    exit();
}

$quiz_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// 1. Check attempt logic
$check_attempt = mysqli_query($conn, "SELECT id FROM quiz_attempts WHERE quiz_id = '$quiz_id' AND enrollment_id = (SELECT id FROM enrollments WHERE student_id = '$user_id' LIMIT 1)");

if(mysqli_num_rows($check_attempt) > 0){
    echo "<script>alert('You have already attempted this quiz.'); window.location='inbox.php';</script>";
    exit();
}

// 2. Quiz details fetch
$quiz_q = mysqli_query($conn, "SELECT * FROM quizzes WHERE id = '$quiz_id'");
$quiz = mysqli_fetch_assoc($quiz_q);

if(!$quiz){
    die("<div style='text-align:center; padding:100px; font-family:sans-serif;'><h2>Quiz Not Found!</h2><a href='inbox.php'>Go Back</a></div>");
}

$questions_q = mysqli_query($conn, "SELECT * FROM quiz_questions WHERE quiz_id = '$quiz_id'");
$total_q = mysqli_num_rows($questions_q);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($quiz['title']) ?> | LearnsDecode</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        
        :root { --primary: #4318ff; --secondary: #6c757d; --bg: #f4f7fe; --text-dark: #2b3674; }
        body { background: var(--bg); font-family: 'Plus Jakarta Sans', sans-serif; color: var(--text-dark); }
        
        /* Fixed Header */
        .quiz-header { position: sticky; top: 0; background: white; z-index: 1000; border-bottom: 2px solid #e9edf7; padding: 15px 0; }
        
        .status-pill { background: #f4f7fe; padding: 10px 20px; border-radius: 12px; font-weight: 700; color: var(--primary); border: 1px solid #e9edf7; }

        .quiz-card { background: white; border-radius: 24px; padding: 35px; box-shadow: 0 20px 50px rgba(0,0,0,0.04); border: none; margin-top: 30px; }
        
        .question-box { background: #f8faff; border-radius: 20px; padding: 25px; margin-bottom: 30px; border: 1px solid #eef2f8; transition: 0.3s; }
        .question-box:hover { border-color: var(--primary); }

        .question-text { font-size: 1.2rem; font-weight: 700; color: var(--text-dark); margin-bottom: 20px; display: block; }
        
        /* Custom Options */
        .option-container { position: relative; margin-bottom: 12px; }
        .option-input { position: absolute; opacity: 0; cursor: pointer; height: 0; width: 0; }
        
        .option-label { 
            display: flex; align-items: center; padding: 15px 20px; 
            background: white; border: 2px solid #e9edf7; border-radius: 14px; 
            cursor: pointer; transition: 0.3s; font-weight: 600;
        }
        
        .option-input:checked + .option-label { 
            border-color: var(--primary); background: #4318ff08; color: var(--primary);
            box-shadow: 0 4px 12px rgba(67, 24, 255, 0.1);
        }

        .check-dot { 
            height: 18px; width: 18px; background-color: #fff; border: 2px solid #d1d9e8; 
            border-radius: 50%; margin-right: 15px; display: inline-block; position: relative;
        }

        .option-input:checked + .option-label .check-dot { 
            background-color: var(--primary); border-color: var(--primary); 
        }
        .option-input:checked + .option-label .check-dot::after {
            content: ""; position: absolute; display: block; left: 4px; top: 1px;
            width: 5px; height: 10px; border: solid white; border-width: 0 2px 2px 0; transform: rotate(45deg);
        }

        .btn-submit { 
            background: var(--primary); color: white; border: none; padding: 18px; 
            border-radius: 18px; font-weight: 800; font-size: 1.1rem; width: 100%;
            transition: 0.4s; margin-top: 20px; box-shadow: 0 10px 25px rgba(67, 24, 255, 0.25);
        }
        .btn-submit:hover { transform: translateY(-3px); background: #3311cc; box-shadow: 0 15px 30px rgba(67, 24, 255, 0.35); }

        .q-number { background: var(--primary); color: white; width: 32px; height: 32px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.9rem; margin-right: 12px; }
    </style>
</head>
<body>

<header class="quiz-header shadow-sm">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold mb-0 text-primary"><i class="fas fa-graduation-cap me-2"></i>LearnsDecode</h5>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="status-pill">
                <span id="answeredCount">0</span> / <?= $total_q ?> Answered
            </div>
        </div>
    </div>
</header>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="quiz-card animate__animated animate__fadeIn">
                <div class="mb-5">
                    <h2 class="fw-extrabold mb-1"><?= htmlspecialchars($quiz['title']) ?></h2>
                    <p class="text-muted">Answer all the questions to submit the quiz.</p>
                </div>

                <form action="process_quiz.php" method="POST" id="quizForm">
                    <input type="hidden" name="quiz_id" value="<?= $quiz_id ?>">
                    
                    <?php 
                    $q_index = 1;
                    if($total_q > 0):
                        while($q = mysqli_fetch_assoc($questions_q)): 
                            $q_id = $q['id'];
                    ?>
                    <div class="question-box">
                        <span class="question-text">
                            <span class="q-number"><?= $q_index ?></span>
                            <?= htmlspecialchars($q['question_text']) ?>
                        </span>
                        
                        <div class="options-wrapper">
                            <?php 
                            $options_q = mysqli_query($conn, "SELECT * FROM quiz_options WHERE question_id = '$q_id'");
                            while($opt = mysqli_fetch_assoc($options_q)):
                            ?>
                            <div class="option-container">
                                <input type="radio" name="answers[<?= $q_id ?>]" id="opt_<?= $opt['id'] ?>" class="option-input" value="<?= $opt['id'] ?>" required>
                                <label for="opt_<?= $opt['id'] ?>" class="option-label">
                                    <span class="check-dot"></span>
                                    <?= htmlspecialchars($opt['option_text']) ?>
                                </label>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                    <?php $q_index++; endwhile; ?>

                    <button type="submit" class="btn btn-submit">
                        Submit Quiz <i class="fas fa-chevron-right ms-2"></i>
                    </button>

                    <?php else: ?>
                    <div class="text-center py-5">
                        <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="80" class="mb-3 opacity-50">
                        <h5>No questions available for this quiz.</h5>
                    </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Questions count update logic
        $('.option-input').on('change', function() {
            let answered = $('input[type="radio"]:checked').length;
            $('#answeredCount').text(answered);
            
            // Effect on status pill
            $('.status-pill').addClass('animate__animated animate__pulse');
            setTimeout(() => {
                $('.status-pill').removeClass('animate__animated animate__pulse');
            }, 500);
        });
    });

</script>

<script>
let switchCount = 0;

document.addEventListener("visibilitychange", function() {
    if (document.hidden) {
        switchCount++;

        if (switchCount === 1) {
            // First Warning
            Swal.fire({
                icon: 'warning',
                title: 'Warning 1/2',
                text: 'You switched tabs! Please stay on this page to complete your quiz.',
                confirmButtonColor: '#6c63ff',
                confirmButtonText: 'I Understand'
            });
        } 
        else if (switchCount === 2) {
            // Second & Last Warning
            Swal.fire({
                icon: 'error',
                title: 'Final Warning!',
                text: 'If you switch tabs one more time, your quiz will be automatically submitted!',
                confirmButtonColor: '#d33',
                confirmButtonText: 'Last Chance'
            });
        } 
        else if (switchCount >= 3) {
            // Final Action: Auto Submit
            Swal.fire({
                icon: 'info',
                title: 'Quiz Terminated',
                text: 'Multiple tab switches detected. For security reasons, your quiz has been submitted automatically.',
                allowOutsideClick: false,
                showConfirmButton: false,
                timer: 3000, // 3 seconds pachi submit thase
                willClose: () => {
                    document.getElementById("quizForm").submit();
                }
            });
        }
    }
});

// Extra layer: Window Blur (jyare biji window upar click kare)
window.onblur = function() {
    // Tame aya pan same logic use kari shako cho jo bau strict rakhvu hoy toh
};
</script>

</body>
</html>