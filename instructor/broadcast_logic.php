<?php
session_start();
require '../config/config.php';

// --- DELETE LOGIC ---
if (isset($_POST['delete_id'])) {
    $id = mysqli_real_escape_string($conn, $_POST['delete_id']);
    
    // File delete karva mate path melvo
    $res = mysqli_query($conn, "SELECT attachment_path FROM inbox_messages WHERE id = '$id'");
    $row = mysqli_fetch_assoc($res);
    
    if(!empty($row['attachment_path'])) {
        $file_path = "../uploads/materials/" . $row['attachment_path'];
        if(file_exists($file_path)) { unlink($file_path); }
    }

    if(mysqli_query($conn, "DELETE FROM inbox_messages WHERE id = '$id'")) {
        echo "deleted";
    }
    exit;
}

// --- UPDATE LOGIC ---
if (isset($_POST['update_broadcast_full'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $msg_type = mysqli_real_escape_string($conn, $_POST['msg_type']);
    $quiz_id = ($msg_type == 'quiz') ? $_POST['quiz_id'] : "NULL";

    // File handling logic
    $attachment_query = "";
    if ($msg_type === 'material' && isset($_FILES['attachment'])) {
        // Junu file delete karo
        $old_res = mysqli_query($conn, "SELECT attachment_path FROM inbox_messages WHERE id = '$id'");
        $old_row = mysqli_fetch_assoc($old_res);
        if(!empty($old_row['attachment_path'])) { @unlink("../uploads/materials/".$old_row['attachment_path']); }

        // Navu file upload
        $target_dir = "../uploads/materials/";
        $file_name = "MAT_" . time() . "_" . $_FILES["attachment"]["name"];
        move_uploaded_file($_FILES["attachment"]["tmp_name"], $target_dir . $file_name);
        $attachment_query = ", attachment_path = '$file_name'";
    } elseif ($msg_type !== 'material') {
        $attachment_query = ", attachment_path = NULL";
    }

    $sql = "UPDATE inbox_messages SET 
            content = '$content', 
            msg_type = '$msg_type', 
            quiz_id = $quiz_id 
            $attachment_query 
            WHERE id = '$id'";

    if (mysqli_query($conn, $sql)) { echo "updated"; } else { echo mysqli_error($conn); }
    exit;
}

// --- INSERT LOGIC (Updated to support Course ID and Quiz ID) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_id'])) {
    $sender_id = $_SESSION['user_id'];
    $course_id = mysqli_real_escape_string($conn, $_POST['course_id']);
    $msg_type = mysqli_real_escape_string($conn, $_POST['msg_type']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $quiz_id = (isset($_POST['quiz_id']) && !empty($_POST['quiz_id'])) ? $_POST['quiz_id'] : "NULL";
    
    $attachment_path = "NULL";

    if ($msg_type === 'material' && isset($_FILES['attachment']) && $_FILES['attachment']['error'] === 0) {
        $target_dir = "../uploads/materials/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_name = time() . '_' . basename($_FILES["attachment"]["name"]);
        if (move_uploaded_file($_FILES["attachment"]["tmp_name"], $target_dir . $file_name)) {
            $attachment_path = "'$file_name'";
        }
    }

    $sql = "INSERT INTO inbox_messages (sender_id, course_id, msg_type, quiz_id, attachment_path, content) 
            VALUES ('$sender_id', '$course_id', '$msg_type', $quiz_id, $attachment_path, '$content')";

    if (mysqli_query($conn, $sql)) {
        echo "success";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>