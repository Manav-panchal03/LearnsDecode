<?php
session_start();
require '../config/config.php';
require_once '../includes/session_utils.php';

activateRole('instructor');

if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'instructor'){
    header("Location: ../login.php");
    exit();
}

$instructor_id = $_SESSION['user_id'];

// AJAX: Fetch Quizzes Logic
if (isset($_POST['get_quizzes'])) {
    $cid = mysqli_real_escape_string($conn, $_POST['course_id']);
    $res = mysqli_query($conn, "SELECT id, title FROM quizzes WHERE course_id = '$cid' AND status = 'published'");
    if (mysqli_num_rows($res) > 0) {
        echo '<option value="">--- Select Quiz ---</option>';
        while($q = mysqli_fetch_assoc($res)) { echo "<option value='{$q['id']}'>{$q['title']}</option>"; }
    } else { echo "empty"; }
    exit;
}

$courses_q = mysqli_query($conn, "SELECT id, title FROM courses WHERE instructor_id = $instructor_id");

// Profile fetch for Top Nav
$profile_q = mysqli_query($conn ,"SELECT u.name, p.profile_pic FROM users u LEFT JOIN instructor_profiles p ON u.id = p.user_id WHERE u.id = $instructor_id");
$profile_data = mysqli_fetch_assoc($profile_q);
$p_img = (!empty($profile_data['profile_pic']) && $profile_data['profile_pic'] != 'default-avatar.png') 
        ? "../uploads/profile/".$profile_data['profile_pic'] 
        : "https://ui-avatars.com/api/?name=".urlencode($profile_data['name'])."&background=6c63ff&color=fff";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Broadcast Center | LearnsDecode</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <style>
        /* TAMARA DASHBOARD NU CSS - UNCHANGED */
        :root {
            --sidebar-width: 280px;
            --primary-color: #6c63ff;
            --dark-bg: #1e1e2d;
        }

        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; overflow-x: hidden; }

        #sidebar { width: var(--sidebar-width); height: 100vh; position: fixed; left: 0; top: 0; background: var(--dark-bg); color: #a2a3b7; transition: all 0.3s; z-index: 1000; box-shadow: 4px 0 10px rgba(0,0,0,0.1); }
        #sidebar.active { left: calc(-1 * var(--sidebar-width)); }
        .sidebar-header { padding: 30px 20px; text-align: center; background: rgba(0,0,0,0.2); }
        .nav-links { padding: 20px 0; }
        .nav-links a { padding: 12px 25px; display: flex; align-items: center; color: #a2a3b7; text-decoration: none; transition: 0.2s; border-left: 4px solid transparent; }
        .nav-links a i { width: 30px; font-size: 1.1rem; }
        .nav-links a:hover, .nav-links a.active { background: #2b2b40; color: #ffffff; border-left: 4px solid var(--primary-color); }
        
        #content { width: calc(100% - var(--sidebar-width)); margin-left: var(--sidebar-width); transition: all 0.3s; min-height: 100vh; }
        #content.active { width: 100%; margin-left: 0; }
        .navbar-custom { background: #ffffff; padding: 15px 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        #sidebarCollapse { background: var(--primary-color); border: none; color: white; padding: 5px 12px; border-radius: 5px; }

        /* BROADCAST SPECIFIC UI (Matching Dashboard Style) */
        .broadcast-card { background: white; border-radius: 20px; padding: 30px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .form-label { font-weight: 600; color: #444; margin-bottom: 8px; font-size: 0.9rem; }
        .form-control, .form-select { border-radius: 10px; border: 1px solid #e1e1e1; padding: 12px; }
        .form-control:focus { border-color: var(--primary-color); box-shadow: 0 0 0 0.2rem rgba(108, 99, 255, 0.1); }
        
        .upload-box { border: 2px dashed #d1cfd4; border-radius: 15px; padding: 30px; text-align: center; cursor: pointer; transition: 0.3s; background: #fbfaff; }
        .upload-box:hover { border-color: var(--primary-color); background: #f3f2ff; }
        .btn-launch { background: var(--primary-color); color: #fff; border: none; border-radius: 10px; padding: 15px; width: 100%; font-weight: 600; transition: 0.3s; }
        .btn-launch:hover { opacity: 0.9; transform: translateY(-2px); }
    </style>
</head>
<body>

<nav id="sidebar">
    <div class="sidebar-header">
        <h3 class="fw-bold text-white mb-0">Learns<span style="color:var(--primary-color)">Decode</span></h3>
        <small>Instructor Workspace</small>
    </div>
    <div class="nav-links">
        <a href="dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
        <a href="add_course.php"><i class="fas fa-plus-square"></i> Create Course</a>
        <a href="manage_courses.php"><i class="fas fa-book-open"></i> My Courses</a>
        <a href="add_quiz.php"><i class="fas fa-question-circle"></i> Create Quizzes</a>
        <a href="manage_quizzes.php"><i class="fas fa-tasks"></i> Manage Quizzes</a>
        <a href="inbox.php" class="active"><i class="fas fa-envelope"></i> Broadcast Center </a>
        <a href="manage_students.php"><i class="fas fa-users"></i> Students</a>
        <a href="manage_certificates.php"><i class="fas fa-certificate"></i> Certificates</a>
        <a href="earnings.php"><i class="fas fa-wallet"></i> Earnings</a>
        <a href="analytics.php"><i class="fas fa-chart-line"></i> Analytics</a>
        <hr style="border-color: rgba(255,255,255,0.1)">
        <a href="../logout.php" class="text-danger"><i class="fas fa-power-off"></i> Logout</a>
    </div>
</nav>

<div id="content">
    <nav class="navbar navbar-custom d-flex justify-content-between">
        <button type="button" id="sidebarCollapse">
            <i class="fas fa-align-left"></i>
        </button>
        <div class="user-info d-flex align-items-center">
            <div class="text-end me-3 d-none d-sm-block">
                <div class="fw-bold lh-1"><?= $profile_data['name']; ?></div>
                <small class="text-muted" style="font-size: 11px;">Instructor</small>
            </div>
            <img src="<?= $p_img ?>" class="rounded-circle shadow-sm border" width="45" height="45" style="object-fit: cover;">
        </div>
    </nav>

    <div class="container-fluid p-4">
        <h2 class="fw-bold mb-4" data-aos="fade-down">Broadcast Center </h2>
        
        <div class="broadcast-card" data-aos="fade-up">
            <form id="broadcastForm" enctype="multipart/form-data">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Step 01: Course</label>
                        <select class="form-select" name="course_id" id="course_id" required>
                            <option value="">Select Course</option>
                            <?php 
                            $courses_q = mysqli_query($conn, "SELECT id, title FROM courses WHERE instructor_id = $instructor_id");
                            while($c = mysqli_fetch_assoc($courses_q)): ?>
                                <option value="<?= $c['id'] ?>"><?= $c['title'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Step 02: Type</label>
                        <select class="form-select" name="msg_type" id="msg_type" required>
                            <option value="msg">💬 Message Only</option>
                            <option value="material">📁 Material Upload</option>
                            <option value="quiz">📝 Quiz Announcement</option>
                        </select>
                    </div>

                    <div class="col-12" id="quiz_section" style="display:none;">
                        <label class="form-label">Step 03: Select Quiz</label>
                        <select class="form-select" name="quiz_id" id="quiz_id"></select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Message Content</label>
                        <textarea class="form-control" name="content" rows="4" placeholder="What's the update today?" required></textarea>
                    </div>

                    <div class="col-12" id="upload_section" style="display:none;">
                        <input type="file" name="attachment" id="file_btn" hidden accept=".pdf,.docx,.jpg,.png">
                        <div class="upload-box" onclick="$('#file_btn').click()">
                            <i class="fas fa-file-import fa-3x text-primary mb-3"></i>
                            <h6 id="file_name">Click to attach PDF, Images or DOCX</h6>
                            <small class="text-muted">Max size: 5MB</small>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-launch mt-4" id="submitBtn">
                    SEND BROADCAST <i class="fas fa-bolt ms-2"></i>
                </button>
            </form>
        </div>

        <div class="broadcast-card mt-5" data-aos="fade-up">
            <h4 class="fw-bold mb-4">Recent Broadcasts </h4>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Type</th>
                            <th>Course</th>
                            <th>Content</th>
                            <th>Ref / File</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="broadcastList">
                        <?php
                        $fetch_sql = "SELECT m.*, c.title as course_title, q.title as quiz_title 
                                     FROM inbox_messages m 
                                     LEFT JOIN courses c ON m.course_id = c.id 
                                     LEFT JOIN quizzes q ON m.quiz_id = q.id 
                                     WHERE m.sender_id = $instructor_id 
                                     ORDER BY m.created_at DESC";
                        $res = mysqli_query($conn, $fetch_sql);
                        while($row = mysqli_fetch_assoc($res)):
                        ?>
                        <tr id="row_<?= $row['id'] ?>">
                            <td>
                                <?php if($row['msg_type'] == 'quiz'): ?>
                                    <span class="badge bg-info text-dark">Quiz</span>
                                <?php elseif($row['msg_type'] == 'material'): ?>
                                    <span class="badge bg-success text-white">Material</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary text-white">Message</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $row['course_title'] ?></td>
                            <td><small><?= substr($row['content'], 0, 40) ?>...</small></td>
                            <td>
                                <?php if($row['msg_type'] == 'quiz'): ?>
                                    <span class="text-primary fw-bold"><?= $row['quiz_title'] ?></span>
                                <?php elseif($row['msg_type'] == 'material'): ?>
                                    <a href="../uploads/materials/<?= $row['attachment_path'] ?>" target="_blank" class="btn btn-sm btn-outline-dark"><i class="fas fa-eye"></i></a>
                                <?php else: ?> - <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-danger" onclick="deleteBroadcast(<?= $row['id'] ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <button class="btn btn-sm btn-primary" onclick="editBroadcast(<?= $row['id'] ?>, '<?= $row['msg_type'] ?>', '<?= addslashes($row['content']) ?>', <?= $row['course_id'] ?>, '<?= $row['quiz_id'] ?>')">
    <i class="fas fa-edit"></i>
</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<script>
    AOS.init({ duration: 800, once: true });

    // Sidebar Toggle
    $('#sidebarCollapse').on('click', function () {
        $('#sidebar').toggleClass('active');
        $('#content').toggleClass('active');
    });

    // --- FILE SIZE VALIDATION & DISPLAY LOGIC ---
    $('#file_btn').on('change', function() {
        const file = this.files[0];
        const maxSize = 5 * 1024 * 1024; // 5MB in Bytes

        if (file) {
            if (file.size > maxSize) {
                Swal.fire({
                    icon: 'error',
                    title: 'File Too Large!',
                    text: 'Please select a file smaller than 5MB.',
                    confirmButtonColor: '#6c63ff'
                });
                this.value = ""; // Clear selected file
                $('#file_name').html('Click to attach PDF or Images').css('color', '#6c757d');
            } else {
                // Display attached file name
                $('#file_name').html('<i class="fas fa-check-circle me-1"></i> Attached: ' + file.name)
                               .css('color', '#28a745');
            }
        }
    });

    // Broadcast Type Switcher
    $('#msg_type').on('change', function() {
        let type = $(this).val();
        $('#quiz_section, #upload_section').hide();
        if(type === 'quiz') { 
            let cid = $('#course_id').val();
            if(!cid) { 
                Swal.fire('Error', 'Please select a course first.', 'error'); 
                $(this).val('msg');
            } else {
                loadQuizzes(cid);
            }
        } else if(type === 'material') {
            $('#upload_section').fadeIn();
        }
    });

    function loadQuizzes(cid) {
        $.post('', { get_quizzes: true, course_id: cid }, function(res) {
            if(res.trim() === "empty") {
                Swal.fire('Warning', 'There are no quizzes available for this course.', 'warning');
                $('#msg_type').val('msg');
            } else {
                $('#quiz_id').html(res);
                $('#quiz_section').fadeIn();
            }
        });
    }

    $('#broadcastForm').on('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sending...');

        $.ajax({
            url: 'broadcast_logic.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(resp) {
                if(resp.includes('success')) {
                    Swal.fire('Success!', 'Broadcast sent successfully!', 'success');
                    $('#broadcastForm')[0].reset();
                    $('#file_name').html('Click to attach PDF or Images').css('color', '#6c757d');
                    $('#quiz_section, #upload_section').hide();
                } else {
                    Swal.fire('Error', resp, 'error');
                }
                $('#submitBtn').prop('disabled', false).html('SEND BROADCAST <i class="fas fa-bolt ms-2"></i>');
            }
        });
    });

    function deleteBroadcast(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "A broadcast will be deleted permanently! , also from students inbox !",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#6c63ff',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('broadcast_logic.php', { delete_id: id }, function(resp) {
                    if(resp.trim() === 'deleted') {
                        $('#row_' + id).fadeOut();
                        Swal.fire('Deleted!', 'Broadcast Deleted!', 'success');
                    } else {
                        Swal.fire('Error', 'Failed to delete broadcast.', 'error');
                    }
                });
            }
        });
    }

    function editBroadcast(id, currentType, currentContent, currentCourseId, currentQuizId = '') {
    Swal.fire({
        title: 'Update Broadcast 🚀',
        html: `
            <div class="text-start">
                <div class="mb-3">
                    <label class="form-label">Message Content</label>
                    <textarea id="edit_content" class="form-control" rows="3">${currentContent}</textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Change Type</label>
                    <select id="edit_type" class="form-select" onchange="toggleEditFields(this.value, ${currentCourseId})">
                        <option value="msg" ${currentType === 'msg' ? 'selected' : ''}>💬 Message Only</option>
                        <option value="material" ${currentType === 'material' ? 'selected' : ''}>📁 Material Upload</option>
                        <option value="quiz" ${currentType === 'quiz' ? 'selected' : ''}>📝 Quiz Announcement</option>
                    </select>
                </div>

                <div id="edit_quiz_div" class="mb-3" style="display: ${currentType === 'quiz' ? 'block' : 'none'};">
                    <label class="form-label">Select Quiz</label>
                    <select id="edit_quiz_id" class="form-select"></select>
                </div>

                <div id="edit_file_div" class="mb-3" style="display: ${currentType === 'material' ? 'block' : 'none'};">
                    <label class="form-label">Update Attachment (Optional)</label>
                    <input type="file" id="edit_attachment" class="form-control" accept=".pdf,.docx,.jpg,.png">
                    <small class="text-muted">Max 5MB. Khali rakhsho toh junu file rehse.</small>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Save Changes',
        confirmButtonColor: '#6c63ff',
        showClass: { popup: 'animate__animated animate__zoomIn' },
        hideClass: { popup: 'animate__animated animate__fadeOut' },
        didOpen: () => {
            if(currentType === 'quiz') {
                loadEditQuizzes(currentCourseId, currentQuizId);
            }
        },
        preConfirm: () => {
            const content = document.getElementById('edit_content').value;
            const type = document.getElementById('edit_type').value;
            const quiz_id = document.getElementById('edit_quiz_id').value;
            const fileInput = document.getElementById('edit_attachment');
            
            if (!content) { Swal.showValidationMessage('Content required!'); return false; }
            if (type === 'quiz' && !quiz_id) { Swal.showValidationMessage('Quiz required!'); return false; }
            
            // File validation jem create ma hatu em j
            if (type === 'material' && fileInput.files.length > 0) {
                if (fileInput.files[0].size > 5 * 1024 * 1024) {
                    Swal.showValidationMessage('File 5MB thi moti na joie!');
                    return false;
                }
            }

            // FormData object banavvo padse kem ke file upload che
            let fd = new FormData();
            fd.append('update_broadcast_full', 'true');
            fd.append('id', id);
            fd.append('content', content);
            fd.append('msg_type', type);
            fd.append('quiz_id', quiz_id);
            if(fileInput.files.length > 0) {
                fd.append('attachment', fileInput.files[0]);
            }
            return fd;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'broadcast_logic.php',
                type: 'POST',
                data: result.value,
                processData: false,
                contentType: false,
                success: function(resp) {
                    if(resp.trim() === 'updated') {
                        Swal.fire('Updated!', 'Success!', 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Error', resp, 'error');
                    }
                }
            });
        }
    });
}

// Helper functions for modal logic
function toggleEditFields(val, courseId) {
    $('#edit_quiz_div').hide();
    $('#edit_file_div').hide();
    if(val === 'quiz') {
        $('#edit_quiz_div').fadeIn();
        loadEditQuizzes(courseId);
    } else if(val === 'material') {
        $('#edit_file_div').fadeIn();
    }
}

function loadEditQuizzes(cid, selectedId = '') {
    $.post('', { get_quizzes: true, course_id: cid }, function(res) {
        $('#edit_quiz_id').html(res);
        if(selectedId) $('#edit_quiz_id').val(selectedId);
    });
}
</script>
</body>
</html>