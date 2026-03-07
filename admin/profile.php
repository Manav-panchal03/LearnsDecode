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
    mysqli_query($conn, "UPDATE users SET name='$name', email='$email' WHERE id=$admin_id");
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;
    $message = 'Profile updated.';
}

$admin = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name,email FROM users WHERE id=$admin_id"));
?>

<?php include 'includes/header.php'; ?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>My Profile</h5>
                </div>
                <div class="card-body">
                    <?php if(isset($message)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <form method="post">
                        <div class="mb-3">
                            <label for="name" class="form-label"><i class="fas fa-user me-1"></i>Full Name</label>
                            <input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($admin['name']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label"><i class="fas fa-envelope me-1"></i>Email Address</label>
                            <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($admin['email']) ?>" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" name="save" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
