<?php
session_start();
require '../config/config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Attempted quizzes fetch karvi marks sathe
$attempts_q = mysqli_query($conn, "
    SELECT a.*, q.title, q.total_marks, q.id as q_id
    FROM quiz_attempts a
    JOIN quizzes q ON a.quiz_id = q.id
    JOIN enrollments e ON a.enrollment_id = e.id
    WHERE e.student_id = '$user_id'
    ORDER BY a.attempted_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Achievements | LearnsDecode</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <style>
        :root { --primary: #4318ff; --bg: #f4f7fe; --secondary: #707eae; }
        body { background: var(--bg); font-family: 'Plus Jakarta Sans', sans-serif; color: #2b3674; }

        .header-section { margin-top: 40px; margin-bottom: 40px; }
        
        .quiz-card { 
            background: white; border-radius: 24px; padding: 25px; 
            margin-bottom: 20px; border: none; transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex; align-items: center; justify-content: space-between;
            box-shadow: 0 10px 30px rgba(0,0,0,0.02); position: relative; overflow: hidden;
        }

        .quiz-card:hover { transform: scale(1.02); box-shadow: 0 20px 40px rgba(67, 24, 255, 0.08); }

        .icon-circle {
            width: 60px; height: 60px; border-radius: 18px; 
            background: #f4f7fe; display: flex; align-items: center; 
            justify-content: center; font-size: 24px; color: var(--primary);
            margin-right: 20px; transition: 0.3s;
        }
        .quiz-card:hover .icon-circle { background: var(--primary); color: white; transform: rotate(10deg); }

        .score-pill {
            padding: 10px 20px; border-radius: 14px; font-weight: 800;
            font-size: 1rem; display: inline-block;
        }
        .bg-soft-success { background: #d1fae5; color: #065f46; }
        .bg-soft-danger { background: #fee2e2; color: #991b1b; }

        .btn-review {
            background: white; border: 2px solid #e9edf7; color: var(--primary);
            padding: 10px 25px; border-radius: 14px; font-weight: 700; transition: 0.3s;
        }
        .btn-review:hover { background: var(--primary); color: white; border-color: var(--primary); transform: translateX(5px); }

        /* Empty State Animation */
        .empty-state { animation: float 3s ease-in-out infinite; }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        /* Staggered Delay for List Items */
        <?php for($i=1; $i<=20; $i++): ?>
            .item-<?= $i ?> { animation-delay: <?= $i * 0.1 ?>s; }
        <?php endfor; ?>
    </style>
</head>
<body>

<div class="container py-5">
    <div class="header-section d-flex justify-content-between align-items-end animate__animated animate__fadeIn">
        <div>
            <h1 class="fw-800 mb-1">My Quiz Journey</h1>
            <p class="text-muted mb-0">Track your performance and review your answers.</p>
        </div>
        <a href="dashboard.php" class="btn btn-light shadow-sm rounded-pill px-4 fw-bold">
            <i class="fas fa-arrow-left me-2"></i> Dashboard
        </a>
    </div>

    <div class="row">
        <?php 
        $count = 1;
        if(mysqli_num_rows($attempts_q) > 0): 
            while($row = mysqli_fetch_assoc($attempts_q)): 
                $per = ($row['score'] / $row['total_marks']) * 100;
                $status_class = ($per >= 40) ? 'bg-soft-success' : 'bg-soft-danger';
                $status_icon = ($per >= 40) ? 'fa-check-double' : 'fa-exclamation-triangle';
        ?>
        <div class="col-12 animate__animated animate__fadeInUp item-<?= $count ?>">
            <div class="quiz-card">
                <div class="d-flex align-items-center">
                    <div class="icon-circle">
                        <i class="fas <?= $status_icon ?>"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1"><?= htmlspecialchars($row['title']) ?></h5>
                        <p class="text-muted small mb-0">
                            <i class="far fa-calendar-alt me-1"></i> <?= date('M d, Y • h:i A', strtotime($row['attempted_at'])) ?>
                        </p>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-4">
                    <div class="text-center">
                        <span class="score-pill <?= $status_class ?>">
                            <?= $row['score'] ?> / <?= $row['total_marks'] ?>
                        </span>
                    </div>
                    <a href="quiz_review.php?attempt_id=<?= $row['id'] ?>" class="btn btn-review">
                        Review <i class="fas fa-chevron-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
        <?php 
            $count++;
            endwhile; 
        else: 
        ?>
        <div class="col-12 text-center py-5">
            <div class="empty-state">
                <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="120" class="mb-4 opacity-50">
            </div>
            <h3 class="fw-bold">No attempts yet!</h3>
            <p class="text-muted">You haven't attempted any quizzes yet.</p>
            <a href="inbox.php" class="btn btn-primary rounded-pill px-5 py-3 mt-3 shadow-lg fw-bold">Start First Quiz</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html>