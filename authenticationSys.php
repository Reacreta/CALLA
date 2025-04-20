<?php
    session_start();
    require_once 'database.php';

    if(isset($_POST['login'])){
        $email = $_POST['email'];
        $password = $_POST['password'];

        $checkAccount = $conn->query("
            SELECT email FROM users WHERE email = '$email' 
            ");
        if(empty($checkAccount)){
            echo "Account not Registered"
        }
        else{
            $account = mysqli_fetch_assoc($checkAccount);
            if ($account['password'] = $password){
                checkRole($account);
            }
        }
    }
    
    if(isset($_POST['register'])){
        $userType = $_POST[''];
        $userName = $_POST[''];
        $email = $_POST[''];
        $password = $_POST[''];
        $firstName = $_POST[''];
        $lastName = $_POST['']; 
        $dateOfBirth = $_POST[''];
        $activeBool = true;

        $userID

        $checkEmail
    }

    function checkRole(){
        
    }
?>