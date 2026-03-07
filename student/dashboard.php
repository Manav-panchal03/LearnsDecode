<?php
session_start();
require '../config/config.php';
require_once '../includes/session_utils.php';

activateRole('student');

if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'student'){
    header("Location: ../login.php");
    exit();
}

$student_name = $_SESSION['user_name'];
?>

<?php include '../includes/header.php'; ?>

<div class="container mt-5">
    <h2>Welcome, <?= htmlspecialchars($student_name) ?>!</h2>
    <p>This is your student dashboard. Content will go here.</p>
</div>

<?php include '../includes/footer.php'; ?>
