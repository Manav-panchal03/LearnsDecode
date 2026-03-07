<?php
session_start();
require '../config/config.php';
require_once '../includes/session_utils.php';

// activate instructor role
activateRole('instructor');

// Security Check
if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'instructor'){
    header("Location: ../login.php");
    exit();
}

$instructor_id = $_SESSION['user_id'];

// ઇન્સ્ટ્રક્ટરના કોર્સ ફેચ કરવા માટે
$courses_query = mysqli_query($conn, "SELECT id, title FROM courses WHERE instructor_id = $instructor_id");

if (isset($_POST['submit_quiz'])) {
    $course_id = $_POST['course_id'];
    $title = mysqli_real_escape_string($conn, $_POST['quiz_title']);
    $total_marks = $_POST['total_marks'];

    $sql = "INSERT INTO quizzes (course_id, title, total_marks) VALUES ('$course_id', '$title', '$total_marks')";
    
    if (mysqli_query($conn, $sql)) {
        $quiz_id = mysqli_insert_id($conn);
        header("Location: add_questions.php?quiz_id=$quiz_id");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Quiz | LearnsDecode</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <style>
        :root {
            --primary-color: #6c63ff;
            --dark-bg: #1e1e2d;
        }
        body { 
            font-family: 'Poppins', sans-serif; 
            background-color: #f4f7f6; 
        }
        .quiz-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            background: #fff;
        }
        .form-control, .form-select {
            border-radius: 10px;
            padding: 12px;
            border: 1px solid #e1e1e1;
        }
        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(108, 99, 255, 0.1);
            border-color: var(--primary-color);
        }
        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            transition: 0.3s;
        }
        .btn-primary:hover {
            background-color: #5a52e0;
            transform: translateY(-2px);
        }
        .back-link {
            text-decoration: none;
            color: #6c757d;
            font-weight: 500;
            transition: 0.2s;
        }
        .back-link:hover { color: var(--primary-color); }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            
            <div class="mb-4 animate__animated animate__fadeInDown">
                <a href="dashboard.php" class="back-link"><i class="fas fa-arrow-left me-2"></i>Back to Dashboard</a>
            </div>

            <div class="card quiz-card p-4 animate__animated animate__zoomIn">
                <div class="text-center mb-4">
                    <div class="bg-light d-inline-block p-3 rounded-circle mb-3">
                        <i class="fas fa-file-alt text-primary fa-2x"></i>
                    </div>
                    <h3 class="fw-bold">Create New <span class="text-primary">Quiz</span></h3>
                    <p class="text-muted">Set up the basic details for your quiz</p>
                </div>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Course</label>
                        <select name="course_id" class="form-select" required>
                            <option value="">-- Choose Course --</option>
                            <?php while($course = mysqli_fetch_assoc($courses_query)): ?>
                                <option value="<?= $course['id'] ?>"><?= $course['title'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Quiz Title</label>
                        <input type="text" name="quiz_title" class="form-control" placeholder="e.g. PHP Basics Final Exam" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Total Marks</label>
                        <input type="number" name="total_marks" min="0" class="form-control" placeholder="e.g. 50" required>
                    </div>

                    <button type="submit" name="submit_quiz" class="btn btn-primary w-100 mb-3">
                        Continue to Add Questions <i class="fas fa-chevron-right ms-2"></i>
                    </button>
                </form>
            </div>

            <p class="text-center mt-4 text-muted small animate__animated animate__fadeIn">
                Step 1 of 2: Basic Information
            </p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>