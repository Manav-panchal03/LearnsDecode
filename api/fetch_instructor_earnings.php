<?php
session_start();
require '../config/config.php';

if (!isset($_SESSION['user_id'])) {
    exit('Unauthorized');
}

$instructor_id = $_SESSION['user_id'];
$student_search = isset($_POST['student']) ? mysqli_real_escape_string($conn, $_POST['student']) : '';
$course_id = isset($_POST['course_id']) ? mysqli_real_escape_string($conn, $_POST['course_id']) : '';

// Base Query - Joining enrollments, courses, and users (to get student name)
$sql = "SELECT e.*, c.title as course_name, u.name as student_name 
        FROM enrollments e
        JOIN courses c ON e.course_id = c.id
        JOIN users u ON e.student_id = u.id
        WHERE c.instructor_id = $instructor_id AND e.payment_status = 'completed'";

// Add Filter for Student Name
if (!empty($student_search)) {
    $sql .= " AND u.name LIKE '%$student_search%'";
}

// Add Filter for Course
if (!empty($course_id)) {
    $sql .= " AND c.id = '$course_id'";
}

$sql .= " ORDER BY e.enrolled_at DESC";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $date = date('d M Y', strtotime($row['enrolled_at']));
        echo "<tr>
                <td>
                    <div class='fw-bold'>{$row['student_name']}</div>
                    <small class='text-muted'>ID: #{$row['student_id']}</small>
                </td>
                <td><span class='text-truncate d-inline-block' style='max-width: 200px;'>{$row['course_name']}</span></td>
                <td>₹" . number_format($row['amount'], 2) . "</td>
                <td><span class='amount-green'>+ ₹" . number_format($row['instructor_revenue'], 2) . "</span></td>
                <td class='text-end text-muted small'>$date</td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='5' class='text-center py-4 text-muted'>No data found matching your filters.</td></tr>";
}
?>