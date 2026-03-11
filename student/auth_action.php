<?php
session_start();
require '../config/config.php';

if(!isset($_SESSION['user_id'])) exit;
$user_id = $_SESSION['user_id'];

if(isset($_POST['action'])){
    
    // Step 1: OTP Generate karo
    if($_POST['action'] == 'send_delete_otp'){
        $otp = rand(100000, 999999);
        mysqli_query($conn, "UPDATE users SET otp='$otp' WHERE id='$user_id'");
        // Logically tme tya OTP print kari sako cho test mate:
        echo "sent"; 
    }

    // Step 2: Verify and Delete All Data
    if($_POST['action'] == 'verify_and_delete'){
        $user_otp = $_POST['otp'];
        $check = mysqli_query($conn, "SELECT id FROM users WHERE id='$user_id' AND otp='$user_otp'");
        
        if(mysqli_num_rows($check) > 0){
            // Totally Data Kadhi Nakhvanu (Enrollments, Progress, then User)
            mysqli_query($conn, "DELETE FROM lesson_progress WHERE enrollment_id IN (SELECT id FROM enrollments WHERE student_id='$user_id')");
            mysqli_query($conn, "DELETE FROM enrollments WHERE student_id='$user_id'");
            mysqli_query($conn, "DELETE FROM users WHERE id='$user_id'");
            
            session_destroy();
            echo "success";
        } else {
            echo "error";
        }
    }
}
?>

<!-- ALTER TABLE users ADD COLUMN otp VARCHAR(10) DEFAULT NULL; -->