<?php
session_start();
require '../config/config.php';

$instructor_id = $_SESSION['user_id'];

// Publish Logic
if(isset($_GET['publish_id'])){
    $pid = $_GET['publish_id'];
    mysqli_query($conn, "UPDATE quizzes SET status = 'published' WHERE id = $pid");
    header("Location: manage_quizzes.php?msg=published");
}

// Fetch Quizzes with question count
$sql = "SELECT q.*, c.title as course_title, c.thumbnail as course_image, 
        (SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = q.id) as total_ques 
        FROM quizzes q 
        JOIN courses c ON q.course_id = c.id 
        WHERE c.instructor_id = $instructor_id 
        ORDER BY q.id DESC";
$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Quizzes | LearnsDecode</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f7f6; }
        .quiz-row { background: white; border-radius: 15px; transition: 0.3s; border: 1px solid #eee; }
        .quiz-row:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
        .status-badge { font-size: 0.75rem; padding: 5px 12px; border-radius: 50px; }
        .bg-draft { background: #fff4e5; color: #ff9800; }
        .bg-published { background: #e6fffa; color: #38b2ac; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="fas fa-tasks text-primary me-2"></i>Manage <span class="text-primary">Quizzes</span></h2>
        <a href="add_quiz.php" class="btn btn-primary rounded-pill px-4"><i class="fas fa-plus me-2"></i>Create New Quiz</a>
    </div>

    <div class="row g-3">
        <?php while($row = mysqli_fetch_assoc($result)): ?>
            <div class="col-12 quiz-row p-3 mb-2 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="quiz-icon">
                        <?php if(!empty($row['course_image'])): ?>
                            <img src="../uploads/thumbnails/<?= $row['course_image'] ?>" alt="Quiz Icon" style="width: 50px; height: 50px; border-radius: 10px; object-fit: cover;">
                        <?php else: ?>
                            <div class="default-icon bg-light d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; border-radius: 10px;">
                                <i class="fas fa-brain text-primary"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0"><?= $row['title'] ?></h6>
                        <small class="text-muted"><?= $row['course_title'] ?> • <?= $row['total_ques'] ?> Questions</small>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-4">
                    <span class="status-badge <?= ($row['status'] == 'published') ? 'bg-published' : 'bg-draft' ?>">
                        <?= strtoupper($row['status']) ?>
                    </span>

                    <div class="actions">
                        <a href="review_quiz.php?quiz_id=<?= $row['id'] ?>" class="btn btn-sm btn-light border" title="Edit Questions"><i class="fas fa-list"></i></a>
                        
                        <?php if($row['status'] == 'draft'): ?>
                            <a href="manage_quizzes.php?publish_id=<?= $row['id'] ?>" class="btn btn-sm btn-success ms-2 px-3" onclick="return confirm('Publish this quiz for students?')">
                                <i class="fas fa-paper-plane me-1"></i> Publish
                            </a>
                        <?php else: ?>
                            <button class="btn btn-sm btn-outline-secondary ms-2" disabled><i class="fas fa-check"></i> Live</button>
                        <?php endif; ?>

                        <a href="delete_quiz.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-light text-danger border ms-2"><i class="fas fa-trash"></i></a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

</body>
</html>