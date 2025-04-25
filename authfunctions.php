<?php
    include 'database.php';
    
    function generateID($prefix = 'NU', $length = 8){
        return $prefix . generateRandomString($length);
    }

    function generateRandomString($length = 8) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    function insertUser(
        $db,
        $userID,
        $usertype,
        $username,
        $email,
        $password,
        $firstName,
        $lastName,
        $sex,
        $dateOfBirth,
        $contact,
        $activebool
    ){
        debug_console("Inserting to Database");

        $stmt = $db->prepare("INSERT INTO users (
            userID, usertype, username, email, password, firstName, lastName, sex, dateOfBirth, contact, active) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);"
        );

        $stmt->bind_param(
            "ssssssssssi",
            $userID,
            $usertype,
            $username,
            $email,
            $password,
            $firstName,
            $lastName,
            $sex,
            $dateOfBirth,
            $contact,
            $activebool
        );

        if($stmt->execute()){
            debug_console("Successfully inserted");
        }
        else{
            debug_console("Failed insert");
        }
    }

    function debug_console($data) {
        $output = $data;
        if (is_array($output))
            $output = implode(',', $output);

        echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
    }

    function redirect($url){
        debug_console("Debugging to ".$url);
        if ($url == 'Admin.php' || $url == 'Instructor.php' || $url == 'Student.php')
        echo "<div style='
        position: absolute;
        display: flex;
        margin: 20px auto;
        padding: 15px 25px;
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
        border-radius: 8px;
        width: fit-content;
        font-family: Inter, sans-serif;
        font-size: 16px;
        text-align: center;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    '>Logged In Successfully</div>";

        echo "<script type='text/javascript'>
            setTimeout(function() {
                window.location.href = '$url';
            }, 2000);
        </script>";
    }

    function logout(){
        
    }
?>
