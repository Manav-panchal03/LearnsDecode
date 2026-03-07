<?php
session_start();
require '../config/config.php';
require_once '../includes/session_utils.php';
activateRole('instructor');

// security check
if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'instructor'){
    header("Location: ../login.php"); exit();
}

if(isset($_POST['update_profile'])){
    $uid = $_SESSION['user_id'];
    $expertise = mysqli_real_escape_string($conn, $_POST['expertise']);
    $bio = mysqli_real_escape_string($conn, $_POST['bio']);
    
    // Check if profile exists
    $check_q = mysqli_query($conn, "SELECT profile_pic FROM instructor_profiles WHERE user_id = $uid");
    $exists = mysqli_fetch_assoc($check_q);
    
    $profile_pic = $exists ? $exists['profile_pic'] : 'default-avatar.png';

    // Handle Image Upload
    if(isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0){
        $ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
        $file_name = "instructor_" . $uid . "_" . time() . "." . $ext;
        $path = "../uploads/profile/";

        if(!is_dir($path)) mkdir($path, 0777, true); // Create folder if not exists

        if(move_uploaded_file($_FILES['profile_pic']['tmp_name'], $path . $file_name)){
            $profile_pic = $file_name;
        }
    }

    if($exists){
        $sql = "UPDATE instructor_profiles SET expertise='$expertise', bio='$bio', profile_pic='$profile_pic' WHERE user_id=$uid";
    } else {
        $sql = "INSERT INTO instructor_profiles (user_id, expertise, bio, profile_pic) VALUES ('$uid', '$expertise', '$bio', '$profile_pic')";
    }

    if(mysqli_query($conn, $sql)){
        header("Location: dashboard.php?status=profile_updated");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}