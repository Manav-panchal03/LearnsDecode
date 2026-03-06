<?php
session_start();
require '../config/config.php';

if (isset($_POST['quiz_id'])) {
    $quiz_id = intval($_POST['quiz_id']);

    // 1. Delete Options first (Child records)
    $del_opts = "DELETE FROM quiz_options WHERE question_id IN (SELECT id FROM quiz_questions WHERE quiz_id = $quiz_id)";
    mysqli_query($conn, $del_opts);

    // 2. Delete Questions
    $del_ques = "DELETE FROM quiz_questions WHERE quiz_id = $quiz_id";
    mysqli_query($conn, $del_ques);

    // 3. Finally delete the Quiz (Parent record)
    $del_quiz = "DELETE FROM quizzes WHERE id = $quiz_id";
    
    if (mysqli_query($conn, $del_quiz)) {
        echo "success";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>