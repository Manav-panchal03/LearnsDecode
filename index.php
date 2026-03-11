<?php 
session_start();
require 'config/config.php'; 
include 'includes/header.php'; 

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

// 1. Categories fetch karva mate
$cat_query = "SELECT * FROM categories ORDER BY id ASC LIMIT 4";
$cat_result = mysqli_query($conn, $cat_query);

// 2. Initial courses fetch karva mate (Jo tame query badlavva na mangta ho to)
$course_query = "SELECT c.*, u.name as instructor_name, 
                (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id AND e.student_id = '$user_id') as is_purchased
                FROM courses c 
                JOIN users u ON c.instructor_id = u.id 
                WHERE c.status = 'published' OR c.status = 'active'
                ORDER BY c.id DESC LIMIT 6";
$course_result = mysqli_query($conn, $course_query);
?>

<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

<section class="hero-section text-white d-flex align-items-center" style="background: linear-gradient(135deg, #6c63ff 0%, #3f3d56 100%); height: 500px;">
    <div class="container text-center">
        <h1 class="display-3 fw-bold mb-3" data-aos="fade-up" data-aos-duration="1000">Master Your Skills with <span style="color: #ffd700;">LearnsDecode</span></h1>
        <p class="lead mb-5" data-aos="fade-up" data-aos-delay="200" data-aos-duration="1000">Decode your future with our expert-led online courses.</p>
        
        <div class="row justify-content-center" data-aos="zoom-in" data-aos-delay="400" data-aos-duration="800">
            <div class="col-md-7">
                <div class="input-group input-group-lg shadow-lg">
                    <input type="text" id="courseSearch" class="form-control border-0" placeholder="What do you want to learn today?" style="border-radius: 50px 0 0 50px; padding-left: 30px;">
                    <button class="btn btn-warning fw-bold px-4" style="border-radius: 0 50px 50px 0;">Search</button>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="container my-5">
    <h3 class="fw-bold mb-4 text-center" data-aos="fade-right">Top Categories</h3>
    <div class="row g-4 text-center">
        <?php if($cat_result && mysqli_num_rows($cat_result) > 0): $delay = 100; ?>
            <?php while($cat = mysqli_fetch_assoc($cat_result)): ?>
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="<?= $delay; ?>" onclick="filterCategory('<?= $cat['name']; ?>')">
                    <div class="card p-4 border-0 shadow-sm hover-effect">
                        <div class="display-5 text-primary mb-2">
                            <i class="<?= $cat['icon']; ?>"></i>
                        </div>
                        <h5><?= $cat['name']; ?></h5>
                    </div>
                </div>
            <?php $delay += 100; endwhile; ?>
        <?php endif; ?>
    </div>
</div>

<div class="container my-5" id="courseListSection">
    <div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-left">
        <h3 class="fw-bold" id="featuredTitle">Featured Courses</h3>
        <a href="courses.php" class="text-primary fw-bold text-decoration-none">View All Courses →</a>
    </div>
    
    <div class="row g-4" id="courseContainer">
        <?php if($course_result && mysqli_num_rows($course_result) > 0): $c_delay = 100; ?>
            <?php while($row = mysqli_fetch_assoc($course_result)): 
                $purchased = ($row['is_purchased'] > 0);
            ?>
                <div class="col-md-4" data-aos="zoom-in-up" data-aos-delay="<?= $c_delay; ?>">
                    <div class="card border-0 shadow-sm h-100 hover-card overflow-hidden">
                        <?php if($purchased): ?>
                            <div class="purchased-badge">Enrolled</div>
                        <?php endif; ?>
                        
                        <?php $thumb = (!empty($row['thumbnail'])) ? "uploads/thumbnails/".$row['thumbnail'] : "https://via.placeholder.com/400x250"; ?>
                        <div style="height: 200px; overflow: hidden;">
                            <img src="<?= $thumb ?>" class="card-img-top w-100 h-100" alt="Course Image" style="object-fit: cover;">
                        </div>
                        <div class="card-body">
                            <span class="badge bg-light text-primary mb-2"><?= ucfirst($row['level'] ?? 'Beginner') ?></span>
                            <h5 class="card-title fw-bold text-truncate"><?= $row['title'] ?></h5>
                            <p class="text-muted small">By <?= $row['instructor_name'] ?></p>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="h5 fw-bold text-primary mb-0">
                                    <?= ($row['price'] > 0) ? '₹' . number_format($row['price'], 0) : 'Free'; ?>
                                </span>
                                <a href="course_details.php?id=<?= $row['id'] ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">Explore</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php $c_delay += 100; endwhile; ?>
        <?php endif; ?>
    </div>
</div>

<style>
    .hover-effect:hover { transform: translateY(-10px); transition: 0.3s; cursor: pointer; background-color: #f0f0ff; }
    .hover-card { transition: 0.3s; position: relative; }
    .hover-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
    .purchased-badge { position: absolute; top: 10px; right: 10px; background: #05cd99; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; z-index: 5; }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
  AOS.init({ duration: 800, once: true });

  $(document).ready(function() {
      // 1. Real-time Search Logic
      $('#courseSearch').on('keyup', function() {
          let keyword = $(this).val();
          loadCourses(keyword, '');
      });
  });

  // 2. Category Filter Logic
  function filterCategory(catName) {
      $('#featuredTitle').text('Category: ' + catName);
      loadCourses('', catName);
      $('html, body').animate({ scrollTop: $("#courseListSection").offset().top - 50 }, 500);
  }

  function loadCourses(keyword, category) {
      $.ajax({
          url: 'api/fetch_courses_index.php',
          method: 'POST',
          data: { search: keyword, cat: category },
          success: function(data) {
              $('#courseContainer').html(data);
          }
      });
  }
</script>

<?php include 'includes/footer.php'; ?>