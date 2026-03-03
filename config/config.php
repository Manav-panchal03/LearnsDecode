<?php

$severname = "localhost";
$username = "root";
$password = "";
$databse = "LMS";

$conn = mysqli_connect($severname, $username, $password, $databse);

if(!$conn){
    die("Connection failed: " . mysqli_connect_error());
}

?>