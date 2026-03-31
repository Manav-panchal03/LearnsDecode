<?php
require 'config/config.php';

function redirect_with_alert($message, $location = 'register.php') {
    echo "<script>
            alert('" . addslashes($message) . "');
            window.location='" . $location . "';
          </script>";
    exit();
}

if(isset($_POST['register'])){
    $name = trim(mysqli_real_escape_string($conn, $_POST['name'] ?? ''));
    $email = trim(mysqli_real_escape_string($conn, $_POST['email'] ?? ''));
    $raw_password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'student';

    if($name === '' || $email === '' || $raw_password === '') {
        redirect_with_alert('Please fill in all required fields.');
    }

    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirect_with_alert('Please enter a valid email address.');
    }

    $allowed_roles = ['student', 'instructor'];
    if(!in_array($role, $allowed_roles, true)) {
        $role = 'student';
    }

    $password_regex = '/^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*]).{8,}$/';
    if(!preg_match($password_regex, $raw_password)){
        redirect_with_alert('Password must be at least 8 characters long and include one uppercase letter, one number, and one special character.');
    }

    if(strlen($raw_password) > 72) {
        redirect_with_alert('Password must not exceed 72 characters.');
    }

    $password = password_hash($raw_password, PASSWORD_BCRYPT);// Secure hashing
    
    // Check if email already exists
    $check_email = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $check_email);

    if(mysqli_num_rows($result) > 0){
        echo "<script>alert('Email already exists!'); window.location='register.php';</script>";
    } else {
        // Handle instructor registration differently
        if($role == 'instructor'){
            $approved = 0; // Instructor needs approval
            $query = "INSERT INTO users (name, email, password, role, approved) VALUES ('$name', '$email', '$password', '$role', '$approved')";
            
            if(mysqli_query($conn, $query)){
                $user_id = mysqli_insert_id($conn);
                
                // Create instructor request record
                $request_reason = isset($_POST['request_reason']) ? mysqli_real_escape_string($conn, $_POST['request_reason']) : 'Instructor access requested';
                $request_query = "INSERT INTO instructor_requests (user_id, request_reason) VALUES ('$user_id', '$request_reason')";
                mysqli_query($conn, $request_query);
                
                echo "<script>alert('Instructor registration request submitted! Please wait for approval from administrators.'); window.location='login.php';</script>";
            } else {
                echo "Error: " . mysqli_error($conn);
            }
        } else {
            $approved = 1; // Students are approved by default
            $query = "INSERT INTO users (name, email, password, role, approved) VALUES ('$name', '$email', '$password', '$role', '$approved')";
            if(mysqli_query($conn, $query)){
                echo "<script>alert('Registration Successful!'); window.location='login.php';</script>";
            } else {
                echo "Error: " . mysqli_error($conn);
            }
        }
    }
}
?>