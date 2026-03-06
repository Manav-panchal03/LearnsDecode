<?php
session_start();
require '../config/config.php';

// Security Check
if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'instructor'){
    header("Location: ../login.php");
    exit();
}

$instructor_id = $_SESSION['user_id'];

// Publish Logic
if(isset($_GET['publish_id'])){
    $pid = mysqli_real_escape_string($conn, $_GET['publish_id']);
    mysqli_query($conn, "UPDATE quizzes SET status = 'published' WHERE id = $pid");
    header("Location: manage_quizzes.php?msg=published");
    exit();
}

// Fetch Quizzes with question count
$sql = "SELECT q.*, c.title as course_title, c.thumbnail as course_image, 
        (SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = q.id) as total_ques 
        FROM quizzes q 
        JOIN courses c ON q.course_id = c.id 
        WHERE c.instructor_id = $instructor_id 
        ORDER BY q.id DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Quizzes | LearnsDecode</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f7f6; overflow-x: hidden; }
        .quiz-row { background: white; border-radius: 15px; transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); border: 1px solid #eee; }
        .quiz-row:hover { transform: scale(1.01); box-shadow: 0 10px 25px rgba(0,0,0,0.08); }
        .status-badge { font-size: 0.75rem; padding: 5px 12px; border-radius: 50px; }
        .bg-draft { background: #fff4e5; color: #ff9800; }
        .bg-published { background: #e6fffa; color: #38b2ac; }
        
        /* Custom Empty State Styling */
        .empty-state-card {
            background: white;
            padding: 60px;
            border-radius: 20px;
            border: 2px dashed #cbd5e1;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeInDown">
        <div class="d-flex align-items-center mb-4">
    <a href="dashboard.php" class="text-decoration-none text-secondary me-3 shadow-sm bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
        <i class="fas fa-arrow-left"></i>
    </a>
    
    <h2 class="fw-bold mb-0"><i class="fas fa-tasks text-primary me-2"></i>Manage <span class="text-primary">Quizzes</span></h2>
</div>

<a href="add_quiz.php" class="btn btn-primary rounded-pill px-4 shadow-sm hover-up">
    <i class="fas fa-plus me-2"></i>Create New Quiz
</a>
    </div>

    <div class="row g-3">
        <?php 
        $delay = 0.1; // Animation delay factor
        if(mysqli_num_rows($result) > 0): 
            while($row = mysqli_fetch_assoc($result)): 
        ?>
            <div id="quiz-card-<?= $row['id'] ?>" 
                class="col-12 quiz-row p-3 mb-2 d-flex align-items-center justify-content-between animate__animated animate__fadeInUp" 
                style="animation-delay: <?= $delay ?>s">
                
                <div class="d-flex align-items-center gap-3">
                    <div class="quiz-icon animate__animated animate__zoomIn" style="animation-delay: <?= $delay + 0.2 ?>s">
                        <?php if(!empty($row['course_image'])): ?>
                            <img src="../uploads/thumbnails/<?= htmlspecialchars($row['course_image']) ?>" alt="Quiz Icon" style="width: 50px; height: 50px; border-radius: 10px; object-fit: cover;">
                        <?php else: ?>
                            <div class="default-icon bg-light d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; border-radius: 10px;">
                                <i class="fas fa-brain text-primary"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0"><?= htmlspecialchars($row['title']) ?></h6>
                        <small class="text-muted"><?= htmlspecialchars($row['course_title']) ?> • <?= $row['total_ques'] ?> Questions</small>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-4">
                    <span class="status-badge <?= ($row['status'] == 'published') ? 'bg-published' : 'bg-draft' ?> animate__animated animate__fadeInRight" style="animation-delay: <?= $delay + 0.3 ?>s">
                        <?= strtoupper($row['status']) ?>
                    </span>

                    <div class="actions">
                        <a href="review_quiz.php?quiz_id=<?= $row['id'] ?>" class="btn btn-sm btn-light border" title="Edit Questions"><i class="fas fa-list"></i></a>
                        
                        <?php if($row['status'] == 'draft'): ?>
                            <a href="manage_quizzes.php?publish_id=<?= $row['id'] ?>" class="btn btn-sm btn-success ms-2 px-3" onclick="return confirm('Publish this quiz for students?')">
                                <i class="fas fa-paper-plane me-1"></i> Publish
                            </a>
                        <?php else: ?>
                            <button class="btn btn-sm btn-outline-secondary ms-2" disabled><i class="fas fa-check"></i> Live</button>
                        <?php endif; ?>

                        <button type="button" onclick="deleteQuiz(<?= $row['id'] ?>, 'quiz-card-<?= $row['id'] ?>')" class="btn btn-sm btn-light text-danger border ms-2">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        <?php 
            $delay += 0.05; // Incremental delay for each card
            endwhile; 
        else: 
        ?>
            <div class="col-12 text-center animate__animated animate__zoomIn">
                <div class="empty-state-card shadow-sm">
                    <i class="fas fa-folder-open text-muted mb-3 animate__animated animate__pulse animate__infinite" style="font-size: 4rem;"></i>
                    <h4 class="fw-bold text-dark">No Quizzes Found</h4>
                    <p class="text-muted">You haven't created any quizzes yet.</p>
                    <a href="add_quiz.php" class="btn btn-primary rounded-pill px-5">Get Started</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function deleteQuiz(quizId, cardId) {
    Swal.fire({
        title: 'Delete Quiz?',
        text: "Associated questions & options will also be deleted!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, delete it!',
        showClass: { popup: 'animate__animated animate__backInDown' },
        hideClass: { popup: 'animate__animated animate__backOutUp' }
    }).then((result) => {
        if (result.isConfirmed) {
            // 1. Smooth Slide Out Animation
            $(`#${cardId}`).removeClass('animate__fadeInUp').addClass('animate__fadeOutRight');
            
            setTimeout(() => { 
                $(`#${cardId}`).hide(); 
            }, 500);

            const Toast = Swal.mixin({
                toast: true,
                position: 'bottom-start',
                showConfirmButton: true,
                confirmButtonText: 'UNDO',
                timer: 5000,
                timerProgressBar: true
            });

            Toast.fire({
                icon: 'success',
                title: 'Quiz moved to trash'
            }).then((undoResult) => {
                if (undoResult.isConfirmed) {
                    // 2. Undo Slide In Animation
                    $(`#${cardId}`).show().removeClass('animate__fadeOutRight').addClass('animate__fadeInLeft');
                } else {
                    // 3. Final AJAX Delete
                    $.ajax({
                        url: 'delete_quiz_ajax.php',
                        type: 'POST',
                        data: { quiz_id: quizId },
                        success: function(response) {
                            if(response.trim() === "success") {
                                $(`#${cardId}`).remove();
                                if ($('.quiz-row:visible').length === 0) {
                                    location.reload(); // Show empty state
                                }
                            } else {
                                $(`#${cardId}`).show().removeClass('animate__fadeOutRight');
                                Swal.fire('Error!', response, 'error');
                            }
                        }
                    });
                }
            });
        }
    });
}
</script>

</body>
</html>