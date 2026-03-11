<?php 
session_start();
require 'config/config.php'; 
include 'includes/header.php'; 

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
?>

<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

<div class="container-fluid py-5" style="background: #f4f7fe; min-height: 100vh;">
    <div class="container">
        <div class="row mb-5 align-items-center">
            <div class="col-md-6" data-aos="fade-right">
                <h2 class="fw-bold text-dark">All Online <span class="text-primary">Courses</span></h2>
                <p class="text-muted">Explore our wide range of professional courses to upgrade your skills.</p>
            </div>
            <div class="col-md-6" data-aos="fade-left">
                <div class="input-group shadow-sm bg-white rounded-pill p-1">
                    <input type="text" id="courseSearchMain" class="form-control border-0 bg-transparent ps-4" placeholder="Search by title or category...">
                    <button class="btn btn-primary rounded-pill px-4"><i class="fas fa-search"></i></button>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="row g-4" id="allCourseContainer">
                    </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Card hovering animation */
    .hover-card { 
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1); 
        border: 1px solid #eee !important;
    }
    .hover-card:hover { 
        transform: translateY(-12px) scale(1.02); 
        box-shadow: 0 25px 50px rgba(108, 99, 255, 0.15) !important; 
        border-color: #6c63ff !important; 
    }
    
    /* Smooth loading pulse for images */
    .course-thumb-container img {
        transition: 0.5s;
    }
    .hover-card:hover .course-thumb-container img {
        transform: scale(1.1);
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    // AOS Initialize
    AOS.init({ 
        duration: 1000, 
        once: true,
        easing: 'ease-out-back' 
    });

    $(document).ready(function() {
        // !!! AA LINE MISSING HATI !!!
        // Page load thay tyare badha courses load karva mate
        loadAllCourses(''); 

        // Search input logic
        $('#courseSearchMain').on('keyup', function() {
            let keyword = $(this).val();
            loadAllCourses(keyword);
        });
    });

    function loadAllCourses(keyword) {
        $.ajax({
            url: 'api/fetch_all_courses.php',
            method: 'POST',
            data: { search: keyword },
            beforeSend: function() {
                // Loading effect
                $('#allCourseContainer').css('opacity', '0.5');
            },
            success: function(data) {
                $('#allCourseContainer').html(data).css('opacity', '1');
                AOS.refresh(); 
            },
            error: function() {
                $('#allCourseContainer').html('<p class="text-center text-danger">Error loading courses.</p>');
            }
        });
    }
</script>
<?php include 'includes/footer.php'; ?>