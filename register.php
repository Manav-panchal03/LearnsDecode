<?php include 'includes/header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-lg p-4">
                <div class="card-body">
                    <h3 class="text-center fw-bold mb-4">Create Account</h3>
                    <form action="register_logic.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" placeholder="John Doe" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="john@example.com" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Create a strong password" required>
                            <div class="form-text text-muted" style="font-size: 0.8rem;">
                                Must be 8+ chars with at least 1 Uppercase, 1 Number, and 1 Special char.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Register As</label>
                            <select name="role" class="form-select">
                                <option value="student">Student (To Learn)</option>
                                <option value="instructor">Instructor (To Teach)</option>
                            </select>
                        </div>
                        <button type="submit" name="register" class="btn btn-primary w-100 py-2 mt-3">Get Started</button>
                    </form>
                    <p class="text-center mt-4 small">Already have an account? <a href="login.php">Login here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>