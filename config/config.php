<?php

// base URL used for generating absolute links; adjust if the project lives under a different path
// note: spaces must be URL-encoded by the browser automatically, so you can keep them here
if(!defined('BASE_URL')){
    define('BASE_URL', '/Learns Decode/LearnsDecode');
}

$severname = "localhost";
$username = "root";
$password = "";
$databse = "LMS";

$conn = mysqli_connect($severname, $username, $password, $databse);

if(!$conn){
    die("Connection failed: " . mysqli_connect_error());
}

?>