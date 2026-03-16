<?php
// Start session
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/config.php';

// --- TAMARU ORIGINAL LOGIC START ---
$role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;

if($role){
    unset($_SESSION[$role]);
    if(isset($_SESSION['roles']) && is_array($_SESSION['roles'])){
        $idx = array_search($role, $_SESSION['roles']);
        if($idx !== false){
            array_splice($_SESSION['roles'], $idx, 1);
        }
    }
}

unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email'], $_SESSION['user_role'], $_SESSION['user_approved'], $_SESSION['login_time']);

if(!empty($_SESSION['roles'])){
    $newRole = $_SESSION['roles'][0];
    if(isset($_SESSION[$newRole])){
        $data = $_SESSION[$newRole];
        $_SESSION['user_id']       = $data['user_id'];
        $_SESSION['user_name']     = $data['user_name'];
        $_SESSION['user_email']    = $data['user_email'];
        $_SESSION['user_role']     = $newRole;
        $_SESSION['user_approved'] = $data['user_approved'];
        $_SESSION['login_time']    = $data['login_time'];
    }
}
// --- TAMARU ORIGINAL LOGIC END ---

$base = defined('BASE_URL') ? BASE_URL : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging Out...</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body>

<script>
    // SweetAlert Logout Animation
    Swal.fire({
        title: 'Logging Out...',
        text: 'Signing you out safely, please wait.',
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        },
        timer: 1500, // 1.5 seconds wait karavse animation mate
        timerProgressBar: true,
        didOpen: () => {
            // Background animation zoom out effect
            document.body.classList.add('animate__animated', 'animate__fadeOut');
        },
        willClose: () => {
            // Redirect to login with logout parameter for SweetAlert on login page
            window.location.href = "<?php echo $base; ?>/login.php?logout=1";
        }
    });
</script>

</body>
</html>