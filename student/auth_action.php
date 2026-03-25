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

        // Keep OTP expiry in session (10 seconds validity)
        $_SESSION['delete_otp'] = $otp;
        $_SESSION['delete_otp_expires_at'] = time() + 10;

        echo $otp; // Return OTP to show on screen
    }

    // Step 2: Verify and Delete All Data
    if($_POST['action'] == 'verify_and_delete'){
        $user_otp = $_POST['otp'];

        if(!isset($_SESSION['delete_otp']) || !isset($_SESSION['delete_otp_expires_at'])){
            echo "expired";
            exit;
        }

        if(time() > $_SESSION['delete_otp_expires_at']){
            unset($_SESSION['delete_otp'], $_SESSION['delete_otp_expires_at']);
            echo "expired";
            exit;
        }

        if($_SESSION['delete_otp'] != $user_otp){
            echo "error";
            exit;
        }

        $check = mysqli_query($conn, "SELECT id FROM users WHERE id='$user_id' AND otp='$user_otp'");
        
        if(mysqli_num_rows($check) > 0){
            // Totally Data Kadhi Nakhvanu (Enrollments, Progress, then User)
            mysqli_query($conn, "DELETE FROM lesson_progress WHERE enrollment_id IN (SELECT id FROM enrollments WHERE student_id='$user_id')");
            mysqli_query($conn, "DELETE FROM enrollments WHERE student_id='$user_id'");
            mysqli_query($conn, "DELETE FROM users WHERE id='$user_id'");
            
            unset($_SESSION['delete_otp'], $_SESSION['delete_otp_expires_at']);
            session_destroy();
            echo "success";
        } else {
            echo "error";
        }
    }
}
?>