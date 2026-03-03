<?php
session_start();
require '../config/config.php';

$quiz_id = $_GET['quiz_id'];

// ક્વિઝ વિગતો
$quiz_res = mysqli_query($conn, "SELECT q.*, c.title as course_title FROM quizzes q JOIN courses c ON q.course_id = c.id WHERE q.id = $quiz_id");
$quiz = mysqli_fetch_assoc($quiz_res);

// પ્રશ્નો મેળવો
$questions_res = mysqli_query($conn, "SELECT * FROM quiz_questions WHERE quiz_id = $quiz_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Review Quiz | LearnsDecode</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f7f6; color: #2d3436; }
        
        .header-section {
            background: white;
            padding: 20px 0;
            border-bottom: 1px solid #e0e0e0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .q-card {
            background: white;
            border-radius: 16px;
            border: none;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            overflow: hidden;
        }

        .q-header {
            background: #f8faff;
            padding: 15px 25px;
            border-bottom: 1px solid #edf2f7;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .q-body { padding: 25px; }

        .options-container {
            display: flex;
            flex-direction: column; /* આનાથી લાઇન બગડશે નહીં */
            gap: 12px;
            margin-top: 20px;
        }

        .option-item {
            background: #ffffff;
            border: 1.5px solid #edf2f7;
            border-radius: 12px;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            font-size: 0.95rem;
            transition: 0.2s;
            width: 100%; /* ફૂલ વિડ્થ */
        }

        .option-item.correct {
            background: #f0fff4;
            border-color: #38a169;
            color: #2f855a;
            font-weight: 500;
        }

        .correct-badge {
            background: #38a169;
            color: white;
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 0.75rem;
            margin-left: auto;
        }

        .action-btn {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: 0.3s;
            text-decoration: none !important;
        }

        .btn-edit { background: #ebf4ff; color: #3182ce; }
        .btn-delete { background: #fff5f5; color: #e53e3e; }
        .btn-edit:hover { background: #3182ce; color: white; }
        .btn-delete:hover { background: #e53e3e; color: white; }

        .marks-tag {
            font-size: 0.8rem;
            background: #edf2f7;
            padding: 4px 12px;
            border-radius: 50px;
            color: #4a5568;
        }
    </style>
</head>
<body>

<div class="header-section shadow-sm">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <p class="text-muted small mb-1">Reviewing Quiz</p>
                <h4 class="fw-bold mb-0 text-dark"><?= $quiz['title'] ?></h4>
            </div>
            <div class="d-flex gap-3">
                <a href="add_questions.php?quiz_id=<?= $quiz_id ?>" class="btn btn-outline-primary rounded-pill px-4 fw-500">
                    <i class="fas fa-plus me-2"></i>Add Questions
                </a>
                <button class="btn btn-success rounded-pill px-4 shadow-sm fw-500" onclick="alert('Publishing...')">
                    <i class="fas fa-rocket me-2"></i>Publish Quiz
                </button>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            <?php 
            $q_num = 1;
            while($q = mysqli_fetch_assoc($questions_res)): 
                $q_id = $q['id'];
                // ઓપ્શન્સ ફેચ કરવાની ક્વેરી
                $opt_res = mysqli_query($conn, "SELECT * FROM quiz_options WHERE question_id = $q_id");
            ?>
            
            <div class="card q-card animate__animated animate__fadeInUp mb-4">
                
                <div class="q-header d-flex justify-content-between align-items-center p-3 border-bottom bg-light" style="border-radius: 16px 16px 0 0;">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary rounded-pill">Q<?= $q_num++ ?></span>
                        <span class="marks-tag px-2 py-1 bg-white border rounded-pill small">Marks: <?= $q['marks'] ?></span>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="edit_question.php?id=<?= $q_id ?>" class="action-btn btn-edit btn btn-sm btn-light"><i class="fas fa-pen text-primary"></i></a>
                        <a href="delete_question.php?id=<?= $q_id ?>&quiz_id=<?= $quiz_id ?>" class="action-btn btn-delete btn btn-sm btn-light" onclick="return confirm('Delete this question?')"><i class="fas fa-trash text-danger"></i></a>
                    </div>
                </div>

                <div class="q-body p-4">
                    <h5 class="fw-bold mb-4 text-dark"><?= nl2br(htmlspecialchars($q['question_text'])) ?></h5>
                    
                    <div class="options-list d-flex flex-column gap-2">
                        <?php while($opt = mysqli_fetch_assoc($opt_res)): ?>
                            <div class="option-item p-3 border rounded-3 d-flex align-items-center <?= $opt['is_correct'] ? 'correct-style' : '' ?>" 
                                style="<?= $opt['is_correct'] ? 'background-color: #f0fff4; border-color: #38a169;' : 'background-color: #fff;' ?>">
                                
                                <i class="fas <?= $opt['is_correct'] ? 'fa-check-circle text-success' : 'fa-circle text-muted' ?> me-3"></i>
                                <span class="<?= $opt['is_correct'] ? 'fw-bold text-success' : '' ?>">
                                    <?= htmlspecialchars($opt['option_text']) ?>
                                </span>
                                
                                <?php if($opt['is_correct']): ?>
                                    <span class="badge bg-success ms-auto">Correct Answer</span>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div> <?php endwhile; ?>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>