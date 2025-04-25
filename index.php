<?php
  ob_start();
  session_start();
  require_once 'database.php';
  require_once 'authfunctions.php';

  if(isset($_POST['login'])){
    // get form information
    $email = $_POST['email'];
    $password = $_POST['password'];

    // check if email exists in database
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?"); // preparation 
    debug_console('prep');
    $stmt->bind_param('s', $email); // subtitute ? with variable
    debug_console('sub');
    $stmt->execute(); 
    debug_console('exec');
    $qrySel = $stmt->get_result();
    debug_console('query');

    // if it doesnt: send error
    if($qrySel->num_rows === 0){
        echo "Account credentials are wrong";
    }

    // else check if password matches password from database        
    else{
        $account = $qrySel->fetch_assoc();

        // if it doesnt match: send error
        if (!password_verify($password, $account['password'])){
            debug_console('wrong credentials');
            echo "Account credentials are wrong";
        }
        else{
            // else save account details to session
            $_SESSION['userID'] = $userID =  $account['userID'];
            $accountRole = $account['userType'];
            debug_console('Check role');

            // get user role ID
            switch($accountRole){
                case 'Administrator': 
                    $stmt = $conn->prepare("SELECT adminID FROM admin WHERE userID = ?");
                    debug_console('admin prep');
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
            debug_console($_SESSION['roleID']);
            
            // redirect according to role
            switch($accountRole){
                case 'Administrator':
                    debug_console('Redirect to Administrator');
                    redirect("Admin.php");
                    break;
                case 'Instructor':
                    redirect("instructor.php"); 
                    break;
                case 'Student':
                    redirect("student.php"); 
                    break;
            }
        }
    }
  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Calla Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Goudy+Bookletter+1911&family=Inter:wght@400;700&display=swap" rel="stylesheet">

  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Inter', sans-serif;
    }

    body, html {
      height: 100%;
    }

    body {
      background-image: url("images/USeP_eagle.jpg");
      background-size: cover;
      background-position: center;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .login-container {
      background-color: #7b0000; /* deep red */
      color: white;
      padding: 40px;
      padding-top: 150px;
      border-radius: 20px;
      width: 650px;
      height: 700px;
      box-shadow: 0 0 15px rgba(0,0,0,0.3);
    }

    .login-container h1 {
      text-align: center;
      font-size: 70px;
      margin-bottom: 25px;
      font-family: 'Goudy Bookletter 1911', serif;
      letter-spacing: 10px;
    }

    .login-container label {
      font-size: 14px;
      display: block;
      margin: 0 0 5px 60px;
      letter-spacing: 2px;
    }

    .login-container input[type="email"],
    .login-container input[type="password"] {
      width: 80%;
    padding: 16px 14px;
    margin: 0 auto 20px;
    display: block;
    border: none;
    border-radius: 10px;
    font-size: 18px;
    background-color: #fdfdfd;
    }

    .login-container a {
      font-size: 12px;
    color: #ddd;
    text-decoration: underline;
    display: block;
    text-align: center;
    margin: 20px 0 0 350px;
    font-style: italic;
    letter-spacing: 2px;
    }

    .login-container button {
    width: 50%;             
    padding: 15px 0;         
    border: none;
    border-radius: 6px;
    background-color: white;
    color: #7b0000;
    font-weight: bold;
    font-size: 16px;
    cursor: pointer;
    display: block;          
    margin: 70px 0 0 140px;
    letter-spacing: 2px ;   
  } 


  </style>
</head>
<body>

  <div class="login-container">
    <h1>CALLA</h1>
    <form action="" method="POST">
      <label for="email">EMAIL:</label>
      <input type="email" id="email" name="email" required>

      <label for="password">PASSWORD:</label>
      <input type="password" id="password" name="password" required>

      <a href="Registration.php">SIGN UP</a>

      <button type="submit" name="login">LOGIN</button>
    </form>

  </div>

</body>
</html>
