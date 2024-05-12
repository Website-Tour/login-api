<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/jwtHandler.php';
require_once __DIR__ . '/sendJson.php';
require_once __DIR__ . '/function.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') :
    
    $data = json_decode(file_get_contents('php://input'));

    if (
        !isset($data->email) ||
        empty(trim($data->email))
    ) :
        sendJson(
            422,
            'Please fill all the required fields & None of the fields should be empty.',
            ['required_fields' => ['email']]
        );
    endif;


    $email = mysqli_real_escape_string($connection, trim($data->email));
    // $password = trim($data->password);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) :
        sendJson(422, 'Invalid Email Address!');
    endif;

    $sql = "SELECT * FROM `users` WHERE `email`='$email'";
    $query = mysqli_query($connection, $sql);
    $row = mysqli_fetch_array($query, MYSQLI_ASSOC);
    if ($row === null) sendJson(404, 'User not found! (Email is not registered)');
    // if (!password_verify($password, $row['password'])) sendJson(401, 'Incorrect Password!');
    reset_password($email);
    sendJson(200, 'Please check your email for the reset password link');
endif;

sendJson(405, 'Invalid Request Method. HTTP method should be POST');