<?php
session_start();
require 'config/config.php';

// Check karo ke user login che
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $user_id = $_SESSION['user_id']; // Session mathi ID lo
    $course_id = mysqli_real_escape_string($conn, $_POST['course_id']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $txn_id = "TXN" . rand(100000, 999999); 

    // 1. Check karo ke student enrolled che? 
    // DHAYAN AAPO: Jo tamara table ma column nu nam 'student_id' hoy toh niche sudharo
    $check_query = "SELECT id FROM enrollments WHERE student_id = '$user_id' AND course_id = '$course_id'";
    $check_res = mysqli_query($conn, $check_query);

    if(!$check_res) {
        // Jo ahiya pan error ave, toh tamare database table check karvu padse
        die("Database Error: " . mysqli_error($conn));
    }

    if(mysqli_num_rows($check_res) > 0) {
        header("Location: student/dashboard.php?order=already");
        exit();
    }

    // 2. Course ni price fetch karo
    $course_q = mysqli_query($conn, "SELECT price FROM courses WHERE id = '$course_id'");
    $course_data = mysqli_fetch_assoc($course_q);
    $amount = $course_data['price'] ?? 0;

    // 3. Database ma enrollment entry karo
    // Ahiya 'student_id' use karyu che 'user_id' ni jagyae
    $query = "INSERT INTO enrollments (student_id, course_id, enrolled_at, status, payment_status, transaction_id, amount) 
              VALUES ('$user_id', '$course_id', NOW(), 'active', 'completed', '$txn_id', '$amount')";
    
    if(mysqli_query($conn, $query)){
        header("Location: student/dashboard.php?order=success");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
} else {
    header("Location: index.php");
}
?>