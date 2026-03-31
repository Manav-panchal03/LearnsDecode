<?php 
session_start();
require '../config/config.php'; 
include 'includes/header.php'; 
?>

<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

<div class="container-fluid py-4" style="background: #f0f2f9; min-height: 100vh;">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-5" data-aos="fade-down">
            <div>
                <h2 class="fw-bold text-dark"><i class="fas fa-users text-primary me-2"></i> Enrolled <span class="text-primary">Students</span></h2>
                <p class="text-muted">Monitor all active student admissions and course enrollments.</p>
            </div>
            <div class="search-box">
                <input type="text" id="enrollSearch" class="form-control rounded-pill px-4 shadow-sm border-0" placeholder="Search Student, Course or Email..." style="width: 350px; height: 45px;">
            </div>
        </div>

        <div class="row g-4" id="enrollmentContainer">
            </div>
    </div>
</div>

<div class="modal fade" id="enrollDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div id="modalData"></div>
        </div>
    </div>
</div>

<style>
    .enroll-card { 
        transition: all 0.3s ease; 
        border: none !important; 
        background: white;
        border-radius: 20px;
        overflow: hidden;
    }
    .enroll-card:hover { 
        transform: translateY(-8px); 
        box-shadow: 0 12px 25px rgba(0,0,0,0.1) !important; 
    }
    .course-tag {
        font-size: 0.75rem;
        background: #eef2ff;
        color: #6c63ff;
        padding: 5px 12px;
        border-radius: 50px;
        font-weight: 600;
    }
    .payment-status {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 5px;
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<script>
    AOS.init({ duration: 800, once: true });

    $(document).ready(function() {
        fetchEnrollments('');

        $('#enrollSearch').on('keyup', function() {
            fetchEnrollments($(this).val());
        });
    });

    function fetchEnrollments(query) {
        $.ajax({
            url: '../api/fetch_enrolled_students.php',
            method: 'POST',
            data: { search: query },
            success: function(data) {
                $('#enrollmentContainer').html(data);
                AOS.refresh();
            }
        });
    }

    function viewEnrollDetails(card) {
        const name = card.dataset.name;
        const email = card.dataset.email;
        const details = JSON.parse(card.dataset.details || '{}');
        const courses = details.courses || [];
        const dates = details.dates || [];
        const amounts = details.amounts || [];
        const txids = details.txids || [];
        const statuses = details.statuses || [];

        let courseRows = '';
        let totalAmount = 0;
        for (let i = 0; i < courses.length; i++) {
            const status = statuses[i] || 'pending';
            const amount = parseFloat(amounts[i] || 0);
            const txid = txids[i] || '-';
            const dateRaw = dates[i] || '';
            const date = dateRaw ? new Date(dateRaw).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';
            const statusBadge = (status === 'completed') ? 'bg-success text-dark' : 'bg-warning text-dark';
            const statusColor = (status === 'completed') ? '#05cd99' : '#ffb800';

            totalAmount += amount;
            courseRows += `
                <div class="p-3 rounded-3 mb-3" style="background: ${statusColor}15; border: 1px solid ${statusColor}22;">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="fw-bold mb-1">${courses[i]}</h6>
                            <small class="text-muted">Enrolled on ${date}</small>
                        </div>
                        <span class="badge ${statusBadge} text-uppercase">${status}</span>
                    </div>
                    <div class="d-flex justify-content-between small text-muted">
                        <div>Amount: ₹${amount.toFixed(2)}</div>
                        <div>TXID: ${txid}</div>
                    </div>
                </div>`;
        }

        const content = `
            <div class="modal-header bg-primary text-white p-4 border-0">
                <div>
                    <h5 class="modal-title fw-bold"><i class="fas fa-info-circle me-2"></i> Enrollment Summary</h5>
                    <small class="text-muted">${courses.length} enrolled course${courses.length === 1 ? '' : 's'} for this student.</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 70px; height: 70px; font-size: 1.8rem; font-weight: bold; color: #6c63ff; border: 3px solid #6c63ff;">
                        ${name.charAt(0).toUpperCase()}
                    </div>
                    <h4 class="fw-bold mb-0">${name}</h4>
                    <p class="text-muted small">${email}</p>
                </div>
                <div class="list-group list-group-flush border-top mb-4">
                    <div class="list-group-item d-flex justify-content-between py-3 border-0">
                        <span class="text-muted">Total Courses</span>
                        <span class="fw-bold text-primary">${courses.length}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between py-3 border-0">
                        <span class="text-muted">Total Amount</span>
                        <span class="fw-bold text-dark">₹${totalAmount.toFixed(2)}</span>
                    </div>
                </div>
                ${courseRows}
            </div>`;

        $('#modalData').html(content);
        new bootstrap.Modal(document.getElementById('enrollDetailModal')).show();
    }
</script>