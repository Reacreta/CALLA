<?php
    session_start();
    require_once 'database.php';

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

                $stmt->bind_param("s",$userID);
                $stmt->execute();
                $_SESSION['roleID'] = $roleID = $stmt->fetch_assoc();
                
                // redirect according to role
                switch($accountRole){
                    case 'Admin': 
                        header("Location: admin.php");
                        break;
                    case 'Instructor':
                        header("Location: instructor.php"); 
                        break;
                    case 'Student':
                        header("Location: student.php"); 
                        break;
                }
            }
        }
    }
    
    if(isset($_POST['register'])){
        // get form data
        $usertype = $_POST['role'];
        $username = $_POST['display_name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $contact = $_POST['contact'];
        $firstName = $_POST['first_name'];
        $lastName = $_POST['last_name']; 
        $dateOfBirth = $_POST['birth_year']."-".$_POST['birth_month']."-".$_POST['birth_day'];
        $sex = $_POST['gender'];
        $activebool = true;
        $userID = generateID("U",9);
        echo $usertype;

        // check if Email is already registered
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?"); // preparation 
        $stmt->bind_param('s', $email); // subtitute ? with variable
        $stmt->execute(); 
        $qrySel = $stmt->get_result();

        // if it does: send error
        if($qrySel->num_rows > 0){
            echo "Email is registered";
            exit();
        }

        else{
            // insert to user
            $stmt = $conn->prepare("INSERT INTO users (
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

            // check role
            switch($usertype){
                case "Administrator":
                    //get admin info
                    $adminID = generateID("A",9);
                    $adminToken = $_POST['token'];
                    echo $adminToken;

                    //check adminToken 
                    $stmt = $conn->prepare("SELECT adminTokenID FROM admin WHERE adminTokenID = ?"); 
                    $stmt->bind_param('s', $adminToken);
                    $stmt->execute(); 
                    $qrySel = $stmt->get_result();

                    if($qrySel->num_rows >= 1) // Check if token is occupied
                        echo "Token is occupied";
                    
                    elseif($qrySel->num_rows <= 0) // Check if token exists
                        echo "Token does not exist";
                    
                    else{ // inserts admin
                        $stmt = $conn->prepare("INSERT INTO admin (adminID, adminTokenID, userID) VALUES (?,?,?)"); // preparation 
                        $stmt->bind_param('sss', $adminID, $adminToken, $userID);
                        $stmt->execute(); 
                    }

                    break;

                case "Instructor":
                    $instID = generateID("I",9);

                    $stmt = $conn->prepare("INSERT INTO instructor (instID, userID) VALUES (?,?)"); // preparation 
                    $stmt->bind_param('ss', $instID, $userID);
                    $stmt->execute(); 

                    break;

                case "Student":
                    $studentID = generateID("S",9);

                    $stmt = $conn->prepare("INSERT INTO student (studentID, userID) VALUES (?,?)"); // preparation 
                    $stmt->bind_param('ss', $studentID, $userID);
                    $stmt->execute(); 

                    break;
                    
                default:
                header("Location: index.php");
                break;
            }

        // redirect to login
        header("Location: index.php");
        }
    }

    function generateID($prefix = 'NU', $length = 8){
        return $prefix . generateRandomString();
    }

    function generateRandomString($length = 8) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, strlen($characters) - 1)];
        }
        return $randomString;
    }
?>