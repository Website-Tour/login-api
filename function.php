<?php
    //Import PHPMailer classes into the global namespace
    //These must be at the top of your script, not inside a function
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    require 'vendor/autoload.php';
    define('host', '127.0.0.1');
    define('user','root');
    define('pass','');
    define('db','tour5');

    function open_database() {
        $conn = new mysqli(host, user, pass, db);
        if ($conn->connect_error) {
            die('Connect error: '. $conn->connect_error);
        }
        return $conn;
    }
    function is_email_exists($email) {
        $sql = "SELECT * FROM `users` WHERE `email`=?";
        $conn = open_database();
        $stm  = $conn->prepare($sql);
        $stm->bind_param("s", $email);
        
        // Execute the statement
        if (!$stm->execute()) {
            die("Query error: " . $stm->error);
        }
        $result = $stm->get_result();
        if ($result->num_rows > 0) {
            return true;
        } else {
            return false;
        }
        
    }
    function send_reset_email($email, $token) {
        

        //Create an instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);

        try {
            //Server settings
            // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = 'vynguyen717@gmail.com';                     //SMTP username
            $mail->Password   = 'nwco peds yzwv gxke';                               //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom('vynguyen717@gmail.com', 'Quản Trị Viên');
            $mail->addAddress($email, 'Người nhận');     //Add a recipient
            // $mail->addAddress('ellen@example.com');               //Name is optional
            // $mail->addReplyTo('info@example.com', 'Information');
            // $mail->addCC('cc@example.com');
            // $mail->addBCC('bcc@example.com');

            //Attachments
            // $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
            // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'Khôi phục mật khẩu của bạn';
            $mail->CharSet = 'UTF-8';
            $mail->Body    = "Click <a href='http://localhost/login-api/reset.php?email=$email&token=$token'>vào đây</a> để khôi phục mật khẩu của bạn";
            // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mail->send();
            // echo 'Message has been sent';
            return true;
        } catch (Exception $e) {
            // echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            return false;
        }
    }
    function reset_password($email) {
        if (!is_email_exists( $email )) {
            return array("code"=> 1,"error"=> "Email does not exist");
        }
        $token = md5( $email.'+'.random_int(1000, 2000));
        $sql = "UPDATE `reset_token` SET `token` =? WHERE `email`=?";
        $conn = open_database();
        $stm = $conn->prepare($sql);
        $stm->bind_param("ss", $token, $email);
        if (!$stm->execute()) { 
            return array("code"=> 2,"error"=> "Cannot execute command");
        }
        if ($stm->affected_rows == 0) {
            //chua co dong nao cua mail nay, them vao dong moi
            $exp = time() + 3600*24; //het han sau 24h
            $sql = "INSERT INTO `reset_token` VALUES (?,?,?)";
            $stm = $conn->prepare($sql);
            $stm->bind_param("ssi", $email, $token, $exp);
            if (!$stm->execute()) { 
                return array("code"=> 1,"error"=> "Cannot execute command");
            }
        }
        // Update the password in the account table
        
        //chen thanh cong/update thanh cong
        send_reset_email($email, $token);
        // return array("code" => 0, "message" => "Password updated successfully");    
    }

    function update_password($email, $new_password) {
        $hash = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "UPDATE `users` SET `password` = ? WHERE `email` = ?";
        $conn = open_database();
        $stm = $conn->prepare($sql);
        $stm->bind_param("ss", $hash, $email);
        if (!$stm->execute()) {
            return array("code" => 3, "error" => "Cannot update password");
        }
        return array("code" => 0, "message" => "Password updated successfully");    
    }