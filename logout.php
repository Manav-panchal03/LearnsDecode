<?php
// Start session
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/config.php';

// determine current active role (stored globally)
$role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;

if($role){
    // remove namespaced data
    unset($_SESSION[$role]);

    // remove from roles list if present
    if(isset($_SESSION['roles']) && is_array($_SESSION['roles'])){
        $idx = array_search($role, $_SESSION['roles']);
        if($idx !== false){
            array_splice($_SESSION['roles'], $idx, 1);
        }
    }
}

// clear global variables for this role
unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email'], $_SESSION['user_role'], $_SESSION['user_approved'], $_SESSION['login_time']);

// if there are still other roles logged in, switch globals to first remaining role
if(!empty($_SESSION['roles'])){
    $newRole = $_SESSION['roles'][0];
    if(isset($_SESSION[$newRole])){
        $data = $_SESSION[$newRole];
        $_SESSION['user_id']      = $data['user_id'];
        $_SESSION['user_name']    = $data['user_name'];
        $_SESSION['user_email']   = $data['user_email'];
        $_SESSION['user_role']    = $newRole;
        $_SESSION['user_approved']= $data['user_approved'];
        $_SESSION['login_time']   = $data['login_time'];
    }
}

// Don't destroy the session completely - just log out the current user
// session_destroy(); // Commented out to prevent affecting other users

// Redirect to login page
$base = defined('BASE_URL') ? BASE_URL : '';
header("Location: {$base}/login.php");
exit();
?>
