<?php
session_start();
require_once '../config/config.php'; // Path tamara folder mujab check kari lejo

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Session expired. Please login again.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic Data Sanitization
    $user_id   = (int)$_SESSION['user_id'];
    $course_id = (int)$_POST['course_id'];
    $rating    = (int)$_POST['rating'];
    $comment   = mysqli_real_escape_string($conn, $_POST['comment']);

    // 1. Check if the student is actually enrolled in this course
    $enroll_check = mysqli_query($conn, "SELECT id FROM enrollments WHERE student_id = '$user_id' AND course_id = '$course_id' AND status = 'active'");
    
    if (mysqli_num_rows($enroll_check) === 0) {
        echo json_encode(['status' => 'error', 'message' => 'You are not enrolled in this course!']);
        exit();
    }

    // 2. Check if the student has already submitted a review for this course
    $duplicate_check = mysqli_query($conn, "SELECT id FROM reviews WHERE user_id = '$user_id' AND course_id = '$course_id'");
    
    if (mysqli_num_rows($duplicate_check) > 0) {
        echo json_encode(['status' => 'error', 'message' => 'You have already submitted a review for this course.']);
        exit();
    }

    // 3. Insert the review into the database
    $insert_query = "INSERT INTO reviews (course_id, user_id, rating, comment, created_at) 
                     VALUES ('$course_id', '$user_id', '$rating', '$comment', NOW())";

    if (mysqli_query($conn, $insert_query)) {
        echo json_encode(['status' => 'success', 'message' => 'Review submitted successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . mysqli_error($conn)]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>