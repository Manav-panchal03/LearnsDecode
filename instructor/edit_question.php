<?php
session_start();
require '../config/config.php';

if (!isset($_GET['id'])) {
    header("Location: manage_quizzes.php");
    exit();
}

$q_id = $_GET['id'];

// પ્રશ્ન અને તેના ઓપ્શન્સ ફેચ કરો
$q_res = mysqli_query($conn, "SELECT * FROM quiz_questions WHERE id = $q_id");
$question = mysqli_fetch_assoc($q_res);
$quiz_id = $question['quiz_id'];

$opt_res = mysqli_query($conn, "SELECT * FROM quiz_options WHERE question_id = $q_id");
$options = [];
while ($row = mysqli_fetch_assoc($opt_res)) {
    $options[] = $row;
}

// Update Logic
if (isset($_POST['update_question'])) {
    $q_text = mysqli_real_escape_string($conn, $_POST['question_text']);
    $marks = $_POST['marks'];
    $correct_opt_id = $_POST['correct_option']; // જે ઓપ્શન રેડિયો બટનથી સિલેક્ટ કર્યો હોય તેની ID

    // Transaction શરૂ કરો
    mysqli_begin_transaction($conn);

    try {
        // 1. પ્રશ્ન અપડેટ કરો
        mysqli_query($conn, "UPDATE quiz_questions SET question_text = '$q_text', marks = $marks WHERE id = $q_id");

        // 2. ઓપ્શન્સ અપડેટ કરો
        foreach ($_POST['option_text'] as $opt_id => $opt_text) {
            $opt_text = mysqli_real_escape_string($conn, $opt_text);
            $is_correct = ($opt_id == $correct_opt_id) ? 1 : 0;
            
            mysqli_query($conn, "UPDATE quiz_options SET option_text = '$opt_text', is_correct = $is_correct WHERE id = $opt_id");
        }

        mysqli_commit($conn);
        echo "<script>alert('Question Updated Successfully!'); window.location.href='review_quiz.php?quiz_id=$quiz_id';</script>";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "<script>alert('Error updating question!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Question | LearnsDecode</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f0f2f5; color: #1a202c; }
        .edit-card {
            background: white;
            border-radius: 24px;
            border: none;
            box-shadow: 0 15px 35px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        .gradient-header {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            padding: 40px;
            color: white;
            text-align: center;
        }
        .form-control {
            border-radius: 12px;
            padding: 12px 15px;
            border: 2px solid #edf2f7;
            transition: 0.3s;
        }
        .form-control:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }
        .option-wrapper {
            background: #f8fafc;
            padding: 20px;
            border-radius: 16px;
            margin-bottom: 15px;
            border: 2px solid transparent;
            transition: 0.3s;
        }
        .option-wrapper:hover { border-color: #e2e8f0; }
        .option-wrapper.correct-selected {
            background: #f0fff4;
            border-color: #38a169;
        }
        .btn-update {
            background: linear-gradient(135deg, #38a169 0%, #2f855a 100%);
            border: none;
            padding: 15px;
            border-radius: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: 0.4s;
        }
        .btn-update:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(56, 161, 105, 0.3); }
        
        /* Radio Button Styling */
        .form-check-input:checked { background-color: #38a169; border-color: #38a169; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card edit-card animate__animated animate__zoomIn">
                <div class="gradient-header">
                    <i class="fas fa-edit fa-3x mb-3 animate__animated animate__bounceInDown"></i>
                    <h2 class="fw-bold">Edit Question</h2>
                    <p class="mb-0 opacity-75">Update your question and options below</p>
                </div>
                
                <form method="POST" class="p-4 p-md-5">
                    <div class="mb-4">
                        <label class="form-label fw-bold text-secondary">Question Statement</label>
                        <textarea name="question_text" class="form-control" rows="3" required><?= htmlspecialchars($question['question_text']) ?></textarea>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary">Marks Allocation</label>
                            <input type="number" name="marks" min="0" class="form-control" value="<?= $question['marks'] ?>" required>
                        </div>
                    </div>

                    <hr class="my-4 opacity-25">
                    <h5 class="fw-bold mb-4"><i class="fas fa-list-ul me-2 text-primary"></i>Options & Answers</h5>

                    <div class="options-container">
                        <?php foreach($options as $index => $opt): ?>
                            <div class="option-wrapper d-flex align-items-center gap-3 <?= $opt['is_correct'] ? 'correct-selected' : '' ?>">
                                <div class="form-check">
                                    <input class="form-check-input radio-correct" type="radio" name="correct_option" 
                                        value="<?= $opt['id'] ?>" id="radio<?= $opt['id'] ?>" 
                                        <?= $opt['is_correct'] ? 'checked' : '' ?> required>
                                </div>
                                <div class="flex-grow-1">
                                    <input type="text" name="option_text[<?= $opt['id'] ?>]" class="form-control" 
                                        value="<?= htmlspecialchars($opt['option_text']) ?>" required>
                                </div>
                                <?php if($opt['is_correct']): ?>
                                    <span class="badge bg-success rounded-pill px-3 py-2">Correct</span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="d-grid mt-5">
                        <button type="submit" name="update_question" class="btn btn-success btn-update text-white">
                            <i class="fas fa-save me-2"></i> Save Changes
                        </button>
                        <a href="review_quiz.php?quiz_id=<?= $quiz_id ?>" class="btn btn-link text-secondary mt-2">Cancel and Go Back</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Radio બટન ક્લિક થાય ત્યારે બેકગ્રાઉન્ડ કલર બદલવા માટે
    document.querySelectorAll('.radio-correct').forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.option-wrapper').forEach(wrapper => {
                wrapper.classList.remove('correct-selected');
            });
            if(this.checked) {
                this.closest('.option-wrapper').classList.add('correct-selected');
            }
        });
    });
</script>

</body>
</html>