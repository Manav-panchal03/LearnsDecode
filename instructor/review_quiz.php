<?php
session_start();
require '../config/config.php';
require_once '../includes/session_utils.php';

activateRole('instructor');


// security check
if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'instructor'){
    header("Location: ../login.php"); exit();
}

if (!isset($_GET['quiz_id'])) {
    header("Location: manage_quizzes.php");
    exit();
}

$quiz_id = $_GET['quiz_id'];

// Publish Logic
if (isset($_POST['publish_quiz'])) {
    mysqli_query($conn, "UPDATE quizzes SET status = 'published' WHERE id = $quiz_id");
    echo "<script>alert('Quiz Published Successfully!'); window.location.href='manage_quizzes.php';</script>";
}

// ક્વિઝ અને કોર્સની વિગતો મેળવો
$quiz_res = mysqli_query($conn, "SELECT q.*, c.title as course_title FROM quizzes q JOIN courses c ON q.course_id = c.id WHERE q.id = $quiz_id");
$quiz = mysqli_fetch_assoc($quiz_res);

// પ્રશ્નો મેળવો
$questions_res = mysqli_query($conn, "SELECT * FROM quiz_questions WHERE quiz_id = $quiz_id");
$total_questions = mysqli_num_rows($questions_res);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review: <?= $quiz['title'] ?> | LearnsDecode</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f8fafc; color: #1e293b; }
        
        .header-section {
            background: white;
            padding: 1.5rem 0;
            border-bottom: 1px solid #e2e8f0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        }

        .breadcrumb-item a { color: #64748b; text-decoration: none; font-size: 0.85rem; }
        .breadcrumb-item.active { color: #3b82f6; font-size: 0.85rem; }

        .q-card {
            background: white;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            overflow: hidden;
        }
        .q-card:hover { transform: translateY(-5px); box-shadow: 0 12px 25px rgba(0,0,0,0.05); }

        .q-header { background: #f1f5f9; }

        .option-item {
            border: 1.5px solid #f1f5f9;
            border-radius: 12px;
            padding: 12px 18px;
            transition: 0.2s;
        }

        .correct-style {
            background-color: #f0fff4 !important;
            border-color: #38a169 !important;
            color: #166534 !important;
        }

        .action-btn {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: 0.3s;
        }
        .btn-edit { background: #eff6ff; color: #2563eb; }
        .btn-delete { background: #fef2f2; color: #dc2626; }
        .btn-edit:hover { background: #2563eb; color: white; }
        .btn-delete:hover { background: #dc2626; color: white; }

        .empty-state {
            padding: 5rem 0;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="header-section">
    <div class="container">
        <nav aria-label="breadcrumb" class="mb-2">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="manage_quizzes.php">Manage Quizzes</a></li>
                <li class="breadcrumb-item active"><?= $quiz['course_title'] ?></li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-0 text-dark"><?= $quiz['title'] ?></h3>
                <span class="text-muted small"><i class="far fa-file-alt me-1"></i> Total <?= $total_questions ?> Questions</span>
            </div>
            <div class="d-flex gap-2">
                <a href="add_questions.php?quiz_id=<?= $quiz_id ?>" class="btn btn-outline-primary rounded-pill px-4 fw-500">
                    <i class="fas fa-plus me-2"></i>Add More
                </a>
                <form method="POST">
                    <button type="submit" name="publish_quiz" class="btn btn-success rounded-pill px-4 shadow-sm fw-500">
                        <i class="fas fa-rocket me-2"></i>Publish Quiz
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            <?php if ($total_questions > 0): ?>
                <?php 
                $q_num = 1;
                while($q = mysqli_fetch_assoc($questions_res)): 
                    $q_id = $q['id'];
                    $opt_res = mysqli_query($conn, "SELECT * FROM quiz_options WHERE question_id = $q_id");
                ?>
                
                <div class="card q-card mb-4 animate__animated animate__fadeInUp" id="q-card-<?= $q_id ?>">
                    <div class="q-header d-flex justify-content-between align-items-center p-3 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary rounded-pill px-3">Question <?= $q_num++ ?></span>
                            <span class="badge bg-white text-dark border rounded-pill fw-normal">Marks: <?= $q['marks'] ?></span>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="edit_question.php?id=<?= $q_id ?>" class="action-btn btn-edit text-decoration-none" title="Edit">
                                <i class="fas fa-pen"></i>
                            </a>
                            <button type="button" class="action-btn btn-delete" 
                                    onclick="confirmDelete(<?= $q_id ?>, 'q-card-<?= $q_id ?>')" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>

                    <div class="q-body p-4">
                        <h5 class="fw-bold mb-4" style="line-height: 1.6; color: #334155;">
                            <?= nl2br(htmlspecialchars($q['question_text'])) ?>
                        </h5>
                        
                        <div class="row g-3">
                            <?php while($opt = mysqli_fetch_assoc($opt_res)): ?>
                                <div class="col-md-6">
                                    <div class="option-item d-flex align-items-center <?= $opt['is_correct'] ? 'correct-style shadow-sm' : 'bg-white' ?>">
                                        <div class="me-3">
                                            <?php if($opt['is_correct']): ?>
                                                <i class="fas fa-check-circle fs-5"></i>
                                            <?php else: ?>
                                                <i class="far fa-circle text-muted fs-5"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-grow-1">
                                            <?= htmlspecialchars($opt['option_text']) ?>
                                        </div>
                                        <?php if($opt['is_correct']): ?>
                                            <span class="badge bg-success small ms-2">Correct</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>

            <?php else: ?>
                <div class="empty-state animate__animated animate__fadeIn">
                    <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="120" class="mb-4 opacity-50">
                    <h4 class="fw-bold text-muted">No questions added yet!</h4>
                    <p class="text-secondary mb-4">Start building your quiz by adding some interesting questions.</p>
                    <a href="add_questions.php?quiz_id=<?= $quiz_id ?>" class="btn btn-primary rounded-pill px-5">
                        <i class="fas fa-plus me-2"></i>Add First Question
                    </a>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>
<script>
function confirmDelete(qId, cardId) {
    Swal.fire({
        title: 'Delete Question?',
        text: "You want to remove this question!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, Delete it!',
        showClass: { popup: 'animate__animated animate__zoomIn' },
        hideClass: { popup: 'animate__animated animate__zoomOut' }
    }).then((result) => {
        if (result.isConfirmed) {
            
            // ૧. કાર્ડને એનિમેશન સાથે છુપાવો
            $(`#${cardId}`).addClass('animate__animated animate__fadeOutRight');
            setTimeout(() => { $(`#${cardId}`).hide(); }, 500);
            
            // ૨. UNDO માટે ટોસ્ટ
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
                title: 'Question deleted'
            }).then((undoResult) => {
                if (undoResult.isConfirmed) {
                    // UNDO લોજિક
                    $(`#${cardId}`).show().removeClass('animate__animated animate__fadeOutRight').addClass('animate__animated animate__fadeInLeft');
                } else {
                    deleteFromDatabase(qId, cardId);
                }
            });
        }
    });
}
function deleteFromDatabase(qId, cardId) {
    $.ajax({
        url: 'delete_question_ajax.php',
        type: 'POST',
        data: { id: qId },
        success: function(response) {
            // ડેટાબેઝમાંથી ડિલીટ થયા પછી DOM માંથી કાર્ડને કાયમ માટે કાઢી નાખો
            $(`#${cardId}`).remove();
            
            // જો પ્રશ્નોના નંબર (Q1, Q2) અપડેટ કરવા હોય તો:
            updateQuestionNumbers();
        }
    });
}

// પ્રશ્નોના નંબર ફરીથી સેટ કરવાનું ફંક્શન
function updateQuestionNumbers() {
    let count = 1;
    $('.q-card:visible').each(function() {
        $(this).find('.badge.bg-primary').text('Question ' + count++);
    });
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>