<?php
require '../config/config.php';
header('Content-Type: application/json');

if (isset($_POST['publish_course'])) {
    $course_id = mysqli_real_escape_string($conn, $_POST['course_id']);
    
    // finalize_course_ajax.php ma aa line badlo:
    $sql = "UPDATE courses SET status = 'published' WHERE id = '$course_id'";    
    if (mysqli_query($conn, $sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}