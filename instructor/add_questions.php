<?php
session_start();
require '../config/config.php';

$quiz_id = $_GET['quiz_id']; // URL માંથી quiz_id મેળવો

if (isset($_POST['save_question'])) {
    $question_text = mysqli_real_escape_string($conn, $_POST['question_text']);
    $marks = $_POST['marks'];
    
    // 1. quiz_questions ટેબલમાં પ્રશ્ન ઉમેરો
    $q_query = "INSERT INTO quiz_questions (quiz_id, question_text, marks) VALUES ('$quiz_id', '$question_text', '$marks')";
    
    if (mysqli_query($conn, $q_query)) {
        $question_id = mysqli_insert_id($conn);
        
        // 2. quiz_options ટેબલમાં 4 ઓપ્શન્સ ઉમેરો
        $options = [
            ['text' => $_POST['opt1'], 'correct' => ($_POST['correct_opt'] == '1' ? 1 : 0)],
            ['text' => $_POST['opt2'], 'correct' => ($_POST['correct_opt'] == '2' ? 1 : 0)],
            ['text' => $_POST['opt3'], 'correct' => ($_POST['correct_opt'] == '3' ? 1 : 0)],
            ['text' => $_POST['opt4'], 'correct' => ($_POST['correct_opt'] == '4' ? 1 : 0)]
        ];

        foreach ($options as $opt) {
            $opt_text = mysqli_real_escape_string($conn, $opt['text']);
            $is_correct = $opt['correct'];
            mysqli_query($conn, "INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES ('$question_id', '$opt_text', '$is_correct')");
        }
        
        echo "<script>alert('Question added successfully!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Questions | LearnsDecode</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card shadow p-4">
                    <h4 class="fw-bold text-primary mb-4">Add Question to Quiz ID: #<?php echo $quiz_id; ?></h4>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Question Text</label>
                            <textarea name="question_text" class="form-control" rows="2" required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Option 1</label>
                                <input type="text" name="opt1" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Option 2</label>
                                <input type="text" name="opt2" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Option 3</label>
                                <input type="text" name="opt3" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Option 4</label>
                                <input type="text" name="opt4" class="form-control" required>
                            </div>
                        </div>

                        <div class="row align-items-end">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold text-success">Correct Option Number (1-4)</label>
                                <select name="correct_opt" class="form-select" required>
                                    <option value="1">Option 1</option>
                                    <option value="2">Option 2</option>
                                    <option value="3">Option 3</option>
                                    <option value="4">Option 4</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Marks</label>
                                <input type="number" name="marks" class="form-control" value="1">
                            </div>
                            <div class="col-md-3 mb-3">
                                <button type="submit" name="save_question" class="btn btn-primary w-100">Add More</button>
                            </div>
                        </div>
                    </form>
                    
                    <hr>
                    <a href="manage_quizzes.php" class="btn btn-dark w-100">Finish & View All Quizzes</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>