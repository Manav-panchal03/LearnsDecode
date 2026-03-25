<?php 
ob_start();
session_start();
require '../config/config.php'; 

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_msg = "";
$error_msg = "";

// Profile Update Logic
if(isset($_POST['update_profile'])){
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);

    if($name === '' || strlen($name) < 2){
        $error_msg = 'Please enter a valid name (at least 2 characters).';
    } elseif($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)){
        $error_msg = 'Please enter a valid email address.';
    } else {
        $name_safe = mysqli_real_escape_string($conn, $name);
        $email_safe = mysqli_real_escape_string($conn, $email);

        // Email uniqueness check among other users
        $check_email = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email_safe' AND id != '$user_id' LIMIT 1");
        if(mysqli_num_rows($check_email) > 0){
            $error_msg = 'This email is already used by another account.';
        } else {
            $update = mysqli_query($conn, "UPDATE users SET name='$name_safe', email='$email_safe' WHERE id='$user_id'");
            if($update){
                $success_msg = 'Profile updated successfully!';
            } else {
                $error_msg = 'Something went wrong while updating your profile. Please try again.';
            }
        }
    }
}

// Password Change Logic
if(isset($_POST['change_password'])){
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if(strlen($new_password) < 8){
        $error_msg = 'New password must be at least 8 characters long.';
    } elseif($new_password !== $confirm_password){
        $error_msg = 'New passwords do not match!';
    } else {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $update = mysqli_query($conn, "UPDATE users SET password='$hashed' WHERE id='$user_id'");
        if($update){
            $success_msg = 'Password changed successfully!';
        } else {
            $error_msg = 'Something went wrong while changing your password.';
        }
    }
}

// User details fetch
$user_q = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'");
$user = mysqli_fetch_assoc($user_q);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - LearnsDecode</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f4f7fe; color: #2b3674; }
        .sidebar { width: 280px; height: 100vh; background: #fff; position: fixed; left: 0; top: 0; border-right: 1px solid #e9edf7; z-index: 1000; }
        .main-content { margin-left: 280px; padding: 40px; }
        .nav-link { color: #a3aed0; padding: 15px 25px; border-radius: 12px; margin: 5px 15px; font-weight: 600; transition: 0.3s; text-decoration: none; display: block; }
        .nav-link.active { background: #4318ff; color: white !important; box-shadow: 0px 10px 20px rgba(67, 24, 255, 0.2); }
        
        .profile-card { background: #fff; border-radius: 30px; border: none; box-shadow: 0px 20px 40px rgba(0,0,0,0.03); padding: 40px; }
        .avatar-circle { width: 120px; height: 120px; background: #4318ff; color: white; font-size: 3rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; box-shadow: 0px 10px 20px rgba(67, 24, 255, 0.3); }
        .form-control { border-radius: 15px; padding: 12px 20px; border: 1px solid #e9edf7; background: #f8fafc; }
        .form-control:focus { box-shadow: none; border-color: #4318ff; background: #fff; }
        .btn-update { background: #4318ff; color: white; border-radius: 15px; padding: 12px 30px; font-weight: 700; border: none; transition: 0.3s; }
        .btn-update:hover { background: #3311cc; transform: translateY(-3px); }
        .btn-delete { background: #fff5f5; color: #ff5b5b; border: 1px solid #ffebeb; border-radius: 15px; padding: 12px 30px; font-weight: 700; transition: 0.3s; }
        .btn-delete:hover { background: #ff5b5b; color: white; }

        @media (max-width: 992px) { .sidebar { display: none; } .main-content { margin-left: 0; } }
    </style>
</head>
<body>

<div class="sidebar d-flex flex-column p-3">
    <div class="text-center my-4"><h3 class="fw-bold text-primary">LearnsDecode</h3></div>
    <nav class="nav flex-column flex-grow-1">
        <a class="nav-link" href="dashboard.php"><i class="fas fa-home me-2"></i> Dashboard</a>
        <a class="nav-link" href="my_courses.php"><i class="fas fa-book-reader me-2"></i> My Courses</a>
        <a class="nav-link d-flex justify-content-between align-items-center" href="inbox.php">
            <span><i class="fas fa-envelope me-2"></i> Inbox</span>
            
        </a>
        <a class="nav-link active" href="profile.php"><i class="fas fa-user-circle me-2"></i> Profile</a>
        <a class="nav-link" href="my_quizzes.php"><i class="fas fa-question-circle me-2"></i> My Quizzes</a>
    </nav>
    <div class="p-3 border-top">
        <div class="d-flex align-items-center">
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 45px; height: 45px; font-size: 1.2rem;">
                <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
            </div>
            <div class="ms-3 small">
                <div class="fw-bold text-dark"><?= $user['name'] ?></div>
                <a href="../logout.php" class="text-danger text-decoration-none fw-bold">Logout</a>
            </div>
        </div>
    </div>
</div>

<div class="main-content">
    <header class="mb-5">
        <h2 class="fw-bold">Account Settings</h2>
        <p class="text-muted">Manage your profile and account preferences.</p>
    </header>

    <div class="row justify-content-center">
        <div class="col-lg-8" data-aos="zoom-in">
            <div class="profile-card text-center">
                <div class="avatar-circle">
                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                </div>
                
                <?php if($success_msg): ?>
                    <div class="alert alert-success border-0 rounded-pill"><?= $success_msg ?></div>
                <?php endif; ?>

                <?php if($error_msg): ?>
                    <div class="alert alert-danger border-0 rounded-pill"><?= $error_msg ?></div>
                <?php endif; ?>

                <form method="POST" class="text-start mt-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Full Name</label>
                            <input type="text" name="name" class="form-control" value="<?= $user['name'] ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Email Address</label>
                            <input type="email" name="email" class="form-control" value="<?= $user['email'] ?>" required>
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-between align-items-center">
                        <button type="submit" name="update_profile" class="btn-update shadow-sm">
                            Save Changes
                        </button>
                        <button type="button" onclick="requestDelete()" class="btn-delete">
                            Delete Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="row justify-content-center mt-4">
        <div class="col-lg-8" data-aos="zoom-in">
            <div class="profile-card text-center">
                <h5 class="fw-bold mb-3">Change Password</h5>
                
                <form method="POST" class="text-start">
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">New Password</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                    </div>

                    <div class="mt-4 text-center">
                        <button type="submit" name="change_password" class="btn-update shadow-sm">
                            Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    AOS.init({ duration: 800 });

    function requestDelete() {
        Swal.fire({
            title: 'Delete Account?',
            text: "Are you sure? This will permanently delete your learning progress!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ff5b5b',
            cancelButtonColor: '#4318ff',
            confirmButtonText: 'Yes, Send OTP'
        }).then((result) => {
            if (result.isConfirmed) {
                // Ajax thi OTP generate karo
                $.ajax({
                    url: 'auth_action.php',
                    type: 'POST',
                    data: { action: 'send_delete_otp' },
                    success: function(response) {
                        showOtpDialog(response.trim());
                    }
                });
            }
        })
    }

    function showOtpDialog(otp) {
        let secondsLeft = 10;

        Swal.fire({
            title: 'Enter OTP',
            html: `
                <div>Your OTP is: <b>${otp}</b></div>
                <div id="otp-timer" style="margin-top:12px; font-weight:bold; color:#d9534f;">Time left: ${secondsLeft}s</div>
            `,
            input: 'text',
            inputAttributes: { autocapitalize: 'off' },
            showCancelButton: true,
            confirmButtonText: 'Confirm Delete',
            confirmButtonColor: '#ff5b5b',
            showLoaderOnConfirm: true,
            didOpen: () => {
                const interval = setInterval(() => {
                    secondsLeft -= 1;
                    const timerElement = document.getElementById('otp-timer');
                    if (timerElement) {
                        timerElement.textContent = `Time left: ${secondsLeft}s`;
                    }

                    if (secondsLeft <= 0) {
                        clearInterval(interval);
                        const swalConfirmBtn = Swal.getConfirmButton();
                        if (swalConfirmBtn) swalConfirmBtn.disabled = true;
                        if (timerElement) timerElement.textContent = 'OTP expired. Please request a new OTP.';
                    }
                }, 1000);
                Swal.update({ didClose: () => clearInterval(interval) });
            },
            preConfirm: (enteredOtp) => {
                if (secondsLeft <= 0) {
                    Swal.showValidationMessage('OTP has expired. Please generate a new one.');
                    return false;
                }
                return $.ajax({
                    url: 'auth_action.php',
                    type: 'POST',
                    data: { action: 'verify_and_delete', otp: enteredOtp }
                }).then(response => {
                    if (response.trim() === 'success') {
                        return true;
                    } else if (response.trim() === 'expired') {
                        Swal.showValidationMessage('OTP has expired. Please generate a new one.');
                    } else {
                        Swal.showValidationMessage('Invalid OTP!');
                    }
                });
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire('Deleted!', 'Your account has been removed.', 'success')
                .then(() => { window.location.href = '../login.php'; });
            }
        });
    }
</script>
</body>
</html>