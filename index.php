<?php
  ob_start();
  session_start();

  require_once 'database.php';
  require_once 'authfunctions.php';

  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    session_unset();  
    session_destroy(); 
    session_start(); 
    debug_console("Session state: " . json_encode($_SESSION)); // check if same session
  }

  if(isset($_POST['login'])){
    // get form information
    $email = $_POST['email'];
    $password = $_POST['password'];
    $_SESSION['loginError'] = false;

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
        debug_console('nothing');
        $_SESSION['loginError'] = true;
    }

    // else check if password matches password from database        
    else{
        $account = $qrySel->fetch_assoc();

        // if it doesnt match: send error
        if (!password_verify($password, $account['password'])){
            debug_console('wrong credentials');
            $_SESSION['loginError'] = true;
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

    .error-section {
      position: relative; /* anchor point for absolute children */
      height: 30px;        /* prevent collapsing height */
    }

    .errorIcon {
      position: absolute;
      bottom: 15px;
      left: 75px;
      display: none;
      width: 30px;
      height: 30px;
      cursor: pointer;
    }

    .errorSummary {
      position: absolute;
      bottom: 10px;
      left: 100px;
      display: none;
      width: 300px;
      color: #fff;
      padding: 15px;
      font-size: 12px;
      z-index: 1000;
      font-style: normal;
    }

    .password-wrapper {
      position: relative;
      width: 80%;
      margin: 0 auto 20px;
    }

    .password-wrapper {
      position: relative;
      width: 80%; /* control wrapper width */
      margin: 0 auto 20px;
    }

    .password-wrapper input[type="password"] {
      width: 100%;
      padding: 16px 45px 16px 14px; /* <-- add right padding to make room for eye */
      border: none;
      border-radius: 10px;
      font-size: 18px;
      background-color: #fdfdfd;
      box-sizing: border-box;
    }

    .password-wrapper input[type="text"] {
      width: 100%;
      padding: 16px 45px 16px 14px; /* <-- add right padding to make room for eye */
      border: none;
      border-radius: 10px;
      font-size: 18px;
      background-color: #fdfdfd;
      box-sizing: border-box;
    }

    .password-wrapper .eyeIcon {
      position: absolute;
      top: 50%;
      right: 15px;
      transform: translateY(-50%);
      height: 24px;
      width: 24px;
      cursor: pointer;
    }

    }
    .eyeIcon:hover {
      opacity: 1;
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
      <div class="password-wrapper">
          <input type="password" id="password" name="password" required>
          <img class="eyeIcon" id="eyeIcon" src="images/eye-close.jpg" alt="Toggle Password">
        </div>

      <a href="Registration.php">SIGN UP</a>

      <div class="error-section">
        <img class="errorIcon" id="errorIcon" src="images/warning.jpg" alt="!">
        <div id="errorSummary" class="errorSummary"></div>
      </div>

      <div class="submit-section">
        <button class="submit-btn" type="submit" name="login">Login</button>
      </div>
    </form>

  </div>

  <script>

  <?php
    $loginError = $_SESSION['loginError'] ?? false;
    $_SESSION['loginError'] = false; // Reset for next load
  ?>

  const form = document.querySelector('form');
  const email = document.getElementsByName('email')[0];
  const password = document.getElementsByName('password')[0];
  const errorIcon = document.getElementById('errorIcon');
  const errorSummary = document.getElementById('errorSummary');

  form.addEventListener('submit', function (e) {
    let valid = true;
    email.classList.remove('invalid');
    password.classList.remove('invalid');
    errorIcon.style.display = 'none';
    errorSummary.style.display = 'none';

    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
      email.classList.add('invalid');
      valid = false;
    }

    if (password.value.length < 8) {
      password.classList.add('invalid');
      valid = false;
    }

    <?php if ($loginError): ?>
      valid = false;
    <?php endif; ?>

    if (!valid) {
      e.preventDefault();
      errorIcon.style.display = 'inline';
      errorSummary.style.display = 'block';
      errorSummary.innerHTML = `Invalid Email or Password`;
    }
  });

  errorIcon.addEventListener('click', () => {
    errorSummary.style.display = errorSummary.style.display === 'none' ? 'block' : 'none';
  });

  //eye script
  const passwordInput = document.getElementById('password');
  const eyeIcon = document.getElementById('eyeIcon');

  eyeIcon.addEventListener('mousedown', () => {
    passwordInput.type = 'text';
    eyeIcon.src = 'images/eye-open.jpg';
  });

  eyeIcon.addEventListener('mouseup', () => {
    passwordInput.type = 'password';
    eyeIcon.src = 'images/eye-close.jpg';
  });

  eyeIcon.addEventListener('mouseleave', () => {
    passwordInput.type = 'password';
    eyeIcon.src = 'images/eye-close.jpg';
  });

</script>

</body>
</html>
