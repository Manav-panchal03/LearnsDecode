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
            
            // Check if instructor is approved
            if($user['role'] == 'instructor' && $user['approved'] == 0){
                echo "<script>alert('Your instructor account is pending approval. Please contact administrators.'); window.history.back();</script>";
                exit();
            }
            
            // Start session if not already started
            if(session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            // do not wipe entire session, just handle namespaced data
            $role = $user['role'];

            // initialize role list if missing
            if(!isset($_SESSION['roles']) || !is_array($_SESSION['roles'])){
                $_SESSION['roles'] = [];
            }
            // store user info under role key
            $_SESSION[$role] = [
                'user_id' => $user['id'],
                'user_name' => $user['name'],
                'user_email' => $user['email'],
                'user_role' => $role,
                'user_approved' => $user['approved'],
                'login_time' => time()
            ];

            // add to role list if not already there
            if(!in_array($role, $_SESSION['roles'])){
                $_SESSION['roles'][] = $role;
            }

            // update globals to represent currently active role
            $_SESSION['user_id']      = $user['id'];
            $_SESSION['user_name']    = $user['name'];
            $_SESSION['user_email']   = $user['email'];
            $_SESSION['user_role']    = $role;
            $_SESSION['user_approved']= $user['approved'];
            $_SESSION['login_time']   = time();
            
            // Role wise redirect karo
            $base = defined('BASE_URL') ? BASE_URL : '';
            if($user['role'] == 'admin'){
                header("Location: {$base}/admin/dashboard.php");
            } elseif($user['role'] == 'instructor'){
                header("Location: {$base}/instructor/dashboard.php");
            } else {
                header("Location: {$base}/index.php");
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