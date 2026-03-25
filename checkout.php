<?php 
ob_start();
session_start();
require 'config/config.php'; 
include 'includes/header.php'; 

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$course_id = mysqli_real_escape_string($conn, $_GET['id']);
$user_id = $_SESSION['user_id'];

// Course details
$query = "SELECT * FROM courses WHERE id = '$course_id'";
$result = mysqli_query($conn, $query);
$course = mysqli_fetch_assoc($result);

// User details
$u_query = "SELECT * FROM users WHERE id = '$user_id'";
$u_result = mysqli_query($conn, $u_query);
$user = mysqli_fetch_assoc($u_result);
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

<div class="container my-5">
    <div class="row g-4">
        <div class="col-md-7" data-aos="fade-right">
            <div class="card border-0 shadow-sm p-4 rounded-4">
                <h4 class="fw-bold mb-4"><i class="fas fa-address-card text-primary me-2"></i>Billing Details</h4>
                <form id="paymentForm" action="process_payment.php" method="POST">
                    <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold small">Full Name</label>
                            <input type="text" class="form-control py-2 bg-light border-0" value="<?= $user['name'] ?>" readonly>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold small">Email Address</label>
                            <input type="email" class="form-control py-2 bg-light border-0" value="<?= $user['email'] ?>" readonly>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold small">Phone Number</label>
                            <input type="text" name="phone" id="phone" class="form-control py-2 shadow-sm" placeholder="Enter 10 digit phone number" required maxlength="10">
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-5" data-aos="fade-left">
            <div class="card border-0 shadow-lg p-4 bg-white rounded-4 sticky-top" style="top: 100px;">
                <h4 class="fw-bold mb-4">Order Summary</h4>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted"><?= $course['title'] ?></span>
                    <span class="fw-bold">₹<?= number_format($course['price'], 2) ?></span>
                </div>
                <hr class="text-muted opacity-25">
                <div class="d-flex justify-content-between mb-4">
                    <span class="h5 fw-bold">Total</span>
                    <span class="h5 fw-bold text-primary">₹<?= number_format($course['price'], 2) ?></span>
                </div>

                <div class="payment-options mb-4">
                    <p class="small text-muted mb-3 fw-bold uppercase">Select Payment Method:</p>
                    
                    <div class="form-check border p-3 rounded-3 mb-3 payment-item active-pay" id="upi-box">
                        <input class="form-check-input ms-1" type="radio" name="pay_method" id="upi" value="upi" checked onchange="togglePayment()">
                        <label class="form-check-label ms-3 fw-bold" for="upi">
                            <i class="fas fa-qrcode me-2 text-primary"></i> UPI / QR Code
                        </label>
                        <div id="upi-input" class="mt-3 animate__animated animate__fadeIn text-center">
                            <div class="p-3 bg-light rounded-3">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=upi://pay?pa=learnsdecode@upi&am=<?= $course['price'] ?>" class="img-fluid rounded shadow-sm mb-2">
                                <p class="small text-muted mb-0">Scan & Pay securely</p>
                            </div>
                        </div>
                    </div>

                    <div class="form-check border p-3 rounded-3 payment-item" id="card-box">
                        <input class="form-check-input ms-1" type="radio" name="pay_method" id="card" value="card" onchange="togglePayment()">
                        <label class="form-check-label ms-3 fw-bold" for="card">
                            <i class="fas fa-credit-card me-2 text-primary"></i> Credit / Debit Card
                        </label>
                        <div id="card-input" class="mt-3 d-none animate__animated animate__fadeIn">
                            <input type="text" id="card_number" class="form-control mb-2 border-0 bg-light" placeholder="Card Number (XXXX XXXX XXXX XXXX)">
                            <div class="row g-2">
                                <div class="col-6"><input type="text" id="expiry" class="form-control border-0 bg-light" placeholder="MM/YY"></div>
                                <div class="col-6"><input type="password" id="cvv" class="form-control border-0 bg-light" placeholder="CVV"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="button" onclick="processCheckout()" class="btn btn-primary w-100 py-3 fw-bold rounded-pill shadow-sm btn-hover-grow">
                    Complete Purchase <i class="fas fa-arrow-right ms-2"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    body { background-color: #f8f9fa; }
    .payment-item { transition: all 0.3s ease; cursor: pointer; border: 2px solid #eee !important; }
    .active-pay { border-color: #0d6efd !important; background-color: #f0f7ff; }
    .btn-hover-grow { transition: all 0.3s ease; }
    .btn-hover-grow:hover { transform: scale(1.02); box-shadow: 0 10px 20px rgba(13, 110, 253, 0.2); }
    .form-control:focus { box-shadow: none; border: 1px solid #0d6efd; }
</style>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({ duration: 1000, once: true });

    function togglePayment() {
        const isUpi = document.getElementById('upi').checked;
        const upiBox = document.getElementById('upi-box');
        const cardBox = document.getElementById('card-box');
        const upiInput = document.getElementById('upi-input');
        const cardInput = document.getElementById('card-input');

        if(isUpi) {
            upiBox.classList.add('active-pay');
            cardBox.classList.remove('active-pay');
            upiInput.classList.remove('d-none');
            cardInput.classList.add('d-none');
        } else {
            cardBox.classList.add('active-pay');
            upiBox.classList.remove('active-pay');
            cardInput.classList.remove('d-none');
            upiInput.classList.add('d-none');
        }
    }

    function processCheckout() {
        const phone = document.getElementById('phone').value.trim();
        if(!/^[0-9]{10}$/.test(phone)) {
            Swal.fire({ icon: 'error', title: 'Oops...', text: 'Please enter a valid 10-digit phone number.' });
            return;
        }

        const payMethod = document.querySelector('input[name="pay_method"]:checked').value;

        if(payMethod === 'card') {
            const cardNumber = document.getElementById('card_number').value.replace(/\s+/g, '');
            const expiry = document.getElementById('expiry').value.trim();
            const cvv = document.getElementById('cvv').value.trim();

            if(!/^[0-9]{16}$/.test(cardNumber)) {
                Swal.fire({ icon: 'error', title: 'Invalid Card', text: 'Please enter a valid 16-digit card number.' });
                return;
            }

            if(!/^(0[1-9]|1[0-2])\/[0-9]{2}$/.test(expiry)) {
                Swal.fire({ icon: 'error', title: 'Invalid Expiry', text: 'Expiry should be in MM/YY format.' });
                return;
            }

            if(!/^[0-9]{3,4}$/.test(cvv)) {
                Swal.fire({ icon: 'error', title: 'Invalid CVV', text: 'Please enter a valid CVV (3-4 digits).' });
                return;
            }
        }

        Swal.fire({
            title: 'Processing Order...',
            text: 'Please wait while we secure your seat.',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        // Simulate a small delay for animation
        setTimeout(() => {
            Swal.fire({
                icon: 'success',
                title: 'Payment Successful!',
                text: 'Congratulations! You are now enrolled in the course.',
                confirmButtonText: 'Start Learning',
                showClass: { popup: 'animate__animated animate__backInDown' }
            }).then(() => {
                document.getElementById('paymentForm').submit();
            });
        }, 2500);
    }
</script>

<?php include 'includes/footer.php'; ?>