<?php
session_start();
require '../config/config.php';
header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] == "POST"){
    $instructor_id = $_SESSION['user_id'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    $price = mysqli_real_escape_string($conn, $_POST['price'] ?? 0);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $course_id = isset($_POST['course_id']) ? $_POST['course_id'] : null;

    // Thumbnail upload logic
    $thumbnail = "course-default.jpg";
    $thumb_update_sql = "";
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === 0) {
        $ext = pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION);
        $file_name = " $instructor_id " . "course_" . time() . "." . $ext ;
        $target = "../uploads/thumbnails/" . $file_name;
        if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $target)) {
            $thumbnail = $file_name;
            $thumb_update_sql = ", thumbnail = '$file_name'";
        }
    }

    if (!empty($course_id) && is_numeric($course_id)) {
        // UPDATE
        $sql = "UPDATE courses SET title='$title', category_id='$category_id', price='$price', description='$description' $thumb_update_sql WHERE id='$course_id'";
        if(mysqli_query($conn, $sql)){
            echo json_encode(['success' => true, 'course_id' => $course_id]);
        } else {
            echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
        }
    } else {
        // INSERT
        $sql = "INSERT INTO courses (instructor_id, category_id, title, description, thumbnail, price, status) 
                VALUES ('$instructor_id', '$category_id', '$title', '$description', '$thumbnail', '$price', 'draft')";
        if(mysqli_query($conn, $sql)){
            echo json_encode(['success' => true, 'course_id' => mysqli_insert_id($conn)]);
        } else {
            echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
        }
    }
    exit;
}