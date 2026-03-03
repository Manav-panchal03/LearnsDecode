<?php
session_start();
require 'config/config.php';

if(isset($_POST['login'])){
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // Database mathi user ne shodho
    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if(mysqli_num_rows($result) > 0){
        $user = mysqli_fetch_assoc($result);
        
        // Hashed password verify karo
        if(password_verify($password, $user['password'])){
            
            // --- SESSION START ---
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            // Role wise redirect karo
            if($user['role'] == 'admin'){
                header("Location: admin/dashboard.php");
            } elseif($user['role'] == 'instructor'){
                header("Location: instructor/dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit();

        } else {
            echo "<script>alert('Incorrect Password!'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('No account found with this email!'); window.history.back();</script>";
    }
}
?>