<?php include 'includes/header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-lg p-4">
                <div class="card-body">
                    <h3 class="text-center fw-bold mb-4">Welcome Back</h3>
                    <form action="login_logic.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary w-100 py-2 mt-3">Login</button>
                    </form>
                    <p class="text-center mt-4 small">New to LearnsDecode? <a href="register.php">Create an account</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>