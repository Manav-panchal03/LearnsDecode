<?php include 'includes/login_header.php'; ?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-lg p-4 animate__animated animate__zoomIn">
                <div class="card-body">
                    <h3 class="text-center fw-bold mb-4" style="color: #444;">Welcome Back</h3>
                    
                    <form action="<?= defined('BASE_URL') ? BASE_URL : '' ?>/login_logic.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-medium">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-medium">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary w-100 py-3 fw-bold">
                            Login Now
                        </button>
                    </form>
                    
                    <div class="text-center mt-4">
                        <p class="small text-muted mb-0">New to LearnsDecode?</p>
                        <a href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/register.php" class="text-decoration-none fw-bold" style="color: #764ba2;">Create an account</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// --- SWEETALERT LOGIN FEEDBACK ---
const urlParams = new URLSearchParams(window.location.search);

// 1. Success Message
if(urlParams.has('registered')) {
    Swal.fire({
        icon: 'success',
        title: 'Account Created!',
        text: 'You can now login with your credentials.',
        confirmButtonColor: '#667eea'
    });
}

// 2. Error Message (Invalid Credentials)
if(urlParams.has('error')) {
    let msg = "Invalid email or password!";
    if(urlParams.get('error') == 'pending') msg = "Your account is pending approval!";
    
    Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: msg,
        confirmButtonColor: '#764ba2'
    });
}

// 3. Logout Message
if(urlParams.has('logout')) {
    Swal.fire({
        icon: 'info',
        title: 'Logged Out',
        text: 'You have been successfully logged out.',
        timer: 2000,
        showConfirmButton: false
    });
}
</script>

</body>
</html>