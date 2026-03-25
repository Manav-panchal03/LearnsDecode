<?php
// simple admin profile page (edit name/email)
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/session_utils.php';

// activate admin role
activateRole('admin');

if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin'){
    header('Location: login.php');
    exit();
}

$admin_id = $_SESSION['user_id'];

if(isset($_POST['save'])){
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    $update_password_sql = '';
    $errors = [];

    if(!empty($_POST['current_password']) || !empty($_POST['new_password']) || !empty($_POST['confirm_password'])){
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if(empty($current_password) || empty($new_password) || empty($confirm_password)){
            $errors[] = 'Please fill all password fields to reset password.';
        } elseif($new_password !== $confirm_password){
            $errors[] = 'New password and confirm password do not match.';
        } else {
            $existing_user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT password FROM users WHERE id=$admin_id"));
            if(!$existing_user || !password_verify($current_password, $existing_user['password'])){
                $errors[] = 'Current password is incorrect.';
            } else {
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $update_password_sql = ", password='$hashed'";
            }
        }
    }

    if(empty($errors)){
        $sql = "UPDATE users SET name='$name', email='$email'$update_password_sql WHERE id=$admin_id";
        mysqli_query($conn, $sql);
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        $message = 'Profile updated successfully!';
    } else {
        $error_message = implode('<br>', $errors);
    }
}

$admin = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name,email FROM users WHERE id=$admin_id"));
?>

<?php include 'includes/header.php'; ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    .profile-card {
        border: none;
        border-radius: 20px;
        overflow: hidden;
        transition: transform 0.3s ease;
    }
    .profile-card:hover {
        transform: translateY(-5px);
    }
    .form-control {
        border-radius: 10px;
        padding: 12px;
        border: 1px solid #e0e0e0;
        transition: all 0.3s;
    }
    .form-control:focus {
        box-shadow: 0 0 0 0.25 red rgba(13, 110, 253, 0.1);
        border-color: #0d6efd;
    }
    .btn-save {
        border-radius: 10px;
        padding: 12px;
        font-weight: 600;
        letter-spacing: 0.5px;
        transition: all 0.3s;
    }
    .profile-icon-wrapper {
        width: 80px;
        height: 80px;
        background: #f8f9fa;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        color: #0d6efd;
        font-size: 2rem;
        border: 2px solid #e9ecef;
    }
</style>

<div class="container-fluid p-4">
    <div class="row justify-content-center align-items-center" style="min-height: 70vh;">
        <div class="col-md-5 col-lg-4">
            <div class="card profile-card shadow-lg animate__animated animate__zoomIn">
                <div class="card-header bg-primary text-white text-center py-4 border-0">
                    <h5 class="mb-0 fw-bold">Admin Settings</h5>
                </div>
                <div class="card-body p-4">
                    <div class="profile-icon-wrapper animate__animated animate__bounceIn animate__delay-1s">
                        <i class="fas fa-user-shield"></i>
                    </div>

                    <?php if(isset($message)): ?>
                        <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeInDown" role="alert">
                            <i class="fas fa-check-circle me-2"></i> <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="post">
                        <div class="mb-3 animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
                            <label for="name" class="form-label text-muted small fw-bold uppercase">Full Name</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="fas fa-user text-primary"></i></span>
                                <input type="text" name="name" id="name" class="form-control bg-light" value="<?= htmlspecialchars($admin['name']) ?>" required>
                            </div>
                        </div>

                        <div class="mb-4 animate__animated animate__fadeInUp" style="animation-delay: 0.3s;">
                            <label for="email" class="form-label text-muted small fw-bold">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="fas fa-envelope text-primary"></i></span>
                                <input type="email" name="email" id="email" class="form-control bg-light" value="<?= htmlspecialchars($admin['email']) ?>" required>
                            </div>
                        </div>

                        <div class="d-grid animate__animated animate__fadeInUp" style="animation-delay: 0.4s;">
                            <button type="submit" name="save" class="btn btn-primary btn-save shadow-sm">
                                <i class="fas fa-sync-alt me-2"></i>Update Profile
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-footer bg-white border-0 text-center pb-4">
                    <a href="dashboard.php" class="text-decoration-none text-muted small">
                        <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>