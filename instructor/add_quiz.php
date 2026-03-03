<?php
session_start();
require '../config/config.php';

$instructor_id = $_SESSION['user_id'];

// ઇન્સ્ટ્રક્ટરના બધા કોર્સ ફેચ કરવા માટે
$courses_query = mysqli_query($conn, "SELECT id, title FROM courses WHERE instructor_id = $instructor_id");

if (isset($_POST['submit_quiz'])) {
    $course_id = $_POST['course_id'];
    $title = mysqli_real_escape_string($conn, $_POST['quiz_title']);
    $total_marks = $_POST['total_marks'];

    // 1. Quizzes ટેબલમાં ડેટા ઇન્સર્ટ કરો
    $sql = "INSERT INTO quizzes (course_id, title, total_marks) VALUES ('$course_id', '$title', '$total_marks')";
    
    if (mysqli_query($conn, $sql)) {
        $quiz_id = mysqli_insert_id($conn);
        header("Location: add_questions.php?quiz_id=$quiz_id"); // પ્રશ્નો એડ કરવાના પેજ પર મોકલો
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Quiz | LearnsDecode</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow border-0 p-4">
                    <h3 class="fw-bold mb-4 text-primary">Create New Quiz</h3>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Select Course</label>
                            <select name="course_id" class="form-select" required>
                                <option value="">Choose a course...</option>
                                <?php while($course = mysqli_fetch_assoc($courses_query)): ?>
                                    <option value="<?= $course['id'] ?>"><?= $course['title'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Quiz Title</label>
                            <input type="text" name="quiz_title" class="form-control" placeholder="e.g. Basic PHP Quiz" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Total Marks</label>
                            <input type="number" name="total_marks" class="form-control" required>
                        </div>
                        <button type="submit" name="submit_quiz" class="btn btn-primary w-100 py-2 fw-bold">Next: Add Questions</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>