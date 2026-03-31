<?php
session_start();
require '../config/config.php';
require_once '../includes/session_utils.php';

// activate instructor role if present
activateRole('instructor');

if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'instructor'){
    header("Location: ../login.php"); exit();
}

$cat_result = mysqli_query($conn, "SELECT * FROM categories");
$edit_id = isset($_GET['edit_id']) ? $_GET['edit_id'] : null;

$course_data = null;
if($edit_id) {
    $res = mysqli_query($conn, "SELECT * FROM courses WHERE id = '$edit_id'");
    $course_data = mysqli_fetch_assoc($res);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create & Edit Course | LearnsDecode</title>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * { font-family: 'Poppins', sans-serif; }
        body { background-color: #f0f2f5; }
        .setup-container { max-width: 1100px; margin: 40px auto; }
        .card { border-radius: 25px; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.1); overflow: hidden; }
        .info-panel { background: linear-gradient(145deg, #6c63ff 0%, #3f3d56 100%); color: white; padding: 50px 40px; }
        .step-item { display: flex; align-items: center; margin-bottom: 35px; opacity: 0.4; transition: 0.4s; }
        .step-item.active { opacity: 1; transform: translateX(10px); }
        .step-number { width: 35px; height: 35px; border: 2px solid rgba(255,255,255,0.4); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-right: 15px; font-weight: 700; }
        .active .step-number { background: white; color: #6c63ff; border-color: white; }
        .form-side { background: white; padding: 50px; min-height: 600px; }
        .form-step { display: none; }
        .form-step.active { display: block; animation: fadeIn 0.5s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .form-label { font-size: 0.75rem; font-weight: 700; color: #64748b; letter-spacing: 1px; }
        .form-control, .form-select { border-radius: 12px; border: 2px solid #f1f5f9; background: #f8fafc; padding: 12px 15px; }
        #imagePreview { width: 100%; height: 180px; border: 2px dashed #cbd5e0; border-radius: 15px; display: flex; align-items: center; justify-content: center; background: #f8fafc; cursor: pointer; overflow: hidden; }
        #imagePreview img { width: 100%; height: 100%; object-fit: cover; }
        .unit-box { background: #fff; border: 1px solid #e2e8f0; border-radius: 15px; padding: 20px; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        .quick-dashboard-btn { position: fixed; bottom: 24px; right: 24px; z-index: 1100; border-radius: 999px; padding: 0.85rem 1.25rem; background: #fff; color: #1f2937; border: 1px solid #e2e8f0; box-shadow: 0 14px 30px rgba(15,23,42,0.12); transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .quick-dashboard-btn:hover { transform: translateY(-2px); text-decoration: none; box-shadow: 0 20px 36px rgba(15,23,42,0.18); }
    </style>
</head>
<body>

<a href="dashboard.php" class="quick-dashboard-btn"><i class="fas fa-arrow-left me-2"></i>Back to Dashboard</a>
<div class="container setup-container">
    <div class="card">
        <div class="row g-0">
            <div class="col-md-4 info-panel">
                <h3 class="fw-bold mb-5">Learns<span>Decode</span></h3>
                <div class="step-item active" id="ind-1">
                    <div class="step-number">1</div> <div>Course Details</div>
                </div>
                <div class="step-item" id="ind-2">
                    <div class="step-number">2</div> <div>Curriculum Builder</div>
                </div>
                <div class="step-item" id="ind-3">
                    <div class="step-number">3</div> <div>Publish Course</div>
                </div>
            </div>

            <div class="col-md-8 form-side">
                <form id="courseMegaForm" enctype="multipart/form-data">
                    <input type="hidden" name="course_id" id="saved_course_id" value="<?= $edit_id ?>">

                    <div class="form-step active" id="step-1">
                        <h2 class="fw-bold">Course Info</h2>
                        <p class="text-muted mb-4">Foundation of your course.</p>
                        
                        <div class="mb-4">
                            <label class="form-label text-uppercase">Course Title</label>
                            <input type="text" name="title" id="course_title" placeholder="e.g. Full Stack Web Development Course" value="<?= $course_data['title'] ?? '' ?>" class="form-control" required>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label text-uppercase">Category</label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">Select Category</option>
                                    <?php while($c = mysqli_fetch_assoc($cat_result)): ?>
                                        <option value="<?= $c['id'] ?>" <?= ($course_data && $course_data['category_id'] == $c['id']) ? 'selected' : '' ?>><?= $c['name'] ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-uppercase">Price (₹)</label>
                                <input type="number" name="price" placeholder="0 = free" value="<?= $course_data['price'] ?? '' ?>" class="form-control">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-uppercase">Thumbnail</label>
                            <input type="file" name="thumbnail" id="thumbInput" class="d-none" onchange="previewThumb(event)">
                            <div id="imagePreview" onclick="document.getElementById('thumbInput').click();">
                                <?php if($course_data && $course_data['thumbnail']): ?>
                                    <img src="../uploads/thumbnails/<?= $course_data['thumbnail'] ?>" id="out-img">
                                <?php else: ?>
                                    <div id="pre-text" class="text-center">
                                        <i class="fas fa-image fa-2x text-muted mb-2"></i>
                                        <p class="mb-0 small text-muted">Click to upload thubmnail (recommended size: 800x439)</p>
                                    </div>
                                    <img id="out-img" style="display:none">
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-uppercase">Description</label>
                            <textarea name="description" placeholder="Brief of your course..." class="form-control" rows="3"><?= $course_data['description'] ?? '' ?></textarea>
                        </div>

                        <button type="button" class="btn btn-primary w-100 p-3 fw-bold" onclick="saveStep1()">
                            Continue to Curriculum <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>

                    <div class="form-step" id="step-2">
                        <h2 class="fw-bold">Curriculum Builder</h2>
                        <p class="text-muted mb-4">Add Units & Lessons (Title, Type, URL).</p>
                        
                        <div id="unit-list" class="mb-4"></div>

                        <div class="input-group mb-4">
                            <input type="text" id="unit_title_input" class="form-control" placeholder="New Unit Title...">
                            <button class="btn btn-dark px-4" type="button" onclick="addUnit()">+ Add Unit</button>
                        </div>

                        <div class="d-flex gap-3">
                            <button type="button" class="btn btn-outline-secondary p-3 flex-fill" onclick="location.href='dashboard.php'">Save as Draft</button>
                            <button type="button" class="btn btn-success p-3 flex-fill shadow" onclick="changeStep(3)">Next: Finalize</button>
                        </div>
                    </div>

                    <div class="form-step text-center pt-5" id="step-3">
                        <img src="https://illustrations.popsy.co/purple/success.svg" style="width: 150px;" class="mb-4">
                        <h2 class="fw-bold">Ready to Launch?</h2>
                        <p class="text-muted mb-5">Click below to make your course live.</p>
                        <button type="button" class="btn btn-primary btn-lg px-5 py-3 fw-bold shadow" onclick="publishNow()">
                            Publish Now <i class="fas fa-rocket ms-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="lessonModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 20px;">
            <div class="modal-header">
                <h5 class="fw-bold" id="modalTitle">Add Lesson</h5> 
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modal_unit_id">
                <input type="hidden" id="modal_lesson_id"> 
                <input type="hidden" id="modal_existing_url"> 
                <div class="mb-3">
                    <label class="form-label small fw-bold">LESSON TITLE</label>
                    <input type="text" id="modal_lesson_title" class="form-control" placeholder="Introduction...">
                </div>
                
                <div class="mb-3">
                    <label class="form-label small fw-bold">TYPE</label>
                    <select id="modal_content_type" class="form-select" onchange="toggleInputType()">
                        <option value="video">Video (YouTube/Vimeo)</option>
                        <option value="file">Upload file / PDF, Excel, etc.</option>
                    </select>
                </div>

                <div class="mb-3" id="url_input_div">
                    <label class="form-label small fw-bold">URL / LINK</label>
                    <input type="url" id="modal_lesson_url" class="form-control" placeholder="https://...">
                </div>

                <div class="mb-3" id="file_input_div" style="display:none;">
                    <label class="form-label small fw-bold">SELECT FILE</label>
                    <input type="file" id="modal_lesson_file" class="form-control">
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-primary w-100 fw-bold" onclick="submitLesson()">Save Lesson</button>
            </div>
        </div>
    </div>
</div>

<script>
let current_course_id = document.getElementById('saved_course_id').value || '';

function previewThumb(event) {
    let reader = new FileReader();
    reader.onload = function(){
        let out = document.getElementById('out-img');
        out.src = reader.result;
        out.style.display = 'block';
        if(document.getElementById('pre-text')) document.getElementById('pre-text').style.display = 'none';
    }
    reader.readAsDataURL(event.target.files[0]);
}

function changeStep(step) {
    document.querySelectorAll('.form-step').forEach(s => s.classList.remove('active'));
    document.getElementById('step-' + step).classList.add('active');
    document.querySelectorAll('.step-item').forEach(i => i.classList.remove('active'));
    document.getElementById('ind-' + step).classList.add('active');
}

function saveStep1() {
    let form = document.getElementById('courseMegaForm');
    let formData = new FormData(form);
    
    if(!document.getElementById('course_title').value.trim()) {
        alert("Course title required!");
        return;
    }

    let btn = event.target;
    let originalText = btn.innerHTML;
    btn.innerHTML = "Saving...";
    btn.disabled = true;

    fetch('add_course_logic_ajax.php', { 
        method: 'POST', 
        body: formData 
    })
    .then(r => r.json())
    .then(data => {
        btn.innerHTML = originalText;
        btn.disabled = false;
        if(data.success) {
            current_course_id = data.course_id;
            document.getElementById('saved_course_id').value = current_course_id;
            loadUnits(current_course_id);
            changeStep(2);
        } else {
            alert("Error: " + (data.message || 'Unknown error'));
        }
    })
    .catch(err => {
        btn.innerHTML = originalText;
        btn.disabled = false;
        console.error('Save error:', err);
        alert("Network error. Please try again.");
    });
}

function addUnit() {
    let title = document.getElementById('unit_title_input').value.trim();
    if(!title || !current_course_id) {
        alert("Enter unit title and save course first!");
        return;
    }

    let formData = new FormData();
    formData.append('add_unit', '1');
    formData.append('course_id', current_course_id);
    formData.append('unit_title', title);

    document.getElementById('unit_title_input').value = '';
    
    fetch('curriculum_logic_ajax.php', { method: 'POST', body: formData })
    .then(r => r.text())
    .then(html => {
        if(html.trim() && !html.includes('error')) {
            document.getElementById('unit-list').insertAdjacentHTML('beforeend', html);
        } else {
            alert('Error adding unit: ' + html);
        }
    })
    .catch(err => {
        alert('Network error adding unit');
        console.error(err);
    });
}

// Unit edit mate
function editUnitPrompt(id) {
    let currentTitle = document.getElementById('unit-title-text-' + id).textContent.trim();
    Swal.fire({
        title: 'Update Unit Title',
        input: 'text',
        inputValue: currentTitle,
        showCancelButton: true,
        confirmButtonText: 'Update'
    }).then((result) => {
        if (result.isConfirmed) {
            let formData = new FormData();
            formData.append('update_unit', '1');
            formData.append('unit_id', id);
            formData.append('unit_title', result.value);
            fetch('curriculum_logic_ajax.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    document.getElementById('unit-title-text-' + id).textContent = data.unit_title;
                    Swal.fire('Updated!', '', 'success');
                }
            });
        }
    });
}

function addLessonPrompt(unitId) {
    if(!current_course_id) {
        alert('Please save course first!');
        return;
    }
    
    document.getElementById('modalTitle').textContent = "Add Lesson";
    document.getElementById('modal_unit_id').value = unitId;
    document.getElementById('modal_lesson_id').value = ""; 
    document.getElementById('modal_existing_url').value = "";
    document.getElementById('modal_lesson_title').value = ""; 
    document.getElementById('modal_lesson_url').value = "";
    document.getElementById('modal_lesson_file').value = "";
    document.getElementById('modal_content_type').value = "video";
    toggleInputType(); 

    new bootstrap.Modal(document.getElementById('lessonModal')).show();
}

// Lesson edit mate
function editLessonPrompt(lessonId, unitId) {
    let fullText = document.getElementById('lesson-title-text-' + lessonId).textContent;
    let title = fullText.substring(fullText.indexOf('] ') + 2);
    let type = document.getElementById('lesson-type-val-' + lessonId).value;
    let url = document.getElementById('lesson-url-val-' + lessonId).value;

    document.getElementById('modalTitle').textContent = "Edit Lesson";
    document.getElementById('modal_unit_id').value = unitId;
    document.getElementById('modal_lesson_id').value = lessonId;
    document.getElementById('modal_lesson_title').value = title;
    document.getElementById('modal_content_type').value = type;
    document.getElementById('modal_lesson_url').value = url;
    
    toggleInputType();
    new bootstrap.Modal(document.getElementById('lessonModal')).show();
}

function toggleInputType() {
    let type = document.getElementById('modal_content_type').value;
    let urlDiv = document.getElementById('url_input_div');
    let fileDiv = document.getElementById('file_input_div');
    
    if(type === 'file') {
        urlDiv.style.display = 'none';
        fileDiv.style.display = 'block';
    } else {
        urlDiv.style.display = 'block';
        fileDiv.style.display = 'none';
    }
}

function submitLesson() {
    let lessonId = document.getElementById('modal_lesson_id').value;
    let unitId = document.getElementById('modal_unit_id').value;
    let title = document.getElementById('modal_lesson_title').value.trim();
    let type = document.getElementById('modal_content_type').value;

    if (!title) {
        alert("Lesson title required");
        return;
    }
    if (!current_course_id) {
        alert("Course not saved!");
        return;
    }

    let formData = new FormData();
    formData.append('lesson_title', title);
    formData.append('content_type', type);

    if (lessonId) {
        formData.append('update_lesson', '1');
        formData.append('lesson_id', lessonId);
    } else {
        formData.append('add_lesson', '1');
        formData.append('unit_id', unitId);
    }

    if (type === 'file') {
        let fileInput = document.getElementById('modal_lesson_file');
        let existingUrl = document.getElementById('modal_existing_url').value.trim();

        if (fileInput.files.length > 0) {
            formData.append('lesson_file', fileInput.files[0]);
        } else if (!lessonId || !existingUrl) {
            alert(lessonId ? "No file selected" : "Please select a file for new lesson");
            return;
        } else {
            formData.append('lesson_url', existingUrl);
        }
    } else {
        let url = document.getElementById('modal_lesson_url').value.trim();
        if (!url) {
            alert("Please enter video URL");
            return;
        }
        formData.append('lesson_url', url);
    }

    fetch('curriculum_logic_ajax.php', { method: 'POST', body: formData })
    .then(r => r.text())
    .then(result => {
        // વધારાની સ્પેસ દૂર કરવા માટે trim()
        let res = result.trim(); 
        
        if(res === 'success') {
            loadUnits(current_course_id);
            bootstrap.Modal.getInstance(document.getElementById('lessonModal')).hide();
        } else {
            // જો એરર આવે તો કોન્સોલમાં ચેક કરો કે PHP શું મોકલે છે
            console.log("Raw Response:", res); 
            alert("Error: " + res);
        }
    })
    .catch(err => alert("Network error"));
}

function previewContent(url, type) {
    if(!url || url.trim() === "") {
        alert("No content available!");
        return;
    }

    if(type === 'video') {
        let videoId = "";
        if(url.includes('v=')) videoId = url.split('v=')[1].split('&')[0];
        else if(url.includes('youtu.be/')) videoId = url.split('youtu.be/')[1].split('?')[0];
        
        if(videoId) {
            window.open(`https://www.youtube.com/embed/${videoId}?autoplay=1`, '_blank');
        } else {
            window.open(url, '_blank');
        }
    } else {
        let fullPath = url.startsWith('http') ? url : '../' + url;
        window.open(fullPath, '_blank');
    }
}

function loadUnits(id) {
    if(!id) return;
    
    let formData = new FormData();
    formData.append('fetch_curriculum', '1');
    formData.append('course_id', id);
    
    fetch('curriculum_logic_ajax.php', { method: 'POST', body: formData })
    .then(r => r.text())
    .then(html => {
        document.getElementById('unit-list').innerHTML = html.trim();
    })
    .catch(err => console.error('Load units error:', err));
}

function publishNow() {
    if(!current_course_id) return;
    
    Swal.fire({
        title: 'Publish Course?',
        text: "Are you sure you want to make this course live?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, Publish!'
    }).then((result) => {
        if (result.isConfirmed) {
            let formData = new FormData();
            formData.append('publish_course', '1');
            formData.append('course_id', current_course_id);

            fetch('finalize_course_ajax.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if(data.success) {
                    Swal.fire({
                        title: 'Published!',
                        text: 'Your course has been published successfully.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = "dashboard.php";
                    });
                } else {
                    Swal.fire({
                        title: 'Publish Failed',
                        text: data.message || 'Unknown error occurred.',
                        icon: 'error'
                    });
                }
            })
            .catch(err => {
                Swal.fire({
                    title: 'Error',
                    text: 'Network error occurred while publishing.',
                    icon: 'error'
                });
            });
        } else {
            // User canceled publish, offer to save as draft
            Swal.fire({
                title: 'Save as Draft?',
                text: 'Do you want to save this course as a draft?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Save as Draft',
                cancelButtonText: 'No, Discard'
            }).then((draftResult) => {
                if (draftResult.isConfirmed) {
                    // Since the course is already saved, just redirect to dashboard
                    window.location.href = "dashboard.php";
                }
                // If no, do nothing, stay on page
            });
        }
    });
}

// Auto-load on page load
document.addEventListener('DOMContentLoaded', function() {
    if(current_course_id) {
        loadUnits(current_course_id);
    }
});

// Unit Delete Function Update
function deleteUnit(unitId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "All related units and lessons will be permanently deleted!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, Delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            let formData = new FormData();
            formData.append('delete_unit', '1');
            formData.append('unit_id', unitId);

            // FIX: File path barabar check karo. 
            // Jo tame curriculum_logic_ajax.php vapro cho toh e j lakho.
            fetch('curriculum_logic_ajax.php', { 
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('File not found (404)');
                }
                return response.text();
            })
            .then(data => {
                if (data.trim() === 'success') {
                    document.getElementById('unit-' + unitId).remove();
                    Swal.fire('Deleted!', 'Unit has been removed.', 'success');
                } else {
                    Swal.fire('Error!', 'Server error: ' + data, 'error');
                }
            })
            .catch(error => {
                // Have aya 404 error pakday jase
                Swal.fire('404 Error!', 'The file curriculum_logic_ajax.php was not found on this server.', 'error');
            });
        }
    });
}

// Lesson Delete Function Update
function deleteLesson(lessonId) {
    Swal.fire({
        title: 'Delete Lesson?',
        text: "Are you sure you want to delete this lesson? This action cannot be undone!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            let formData = new FormData();
            formData.append('delete_lesson', '1');
            formData.append('lesson_id', lessonId);

            fetch('curriculum_logic_ajax.php', { method: 'POST', body: formData })
            .then(r => r.text())
            .then(res => {
                if (res.trim() === 'success') {
                    Swal.fire('Deleted!', 'Lesson removed.', 'success');
                    loadUnits(current_course_id);
                } else {
                    Swal.fire('Error!', 'Delete failed!', 'error');
                }
            });
        }
    })
}
</script>
</body>
</html>