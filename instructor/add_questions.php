<?php
session_start();
require '../config/config.php';

// Security Check
if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'instructor'){
    header("Location: ../login.php");
    exit();
}

// Quiz ID મેળવો અને સુરક્ષિત કરો
$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : null;

if (!$quiz_id) {
    header("Location: manage_quizzes.php");
    exit();
}

// ક્વિઝનું ટાઇટલ ફેચ કરવા માટે
$quiz_info = mysqli_query($conn, "SELECT title FROM quizzes WHERE id = $quiz_id");
$quiz_data = mysqli_fetch_assoc($quiz_info);

// પ્રશ્ન સેવ કરવાનું લોજિક
if ($_SERVER['REQUEST_METHOD'] == 'POST' && (isset($_POST['save_question']) || isset($_POST['finish_quiz']))) {
    
    $question_text = mysqli_real_escape_string($conn, $_POST['question_text']);
    $marks = intval($_POST['marks']);
    $correct_opt_idx = intval($_POST['correct_opt']); // ૧ થી ૪ વચ્ચેની વેલ્યુ

    // ૧. પહેલા quiz_questions માં ઇન્સર્ટ કરો
    $q_query = "INSERT INTO quiz_questions (quiz_id, question_text, marks) VALUES ($quiz_id, '$question_text', $marks)";
    
    if (mysqli_query($conn, $q_query)) {
        // ૨. આ જ પ્રશ્નનું તાજું ID મેળવો
        $new_question_id = mysqli_insert_id($conn); 

        // ૩. ચારેય ઓપ્શન્સને લૂપ ફેરવીને ઇન્સર્ટ કરો
        for ($i = 1; $i <= 4; $i++) {
            $opt_field = "opt" . $i;
            $option_text = mysqli_real_escape_string($conn, $_POST[$opt_field]);
            $is_correct = ($i === $correct_opt_idx) ? 1 : 0;

            $opt_query = "INSERT INTO quiz_options (question_id, option_text, is_correct) 
                          VALUES ($new_question_id, '$option_text', $is_correct)";
            mysqli_query($conn, $opt_query);
        }

        // જો યુઝરે 'Finish' બટન દબાવ્યું હોય
        if (isset($_POST['finish_quiz'])) {
            header("Location: manage_quizzes.php?msg=quiz_updated");
            exit();
        }
        $success = true;
    }
}

// પ્રશ્નોની ગણતરી માટે
$count_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM quiz_questions WHERE quiz_id = $quiz_id");
$count_data = mysqli_fetch_assoc($count_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Questions | LearnsDecode</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <style>
        :root { --primary-color: #6c63ff; --success-color: #2ed573; }
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; }
        .question-card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); background: #fff; }
        .form-label { font-weight: 600; font-size: 0.9rem; color: #444; }
        .option-input { border-left: 4px solid #e1e1e1; transition: 0.3s; }
        .option-input:focus { border-left: 4px solid var(--primary-color); box-shadow: none; }
        .correct-select { background-color: #f8fff9; border: 1px solid var(--success-color); }
        .btn-add { background: var(--primary-color); border: none; border-radius: 10px; padding: 12px; font-weight: 600; color: white; transition: 0.3s; }
        .btn-add:hover { background: #5a52e0; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(108, 99, 255, 0.3); }
        .btn-finish { border-radius: 10px; padding: 12px; font-weight: 600; }
        .badge-count { background: #eef2ff; color: var(--primary-color); border: 1px solid #e0e7ff; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            
            <div class="d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeIn">
                <div>
                    <h4 class="fw-bold mb-0">Quiz: <span class="text-primary"><?= htmlspecialchars($quiz_data['title']) ?></span></h4>
                    <span class="badge badge-count rounded-pill px-3 mt-2">Questions Added: <?= $count_data['total'] ?></span>
                </div>
                <a href="review_quiz.php?quiz_id=<?= $quiz_id ?>" 
                    class="btn btn-outline-primary btn-sm rounded-pill px-4 animate__animated animate__fadeInRight" 
                    style="transition: 0.3s; font-weight: 500;">
                    <i class="fas fa-eye me-1"></i> Review Questions
                </a>
            </div>

            <?php if(isset($success)): ?>
                <div class="alert alert-success border-0 shadow-sm rounded-4 animate__animated animate__backInRight">
                    <i class="fas fa-check-circle me-2"></i> Question saved! You can add another one below.
                </div>
            <?php endif; ?>

            <div class="card question-card p-4 animate__animated animate__fadeInUp">
                <form method="POST">
                    <div class="mb-4">
                        <label class="form-label">Question Text</label>
                        <textarea name="question_text" class="form-control" rows="3" placeholder="Enter your question here..." required></textarea>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Option 1</label>
                            <input type="text" name="opt1" class="form-control option-input" placeholder="Enter option 1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Option 2</label>
                            <input type="text" name="opt2" class="form-control option-input" placeholder="Enter option 2" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Option 3</label>
                            <input type="text" name="opt3" class="form-control option-input" placeholder="Enter option 3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Option 4</label>
                            <input type="text" name="opt4" class="form-control option-input" placeholder="Enter option 4" required>
                        </div>
                    </div>

                    <div class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label text-success">Select Correct Answer</label>
                            <select name="correct_opt" class="form-select correct-select" required>
                                <option value="1">Option 1</option>
                                <option value="2">Option 2</option>
                                <option value="3">Option 3</option>
                                <option value="4">Option 4</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Marks</label>
                            <input type="number" min="1" name="marks" class="form-control" value="1" required>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" name="save_question" class="btn-add w-100">
                                <i class="fas fa-plus me-2"></i>Add Next
                            </button>
                        </div>
                    </div>

                    <hr class="my-4" style="opacity: 0.1;">
                    
                    <div class="text-center">
                        <p class="small text-muted mb-3">Done adding questions?</p>
                        <button type="submit" name="finish_quiz" class="btn btn-dark btn-finish px-5 shadow-sm">
                            <i class="fas fa-check-double me-2"></i>Finish & Save Quiz
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>