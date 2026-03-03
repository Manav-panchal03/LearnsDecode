<?php
require 'config/config.php';

if(isset($_POST['register'])){
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $raw_password = $_POST['password'];
    $role = $_POST['role'];

    // --- PRO-TIP: STRONG PASSWORD REGEX ---
    // Explaining the Regex:
    // (?=.*[A-Z]) -> At least one uppercase
    // (?=.*[0-9]) -> At least one number
    // (?=.*[!@#$%^&*]) -> At least one special char
    // {8,} -> Minimum 8 characters
    $password_regex = "/^(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*])(?=.{8,})/";
    if(!preg_match($password_regex, $raw_password)){
        echo "<script>
                alert('Password selection criteria: \\n- Min 8 characters \\n- One Uppercase letter \\n- One Number \\n- One Special Character'); 
                window.history.back();
            </script>";
        exit();
    }
    $password = password_hash($raw_password, PASSWORD_BCRYPT);// Secure hashing
    
    // Check if email already exists
    $check_email = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $check_email);

    if(mysqli_num_rows($result) > 0){
        echo "<script>alert('Email already exists!'); window.location='register.php';</script>";
    } else {
        $query = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$password', '$role')";
        if(mysqli_query($conn, $query)){
            echo "<script>alert('Registration Successful!'); window.location='login.php';</script>";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}
?>