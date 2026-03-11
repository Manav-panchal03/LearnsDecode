<?php
session_start();
require '../config/config.php';

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])){
    $user_id = $_SESSION['user_id'];
    $quiz_id = (int)$_POST['quiz_id'];
    $student_answers = $_POST['answers'] ?? []; // Empty array check

    // 1. Student nu enrollment id fetch karvu
    $enroll_q = mysqli_query($conn, "
        SELECT e.id FROM enrollments e
        JOIN quizzes q ON e.course_id = q.course_id
        WHERE e.student_id = '$user_id' AND q.id = '$quiz_id'
        LIMIT 1
    ");
    
    $enroll = mysqli_fetch_assoc($enroll_q);
    
    if(!$enroll){
        die("Tame aa course ma enrolled nathi!");
    }
    
    $enrollment_id = $enroll['id'];
    $total_score = 0;

    // 2. Marks calculate karva (Main logic)
    foreach($student_answers as $q_id => $opt_id){
        $q_id = (int)$q_id;
        $opt_id = (int)$opt_id;

        // Sacho option check karvo
        $check_q = mysqli_query($conn, "SELECT is_correct FROM quiz_options WHERE id = '$opt_id' AND question_id = '$q_id'");
        $res = mysqli_fetch_assoc($check_q);
        
        if(isset($res['is_correct']) && $res['is_correct'] == 1){
            // Marks fetch karva
            $marks_q = mysqli_query($conn, "SELECT marks FROM quiz_questions WHERE id = '$q_id'");
            $m = mysqli_fetch_assoc($marks_q);
            $total_score += ($m['marks'] ?? 0);
        }
    }

    // 3. Quiz Attempt save karvu
    $insert_query = "INSERT INTO quiz_attempts (enrollment_id, quiz_id, score) VALUES ('$enrollment_id', '$quiz_id', '$total_score')";
    
    if(mysqli_query($conn, $insert_query)){
        // Have nava attempt ni ID levani
        $attempt_id = mysqli_insert_id($conn);

        // 4. Darek response (selected option) save karvo
        foreach($student_answers as $q_id => $opt_id){
            $q_id = (int)$q_id;
            $opt_id = (int)$opt_id;
            
            // Aa query student na selected answers save karshe
            mysqli_query($conn, "INSERT INTO quiz_responses (attempt_id, question_id, selected_option_id) VALUES ('$attempt_id', '$q_id', '$opt_id')");
        }

        header("Location: result.php?quiz_id=$quiz_id&score=$total_score");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
} else {
    header("Location: inbox.php");
    exit();
}