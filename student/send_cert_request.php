<?php
session_start();
require '../config/config.php';

header('Content-Type: application/json');

if(!isset($_SESSION['user_id']) || !isset($_GET['course_id'])){
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$course_id = (int)$_GET['course_id'];

// 1. Check if already requested
$check = mysqli_query($conn, "SELECT id FROM certificate_requests WHERE student_id = '$user_id' AND course_id = '$course_id'");
if(mysqli_num_rows($check) > 0){
    echo json_encode(['status' => 'error', 'message' => 'Tame pehla j request kareli che!']);
    exit();
}

// 2. Insert Request
$insert = mysqli_query($conn, "INSERT INTO certificate_requests (student_id, course_id) VALUES ('$user_id', '$course_id')");

if($insert){
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
?>