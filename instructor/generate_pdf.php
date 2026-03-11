<?php
require 'vendor/autoload.php'; // DomPDF installed folder
require '../config/config.php';
use Dompdf\Dompdf;
use Dompdf\Options;

$req_id = $_GET['request_id'] ?? null;
if(!$req_id) die("Invalid Request");

// Fetch Student & Course Details
$query = "SELECT r.*, u.name as student_name, c.title as course_title 
        FROM certificate_requests r 
        JOIN users u ON r.student_id = u.id 
        JOIN courses c ON r.course_id = c.id 
        WHERE r.id = '$req_id'";
$res = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($res);

if(!$data) die("Request Not Found");

$options = new Options();
$options->set('isRemoteEnabled', true); // For loading images/online fonts
$dompdf = new Dompdf($options);

// Superb & Animated Style Template
$html = "
<!DOCTYPE html>
<html>
<head>
<style>
    @page { margin: 0; }
    body { font-family: 'Helvetica', sans-serif; margin: 0; padding: 0; background-color: #f4f7fe; }
    
    .cert-wrapper {
        width: 800px;
        height: 560px;
        margin: 20px auto;
        padding: 15px;
        background: #fff;
        position: relative;
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(67, 24, 255, 0.1);
    }

    /* Professional Gradient Border */
    .outer-border {
        border: 10px solid transparent;
        height: 96%;
        border-image: linear-gradient(45deg, #4318ff, #05cd99) 1;
        padding: 30px;
        text-align: center;
    }

    .logo {
        font-size: 28px;
        font-weight: bold;
        color: #4318ff; /* Brand Color from Dashboard */
        margin-bottom: 20px;
    }

    .badge-icon {
        width: 80px;
        margin-bottom: 10px;
    }

    .main-title {
        font-size: 16px;
        color: #a3aed0;
        letter-spacing: 5px;
        text-transform: uppercase;
        margin-bottom: 5px;
    }

    .sub-title {
        font-size: 40px;
        color: #2b3674;
        font-weight: 800;
        margin-bottom: 30px;
    }

    .student-label {
        font-size: 18px;
        color: #707eae;
        font-style: italic;
    }

    .student-name {
        font-size: 55px;
        color: #4318ff;
        font-weight: bold;
        margin: 20px 0;
        text-decoration: underline;
        text-decoration-color: #05cd99;
    }

    .course-info {
        font-size: 20px;
        color: #2b3674;
        line-height: 1.5;
    }

    .course-name {
        font-weight: bold;
        color: #05cd99; /* Green from progress bar */
    }

    .footer {
        margin-top: 50px;
        width: 100%;
    }

    .sig-block {
        display: inline-block;
        width: 30%;
        text-align: center;
        margin: 0 5%;
    }

    .sig-line {
        border-top: 2px solid #e9edf7;
        margin-top: 10px;
        padding-top: 5px;
        color: #a3aed0;
        font-size: 14px;
    }

    /* Watermark decoration */
    .watermark {
        position: absolute;
        bottom: -20px;
        right: -20px;
        font-size: 150px;
        color: rgba(67, 24, 255, 0.03);
        font-weight: bold;
        transform: rotate(-30deg);
        z-index: -1;
    }
</style>
</head>
<body>
    <div class='cert-wrapper'>
        <div class='watermark'>LearnsDecode</div>
        <div class='outer-border'>
            <div class='logo'>LearnsDecode</div>
            <div class='main-title'>Certificate of Achievement</div>
            <div class='sub-title'>COMPLETION AWARD</div>
            
            <p class='student-label'>This certificate is proudly presented to</p>
            <div class='student-name'>{$data['student_name']}</div>
            
            <p class='course-info'>
                for successfully completing the advanced training in <br>
                <span class='course-name'>\"{$data['course_title']}\"</span> <br>
                on this day, " . date('d M, Y') . "
            </p>

            <div class='footer'>
                <div class='sig-block'>
                    <div class='sig-line'>Course Instructor</div>
                </div>
                <div class='sig-block'>
                    <img src='https://cdn-icons-png.flaticon.com/512/190/190411.png' class='badge-icon'>
                </div>
                <div class='sig-block'>
                    <div class='sig-line'>Platform Director</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>";

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

// Generate Unique Filename
$filename = "LearnsDecode_Cert_" . $req_id . "_" . time() . ".pdf";
$dest = "../uploads/certificates/" . $filename;

// Save PDF
file_put_contents($dest, $dompdf->output());

// Update Database status
mysqli_query($conn, "UPDATE certificate_requests SET status='approved', certificate_pdf='$filename', issued_at=NOW() WHERE id='$req_id'");

// Redirect back with success message
header("Location: manage_certificates.php?issued=success");
exit();
?>