<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: *');
header('Access-Control-Allow-Methods:  PUT, GET, POST, DELETE, OPTIONS');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');
header('Access-Control-Max-Age: 86400');

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/sendJson.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') :
    $data = json_decode(file_get_contents('php://input'));
    if (
        !isset($data->ten) ||
        !isset($data->email) ||
        !isset($data->phone) ||
        !isset($data->password) ||
        empty(trim($data->ten)) ||
        empty(trim($data->email)) ||
        empty(trim($data->phone)) ||
        empty(trim($data->password))
    ) :
        sendJson(
            422,
            'Please fill all the required fields & None of the fields should be empty.',
            ['required_fields' => ['ten', 'email', 'phone', 'password']]
        );
    endif;

    $ten = mysqli_real_escape_string($connection, htmlspecialchars(trim($data->ten)));
    $email = mysqli_real_escape_string($connection, trim($data->email));
    $phone = trim($data->phone);
    $password = trim($data->password);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) :
        sendJson(422, 'Invalid Email Address!');

    elseif (strlen($password) < 8) :
        sendJson(422, 'Your password must be at least 8 characters long!');

    elseif (strlen($ten) < 3) :
        sendJson(422, 'Your ten must be at least 3 characters long!');

    endif;

    $hash_password = password_hash($password, PASSWORD_DEFAULT);
    $sql = "SELECT `email` FROM `users` WHERE `email`='$email'";
    $query = mysqli_query($connection, $sql);
    $row_num = mysqli_num_rows($query);

    if ($row_num > 0) sendJson(422, 'This E-mail already in use!');

    $sql = "INSERT INTO `users`(`ten`,`email`,`phone`,`password`) VALUES('$ten','$email','$phone','$hash_password')";
    $query = mysqli_query($connection, $sql);
    if ($query) sendJson(201, 'You have successfully registered.');
    sendJson(500, 'Something going wrong.');
endif;

sendJson(405, 'Invalid Request Method. HTTP method should be POST');