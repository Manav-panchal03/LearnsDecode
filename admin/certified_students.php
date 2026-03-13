<?php 
session_start();
require '../config/config.php'; 
include 'includes/header.php'; 
?>

<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

<div class="container-fluid py-4" style="background: #f8f9ff; min-height: 100vh;">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-5" data-aos="fade-down">
            <div>
                <h2 class="fw-bold text-dark"><i class="fas fa-certificate text-warning me-2"></i> Certified <span class="text-primary">Students</span></h2>
                <p class="text-muted">List of approved graduates from LearnsDecode.</p>
            </div>
            <div class="search-box">
                <input type="text" id="certSearch" class="form-control rounded-pill px-4 shadow-sm border-0" placeholder="Search student or course..." style="width: 300px;">
            </div>
        </div>

        <div class="row g-4" id="certifiedContainer">
            </div>
    </div>
</div>

<div class="modal fade" id="studentDetailModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-primary text-white border-0 p-4">
                <h5 class="modal-title fw-bold"><i class="fas fa-user-graduate me-2"></i> Student Achievement</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center" id="modalContent">
                </div>
        </div>
    </div>
</div>

<style>
    .cert-card { transition: 0.4s; cursor: pointer; border: 1px solid #eee !important; background: white; }
    .cert-card:hover { transform: translateY(-10px); box-shadow: 0 15px 30px rgba(108, 99, 255, 0.15) !important; border-color: #6c63ff !important; }
    .avatar-circle { width: 60px; height: 60px; background: #6c63ff; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: bold; margin: 0 auto; }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<script>
    AOS.init({ duration: 800, once: true });

    $(document).ready(function() {
        loadCertified('');
        $('#certSearch').on('keyup', function() {
            loadCertified($(this).val());
        });
    });

    function loadCertified(query) {
        $.ajax({
            url: '../api/fetch_certified_students.php',
            method: 'POST',
            data: { search: query },
            success: function(data) {
                $('#certifiedContainer').html(data);
                AOS.refresh();
            }
        });
    }

    function showStudentInfo(name, course, date, email, certLink) {
        // Build the HTML for the modal
        let html = `
            <div class="avatar-circle" style="width: 75px; height: 75px; background: linear-gradient(135deg, #6c63ff, #3f3d56); font-size: 2rem; margin-bottom: 15px;">
                ${name.charAt(0).toUpperCase()}
            </div>
            <h4 class="fw-bold mb-1">${name}</h4>
            <p class="text-muted mb-4 small">${email}</p>
            <div class="p-3 rounded-3 text-start mb-4" style="background: #f4f7fe; border-left: 4px solid #6c63ff;">
                <div class="mb-2"><strong>Course:</strong> <span class="text-dark">${course}</span></div>
                <div class="mb-2"><strong>Date:</strong> <span class="text-dark">${date}</span></div>
                <div><strong>Status:</strong> <span class="badge bg-success">Verified Graduate</span></div>
            </div>
            <a href="${certLink}" target="_blank" class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow-sm">
                <i class="fas fa-file-download me-2"></i> View Certificate
            </a>
        `;
        
        $('#modalContent').html(html);
        
        // Show Modal using Bootstrap's JavaScript API
        var myModal = new bootstrap.Modal(document.getElementById('studentDetailModal'));
        myModal.show();
    }
</script>