<?php include 'includes/header.php'; ?>

<section class="hero-section text-white d-flex align-items-center" style="background: linear-gradient(135deg, #6c63ff 0%, #3f3d56 100%); height: 500px;">
    <div class="container text-center">
        <h1 class="display-3 fw-bold mb-3">Master Your Skills with <span style="color: #ffd700;">LearnsDecode</span></h1>
        <p class="lead mb-5">Decode your future with our expert-led online courses.</p>
        
        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="input-group input-group-lg shadow-lg">
                    <input type="text" id="courseSearch" class="form-control border-0" placeholder="What do you want to learn today?" style="border-radius: 50px 0 0 50px; padding-left: 30px;">
                    <button class="btn btn-warning fw-bold px-4" style="border-radius: 0 50px 50px 0;">Search</button>
                </div>
                <div id="searchResult" class="list-group mt-2 text-start shadow" style="position: absolute; z-index: 1000; width: 55%;"></div>
            </div>
        </div>
    </div>
</section>

<div class="container my-5">
    <h3 class="fw-bold mb-4 text-center">Top Categories</h3>
    <div class="row g-4 text-center">
        <div class="col-md-3">
            <div class="card p-4 border-0 shadow-sm hover-effect">
                <div class="display-5 text-primary mb-2">💻</div>
                <h5>Web Development</h5>
            </div>
        </div>
        </div>
</div>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">Featured Courses</h3>
        <a href="courses.php" class="text-primary fw-bold text-decoration-none">View All Courses →</a>
    </div>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <img src="https://via.placeholder.com/400x250" class="card-img-top" alt="Course Image">
                <div class="card-body">
                    <span class="badge bg-light text-primary mb-2">Beginner</span>
                    <h5 class="card-title fw-bold">Introduction to PHP 8.x</h5>
                    <p class="text-muted small">By Instructor Name</p>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <span class="h5 fw-bold text-primary mb-0">Free</span>
                        <a href="course_details.php" class="btn btn-outline-primary btn-sm">Explore</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-effect:hover { transform: translateY(-10px); transition: 0.3s; cursor: pointer; background-color: #f0f0ff; }
</style>

<?php include 'includes/footer.php'; ?>