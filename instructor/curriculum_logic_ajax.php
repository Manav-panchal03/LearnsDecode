<?php
error_reporting(E_ALL);
ini_set('display_errors', 0); // એરરને છુપાવો જેથી AJAX બગડે નહીં
require '../config/config.php';

// 1. FETCH CURRICULUM
if (isset($_POST['fetch_curriculum'])) {
    $course_id = (int)$_POST['course_id'];
    $result = mysqli_query($conn, "SELECT * FROM units WHERE course_id = $course_id ORDER BY id ASC");
    
    while($unit = mysqli_fetch_assoc($result)) {
        echo '<div class="unit-box shadow-sm border rounded-3 p-3 mb-3 bg-white" id="unit-'.$unit['id'].'">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-primary">
                    <i class="fas fa-folder me-2"></i><span id="unit-title-text-'.$unit['id'].'">'.htmlspecialchars($unit['unit_title']).'</span>
                </h6>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-light border" title="Edit Unit" onclick="editUnitPrompt('.$unit['id'].')">
                        <i class="fas fa-edit text-muted"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-light border" title="Delete Unit" onclick="deleteUnit('.$unit['id'].')">
                        <i class="fas fa-trash text-danger"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-dark fw-bold" onclick="addLessonPrompt('.$unit['id'].')">
                        <i class="fas fa-plus me-1"></i> Lesson
                    </button>
                </div>
            </div>
            <div id="lesson-list-'.$unit['id'].'" class="mt-2 ms-4 pt-1 border-start ps-3">';
        
        $lessons_result = mysqli_query($conn, "SELECT * FROM lessons WHERE unit_id = {$unit['id']} ORDER BY id ASC");
        while($lesson = mysqli_fetch_assoc($lessons_result)) {
            $type_display = $lesson['content_type'] ?: 'video';
            echo '<div class="lesson-item py-2 border-bottom d-flex justify-content-between align-items-center" style="font-size: 0.9rem;">
                <div>
                    <i class="fas fa-play-circle text-primary me-2" onclick="previewContent(\''.addslashes($lesson['lesson_url']).'\', \''.$type_display.'\')" style="cursor:pointer"></i>
                    <span id="lesson-title-text-'.$lesson['id'].'">['.strtoupper($type_display).'] '.htmlspecialchars($lesson['lesson_title']).'</span>
                    <input type="hidden" id="lesson-url-val-'.$lesson['id'].'" value="'.htmlspecialchars($lesson['lesson_url']).'">
                    <input type="hidden" id="lesson-type-val-'.$lesson['id'].'" value="'.$type_display.'">
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm text-muted" title="Edit Lesson" onclick="editLessonPrompt('.$lesson['id'].', '.$unit['id'].')">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-sm text-muted" title="Delete Lesson" onclick="deleteLesson('.$lesson['id'].')">
                        <i class="fas fa-trash text-danger"></i>
                    </button>
                </div>
            </div>';
        }
        echo '</div></div>';
    }
    exit;
}

// 2. ADD UNIT
if(isset($_POST['add_unit'])) {
    $course_id = (int)$_POST['course_id'];
    $unit_title = mysqli_real_escape_string($conn, trim($_POST['unit_title']));
    $query = "INSERT INTO units (course_id, unit_title) VALUES ($course_id, '$unit_title')";
    if(mysqli_query($conn, $query)) {
        $unit_id = mysqli_insert_id($conn);
        echo '<div class="unit-box shadow-sm border rounded-3 p-3 mb-3 bg-white" id="unit-'.$unit_id.'">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-primary">
                    <i class="fas fa-folder me-2"></i><span id="unit-title-text-'.$unit_id.'">'.htmlspecialchars($unit_title).'</span>
                </h6>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-light border" onclick="editUnitPrompt('.$unit_id.')">
                        <i class="fas fa-edit text-muted"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-dark fw-bold" onclick="addLessonPrompt('.$unit_id.')">
                        <i class="fas fa-plus me-1"></i> Lesson
                    </button>
                </div>
            </div>
            <div id="lesson-list-'.$unit_id.'" class="mt-2 ms-4 pt-1 border-start ps-3"></div>
        </div>';
    }
    exit;
}

// 3. UPDATE UNIT
if(isset($_POST['update_unit'])) {
    $unit_id = (int)$_POST['unit_id'];
    $unit_title = mysqli_real_escape_string($conn, trim($_POST['unit_title']));
    mysqli_query($conn, "UPDATE units SET unit_title='$unit_title' WHERE id=$unit_id");
    echo json_encode(['success' => true, 'unit_title' => $unit_title]);
    exit;
}

// 4. LESSON ADD/UPDATE
if(isset($_POST['add_lesson']) || isset($_POST['update_lesson'])) {
    $title = mysqli_real_escape_string($conn, trim($_POST['lesson_title']));
    $type = mysqli_real_escape_string($conn, $_POST['content_type']);
    $final_path = '';

    if($type === 'file') {
        if(isset($_FILES['lesson_file']) && $_FILES['lesson_file']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['lesson_file']['name'], PATHINFO_EXTENSION));
            $new_name = 'res_'.time().'_'.rand(1000,9999).'.'.$ext;
            $target = '../uploads/resources/';
            if(!is_dir($target)) mkdir($target, 0755, true);
            
            if(move_uploaded_file($_FILES['lesson_file']['tmp_name'], $target.$new_name)) {
                $final_path = 'uploads/resources/'.$new_name;
            }
        } else {
            $final_path = isset($_POST['lesson_url']) ? trim($_POST['lesson_url']) : '';
        }
    } else {
        $final_path = isset($_POST['lesson_url']) ? trim($_POST['lesson_url']) : '';
    }

    if(empty($final_path)) { echo 'error: Missing file or URL'; exit; }

    if(isset($_POST['add_lesson'])) {
        $unit_id = (int)$_POST['unit_id'];
        $query = "INSERT INTO lessons (unit_id, lesson_title, content_type, lesson_url) VALUES ($unit_id, '$title', '$type', '$final_path')";
    } else {
        $lesson_id = (int)$_POST['lesson_id'];
        $query = "UPDATE lessons SET lesson_title='$title', content_type='$type', lesson_url='$final_path' WHERE id=$lesson_id";
    }

    if(mysqli_query($conn, $query)) {
        echo 'success';
    } else {
        echo 'error: ' . mysqli_error($conn);
    }
    exit;
}

// 5. DELETE UNIT
if (isset($_POST['delete_unit'])) {
    $unit_id = (int)$_POST['unit_id'];
    mysqli_query($conn, "DELETE FROM lessons WHERE unit_id = $unit_id");
    $query = "DELETE FROM units WHERE id = $unit_id";
    if (mysqli_query($conn, $query)) {
        echo 'success';
    } else {
        echo 'error';
    }
    exit;
}

// 6. DELETE LESSON
if (isset($_POST['delete_lesson'])) {
    $lesson_id = (int)$_POST['lesson_id'];
    $query = "DELETE FROM lessons WHERE id = $lesson_id";
    if (mysqli_query($conn, $query)) {
        echo 'success';
    } else {
        echo 'error';
    }
    exit;
}
?>