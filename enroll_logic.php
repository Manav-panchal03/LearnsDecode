<?php
session_start();
require 'config/config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

if(isset($_GET['course_id'])){
    $user_id = $_SESSION['user_id'];
    $course_id = $_GET['course_id'];

    // Pehla check karo ke student e pehla thi enroll karyu che ke nahi
    $check = mysqli_query($conn, "SELECT * FROM enrollments WHERE user_id = '$user_id' AND course_id = '$course_id'");
    
    if(mysqli_num_rows($check) > 0){
        header("Location: student/dashboard.php?msg=already_enrolled");
    } else {
        // Enrollment entry insert karo
        $query = "INSERT INTO enrollments (user_id, course_id) VALUES ('$user_id', '$course_id')";
        if(mysqli_query($conn, $query)){
            header("Location: student/dashboard.php?msg=success");
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
} else {
    header("Location: index.php");
}
?>