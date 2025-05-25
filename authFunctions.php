<?php
    include 'database.php';

    function destroySession(){
        session_unset(); 
        session_destroy();
    }

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
        if ($url == 'deactive'){
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
            z-index: 4000;
        '>This account is currently deactivated, Please ask an administrator to reactivate this account and log in again.</div>";
            destroySession();
            echo "<script type='text/javascript'>
            setTimeout(function() {
                window.location.href = 'index.php';
            }, 3000);
        </script>";
        }
        else {
            if ($url == 'Admin.php' || $url == 'Instructor.php' || $url == 'Student.php') { // if logging in
                if (isset($_SESSION['accountRole'])) $accountRole = $_SESSION['accountRole'];

                else debug_console("Failed to get accountRole.");

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
                z-index: 1000;
            '>Logged In Successfully, Welcome {$accountRole}.</div>";
                }
            else if ($url == 'index.php'){ // if registration
                echo "<div style='
                position: absolute;
                display: flex;
                top: 45%;
                left: 40%;
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
                z-index: 1000;
            '>Registration successful! You can now log in.</div>";
            }
            echo "<script type='text/javascript'>
                setTimeout(function() {
                    window.location.href = '$url';
                }, 3000);
            </script>";
        }
    }

    function sessionCheck(){
        if(!isset($_SESSION['userID'])){
            redirect("index.php");
        }
    }

    function logAction($conn, $userID, $action) {
        $logID = generateID('L',9);
    
        $sql = "INSERT INTO activity (logID,userID,action,dateTimeCreated) VALUES (?,?,?,CURRENT_TIMESTAMP)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sss', $logID, $userID, $action);
        $stmt->execute();
      }
      

    function checkFiles($files) {
        debug_console("Checking Files");
        $permitted = array('txt', 'json'); 
    
        // Check if files are uploaded
        if (!isset($files['name']) || $files['name'][0] === "") {
            debug_console("No files uploaded.");
            return false;
        }
    
        // Validate each file
        foreach ($files['name'] as $key => $fileName) {
            $ext = pathinfo($fileName, PATHINFO_EXTENSION);
            if (!in_array($ext, $permitted)) {
                debug_console("Invalid file type: " . $fileName);
                return false;
            }
        }
        debug_console("All files are valid.");
        return true;
    }
?>
