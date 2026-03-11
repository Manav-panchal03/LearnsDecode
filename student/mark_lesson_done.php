<?php
session_start();
require '../config/config.php';

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['lesson_id'])){
    $lesson_id = mysqli_real_escape_string($conn, $_POST['lesson_id']);
    $enrollment_id = mysqli_real_escape_string($conn, $_POST['enrollment_id']);

    // Check karo ke progress record pehla thi che ke nahi
    $check = mysqli_query($conn, "SELECT id FROM lesson_progress WHERE enrollment_id = '$enrollment_id' AND lesson_id = '$lesson_id'");
    
    if(mysqli_num_rows($check) > 0){
        // Jo record hoy to update karo
        $query = "UPDATE lesson_progress SET is_completed = 1, completed_at = NOW() WHERE enrollment_id = '$enrollment_id' AND lesson_id = '$lesson_id'";
    } else {
        // Navo record insert karo
        $query = "INSERT INTO lesson_progress (enrollment_id, lesson_id, is_completed, completed_at) VALUES ('$enrollment_id', '$lesson_id', 1, NOW())";
    }

    if(mysqli_query($conn, $query)){
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
    }
}
?>