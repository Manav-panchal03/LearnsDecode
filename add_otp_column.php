<?php
require 'config/config.php';
$query = "ALTER TABLE users ADD COLUMN otp VARCHAR(10) DEFAULT NULL";
if(mysqli_query($conn, $query)){
    echo "OTP column added successfully.";
} else {
    echo "Column already exists or error: " . mysqli_error($conn);
}
?>