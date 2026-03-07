<?php

require 'config/config.php';

// Add approved column to users table for admin approval system
$alter_users = "ALTER TABLE users ADD COLUMN approved TINYINT(1) DEFAULT 1";
$result_alter = mysqli_query($conn, $alter_users);
if ($result_alter) {
    echo "Column 'approved' added to users table successfully.<br>";
} else {
    echo "Error adding column or column already exists: " . mysqli_error($conn) . "<br>";
}

// Set default approved status for existing users
$update_existing = "UPDATE users SET approved = 1 WHERE role != 'admin' OR role IS NULL";
$result_update = mysqli_query($conn, $update_existing);
if ($result_update) {
    echo "Existing non-admin users approved by default.<br>";
} else {
    echo "Error updating existing users: " . mysqli_error($conn) . "<br>";
}

// Set admin users to not approved by default (they need approval)
$update_admins = "UPDATE users SET approved = 0 WHERE role = 'admin'";
$result_admins = mysqli_query($conn, $update_admins);
if ($result_admins) {
    echo "Admin users set to pending approval.<br>";
} else {
    echo "Error updating admin users: " . mysqli_error($conn) . "<br>";
}

// Create admin_requests table for tracking approval requests
$admin_requests = "CREATE TABLE IF NOT EXISTS admin_requests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    request_reason TEXT,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL,
    reviewed_by INT UNSIGNED NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (reviewed_by) REFERENCES users(id)
) ENGINE=InnoDB;";

$result_requests = mysqli_query($conn, $admin_requests);
if ($result_requests) {
    echo "Table 'admin_requests' created successfully.<br>";
} else {
    echo "Error creating table 'admin_requests': " . mysqli_error($conn) . "<br>";
}

echo "<br>Database setup complete!";

?>

// $enrollments="CREATE TABLE enrollments (
//     id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
//     student_id INT UNSIGNED NOT NULL,
//     course_id INT UNSIGNED NOT NULL,
//     enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
//     status ENUM('active','completed','dropped') DEFAULT 'active',
//     UNIQUE KEY student_course_unique (student_id, course_id),
//     FOREIGN KEY (student_id) REFERENCES users(id),
//     FOREIGN KEY (course_id) REFERENCES courses(id)
// ) ENGINE=InnoDB;";

// $resultENROLL = mysqli_query($conn, $enrollments);
// if ($resultENROLL) {
//     echo "Table 'enrollments' created successfully.<br>";
// } else {
//     echo "Error creating table 'enrollments': " . mysqli_error($conn) . "<br>";
// }

// $lession_progress="CREATE TABLE lesson_progress (
//   id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
//   enrollment_id INT UNSIGNED NOT NULL,
//   lesson_id INT UNSIGNED NOT NULL,
//   is_completed TINYINT(1) DEFAULT 0,
//   completed_at TIMESTAMP NULL,
//   UNIQUE KEY enrollment_lesson_unique (enrollment_id, lesson_id),
//   FOREIGN KEY (enrollment_id) REFERENCES enrollments(id),
//   FOREIGN KEY (lesson_id) REFERENCES lessons(id)
// ) ENGINE=InnoDB;";

// $resultPROGRESS = mysqli_query($conn, $lession_progress);
// if ($resultPROGRESS) {
//     echo "Table 'lesson_progress' created successfully.<br>";
// } else {
//     echo "Error creating table 'lesson_progress': " . mysqli_error($conn) . "<br>";
// }

// $quiz_attempts="CREATE TABLE quiz_attempts (
//   id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
//   enrollment_id INT UNSIGNED NOT NULL,
//   quiz_id INT UNSIGNED NOT NULL,
//   score INT UNSIGNED DEFAULT 0,
//   attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
//   FOREIGN KEY (enrollment_id) REFERENCES enrollments(id),
//   FOREIGN KEY (quiz_id) REFERENCES quizzes(id)
// ) ENGINE=InnoDB;";

// $resultQUIZATTEMPT = mysqli_query($conn, $quiz_attempts);
// if ($resultQUIZATTEMPT) {
//     echo "Table 'quiz_attempts' created successfully.<br>";
// } else {
//     echo "Error creating table 'quiz_attempts': " . mysqli_error($conn) . "<br>";
// }


// $messages="CREATE TABLE messages (
//   id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
//   sender_id INT UNSIGNED NOT NULL,
//   receiver_id INT UNSIGNED NOT NULL,
//   course_id INT UNSIGNED NOT NULL,
//   message_text TEXT NOT NULL,
//   sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
//   FOREIGN KEY (sender_id) REFERENCES users(id),
//   FOREIGN KEY (receiver_id) REFERENCES users(id),
//   FOREIGN KEY (course_id) REFERENCES courses(id)
// ) ENGINE=InnoDB;";

// $resultMESSAGE = mysqli_query($conn, $messages);
// if ($resultMESSAGE) {
//     echo "Table 'messages' created successfully.<br>";
// } else {
//     echo "Error creating table 'messages': " . mysqli_error($conn) . "<br>";
// }

// $certificates="CREATE TABLE certificates (
//   id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
//   enrollment_id INT UNSIGNED NOT NULL,
//   certificate_code VARCHAR(50) NOT NULL UNIQUE,
//   issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
//   FOREIGN KEY (enrollment_id) REFERENCES enrollments(id)
// ) ENGINE=InnoDB;";

// $resultCERTIFICATE = mysqli_query($conn, $certificates);
// if ($resultCERTIFICATE) {
//     echo "Table 'certificates' created successfully.<br>";
// } else {
//     echo "Error creating table 'certificates': " . mysqli_error($conn) . "<br>";
// }

//-- 1. Categories Table
// $categories = "CREATE TABLE categories (
//     id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
//     name VARCHAR(100) NOT NULL,
//     icon VARCHAR(50), 
//     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
// ) ENGINE=InnoDB;";

// $categories = mysqli_query($conn, $categories);
// if ($categories) {
//     echo "Table 'categories' created successfully.<br>";
// } else {
//     echo "Error creating table 'categories': " . mysqli_error($conn) . "<br>";
// }

//-- 2. Reviews & Ratings Table
// $reviews = "CREATE TABLE reviews (
//     id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
//     course_id INT UNSIGNED NOT NULL,
//     student_id INT UNSIGNED NOT NULL,
//     rating TINYINT UNSIGNED NOT NULL CHECK (rating BETWEEN 1 AND 5),
//     review_text TEXT,
//     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
//     FOREIGN KEY (course_id) REFERENCES courses(id),
//     FOREIGN KEY (student_id) REFERENCES users(id)
// ) ENGINE=InnoDB;";

// $reviewsTable = mysqli_query($conn, $reviews);
// if ($reviewsTable) {
//     echo "Table 'reviews' created successfully.<br>";
// } else {
//     echo "Error creating table 'reviews': " . mysqli_error($conn) . "<br>";
// }

//-- 3. Wishlist Table
// $wishlist = "CREATE TABLE wishlist (
//     id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
//     user_id INT UNSIGNED NOT NULL,
//     course_id INT UNSIGNED NOT NULL,
//     added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
//     FOREIGN KEY (user_id) REFERENCES users(id),
//     FOREIGN KEY (course_id) REFERENCES courses(id)
// ) ENGINE=InnoDB;";

// $wishlistTable = mysqli_query($conn, $wishlist);
// if ($wishlistTable) {
//     echo "Table 'wishlist' created successfully.<br>";
// } else {
//     echo "Error creating table 'wishlist': " . mysqli_error($conn) . "<br>";
// }

//-- 4. Notifications Table
// $notifications = "CREATE TABLE notifications (
//     id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
//     user_id INT UNSIGNED NOT NULL,
//     message TEXT NOT NULL,
//     is_read TINYINT(1) DEFAULT 0,
//     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
//     FOREIGN KEY (user_id) REFERENCES users(id)
// ) ENGINE=InnoDB;";

// $notificationsTable = mysqli_query($conn, $notifications);
// if ($notificationsTable) {
//     echo "Table 'notifications' created successfully.<br>";
// } else {
//     echo "Error creating table 'notifications': " . mysqli_error($conn) . "<br>";
// }

//-- 1. Table for Units (e.g., Unit 1: Introduction)
// $units = "CREATE TABLE units (
//     id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
//     course_id INT(10) UNSIGNED NOT NULL,
//     unit_title VARCHAR(255) NOT NULL,
//     order_no INT DEFAULT 0,
//     FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
// ) ENGINE=InnoDB;";

// $resultUnits = mysqli_query($conn, $units);
// if ($resultUnits) {
//     echo "Table 'units' created successfully.<br>";
// } else {
//     echo "Error creating table 'units': " . mysqli_error($conn) . "<br>";
// }

// $lessons = "CREATE TABLE lessons (
//     id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
//     unit_id INT(10) UNSIGNED NOT NULL,
//     lesson_title VARCHAR(255) NOT NULL,
//     content_type ENUM('video', 'pdf', 'text') DEFAULT 'video',
//     content_url VARCHAR(500),
//     order_no INT DEFAULT 0,
//     FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE
// ) ENGINE=InnoDB;";

// $resultLessons = mysqli_query($conn, $lessons);
// if ($resultLessons) {
//     echo "Table 'lessons' created successfully.<br>";
// } else {
//     echo "Error creating table 'lessons': " . mysqli_error($conn) . "<br>";
// }
// $instructor_profiles = "CREATE TABLE instructor_profiles (
//     id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
//     user_id INT(10) UNSIGNED NOT NULL,
//     expertise VARCHAR(255) DEFAULT NULL,
//     bio TEXT DEFAULT NULL,
//     profile_pic VARCHAR(255) DEFAULT 'default-avatar.png',
//     facebook_link VARCHAR(255) DEFAULT NULL,
//     linkedin_link VARCHAR(255) DEFAULT NULL,
//     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
//     CONSTRAINT fk_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
// ) ENGINE=InnoDB;";

// $resultInstructorProfiles = mysqli_query($conn, $instructor_profiles);
// if ($resultInstructorProfiles) {
//     echo "Table 'instructor_profiles' created successfully.<br>";
// } else {
//     echo "Error creating table 'instructor_profiles': " . mysqli_error($conn) . "<br>";
// }
?>