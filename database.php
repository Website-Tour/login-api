<?php
$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'tour5';
$connection = mysqli_connect($hostname, $username, $password, $database);

if (mysqli_connect_errno()) {
    echo "Connection Failed - " . mysqli_connect_error();
    exit;
}