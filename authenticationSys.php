<?php
    ob_start();
    session_start();
    include ('database.php');

    if(isset($_POST['login'])){
        // get form information
        $email = $_POST['email'];
        $password = $_POST['password'];
    
        // check if email exists in database
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?"); // preparation 
        $stmt->bind_param('s', $email); // subtitute ? with variable
        $stmt->execute(); 
        $qrySel = $stmt->get_result();

        // if it doesnt: send error
        if($qrySel->num_rows === 0){
            echo "Account credentials are wrong";
        }

        // else check if password matches password from database        
        else{
            $account = $qrySel->fetch_assoc();

            // if it doesnt match: send error
            if (!password_verify($password, $account['password'])){
                echo "Account credentials are wrong";
            }
            else{
                // else save account details to session
                $_SESSION['userID'] = $userID =  $account['userID'];
                $accountRole = $account['userType'];

                // get user role ID
                switch($accountRole){
                    case 'Admin': 
                        $stmt = $conn->prepare("SELECT adminID FROM admin WHERE userID = ?");
                        break;
                    case 'Instructor':
                        $stmt = $conn->prepare("SELECT instID FROM instructor WHERE userID = ?");
                        break;
                    case 'Student': 
                        $stmt = $conn->prepare("SELECT studentID FROM student WHERE userID = ?");
                        break;
                }

                $stmt->bind_param("s", $userID);
                $stmt->execute();
                $res = $stmt->get_result();
                $row = $res->fetch_assoc();
                $_SESSION['roleID'] = array_values($row)[0];
                
                // redirect according to role
                switch($accountRole){
                    case 'Admin': 
                        redirect("admin.php");
                        break;
                    case 'Instructor':
                        redirect("instructor.php"); 
                        break;
                    case 'Student':
                        redirect("Location: student.php"); 
                        break;
                }
            }
        }
    }
    
    if(isset($_POST['register'])){
        // get form data
        debug_console("Getting form data");

        $usertype = $_POST['role'];
        $username = $_POST['display_name'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $contact = $_POST['contact'];
        $firstName = $_POST['first_name'];
        $lastName = $_POST['last_name']; 
        $dateOfBirth = $_POST['birth_year']."-".$_POST['birth_month']."-".$_POST['birth_day'];
        $sex = $_POST['gender'];
        $activebool = true;
        $userID = generateID("U",9);

        // check if Email is already registered
        debug_console("Checking user email");

        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?"); // preparation 
        $stmt->bind_param('s', $email); // subtitute ? with variable
        $stmt->execute(); 
        $qrySel = $stmt->get_result();

        // if it does: send error
        if($qrySel->num_rows > 0){
            debug_console("Email is already registered");
            redirect('index.php');
            exit;
        }

        else{
            // check role
            debug_console("Checking role");

            switch($usertype){
                case "Administrator":

                    debug_console("Adding to Administrator: ".$userID);

                    //get admin info
                    $adminID = generateID("A",9);
                    $adminToken = $_POST['token'];
                    debug_console("Admin Token: ".$adminToken);

                    //check adminToken 
                    debug_console("checking Admin Token");

                    $stmt = $conn->prepare("SELECT adminTokenID FROM admin WHERE adminTokenID = ?"); 
                    $stmt->bind_param('s', $adminToken);
                    $stmt->execute(); 
                    $qrySel = $stmt->get_result();

                    if($qrySel->num_rows >= 1){ // Check if token is occupied
                        debug_console($adminToken." is occupied");
                        break;
                    }

                    // inserts admin
                    insertUser(
                        $conn,
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
                        $activebool);

                    $stmt = $conn->prepare("INSERT INTO admin (adminID, adminTokenID, userID) VALUES (?,?,?)"); // preparation 
                    $stmt->bind_param('sss', $adminID, $adminToken, $userID);
                    debug_console("insert: ".$stmt->execute());
                    break;

                case "Instructor":
                    debug_console("Adding to Instructor: ".$userID);

                    $instID = generateID("I",9);

                    insertUser(
                        $conn,
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
                        $activebool);

                    $stmt = $conn->prepare("INSERT INTO instructor (instID, userID) VALUES (?,?)"); // preparation 
                    $stmt->bind_param('ss', $instID, $userID);
                    debug_console("insert: ".$stmt->execute());
                    break;

                case "Student":
                    debug_console("Adding to Student: ".$userID);

                    $studentID = generateID("S",9);

                    insertUser(
                        $conn,
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
                        $activebool);

                    $stmt = $conn->prepare("INSERT INTO student (studentID, userID) VALUES (?,?)"); // preparation 
                    $stmt->bind_param('ss', $studentID, $userID);
                    debug_console("insert: ".$stmt->execute());
                    break;
            }

            // redirect to login
            debug_console("Redirecting to login");
            redirect('index.php');
            exit;
        }
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
        echo "<script type='text/javascript'>
            setTimeout(function() {
                window.location.href = '$url';
            }, 2000);
        </script>";
    }
?>
