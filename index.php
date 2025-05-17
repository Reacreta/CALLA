<?php
  ob_start();
  session_start();

  require_once 'database.php';
  require_once 'authFunctions.php';

  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    destroySession();
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
            header("Refresh:0");
        }
        
        else{
            // else save account details to session
            $_SESSION['userID'] = $userID =  $account['userID'];
            $_SESSION['username'] = $account['username'];
            $_SESSION['email'] = $account['email'];
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
                    redirect("Instructor.php"); 
                    break;
                case 'Student':
                    redirect("Student.php"); 
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
      display: flex;
      flex-direction: column;
      background-color: #7b0000; /* deep red */
      color: white;
      padding: 100px 0;
      border-radius: 20px;
      width: 650px;
      height: auto;
      box-shadow: 0 0 15px rgba(0,0,0,0.3);
    }

    #header h1 {
      text-align: center;
      font-size: 64px;
      margin-bottom: 25px;
      font-family: 'Goudy Bookletter 1911';

    }

    .input-field-group{
      display: flex;
      flex-direction: column;
      gap: 20px;

      width: fit-content;
      margin: auto;
    }

    .login-container input[type="email"]{
    width: 505px;
    height: auto;
    padding: 25px 15px;
    margin: auto;
    border: none;
    border-radius: 10px;
    font-size: 18px;
    background-color: #fdfdfd;
    }

    .password-wrapper {
      position: relative;
      width: 505px;
      height: fit-content;
      margin: auto;
    }

    .password-wrapper input[type="password"] {
      width: 505px;
      padding: 25px 45px 25px 15px; /* <-- add right padding to make room for eye */
      border: none;
      border-radius: 10px;
      font-size: 18px;
      background-color: #fdfdfd;
      box-sizing: border-box;
    }

    .password-wrapper input[type="text"] {
      width: 505px;
      padding: 25px 45px 25px 15px; /* <-- add right padding to make room for eye */
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
      border-radius: 10px;
    }
    
    .eyeIcon:hover {
      opacity: 1;
    }

    #regButton{
      width: 100%;
    }

    .login-container a {
    font-size: 15px;
    color: #ddd;
    text-decoration: underline;
    text-align: right;
    font-weight: bold;
    font-style: italic;
    letter-spacing: 2px;
    }

    .login-container button {                      
    border: none;
    border-radius: 15px;
    background-color: white;

    color: #7b0000;
    font-weight: bold;
    font-size: 20px;

    cursor: pointer;
    display: block;          
    margin: auto;
    padding: 15px 45px;
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

  </style>
</head>
<body>
    <div class="login-container">
      <div id="header">
        <!--<img src="images/" alt="Calla logo"> if mag send na si earl og logo--> 
        <h1>LOGIN</h1>
      </div>
      <form action="" method="POST">
        <div class="input-field-group">
          <input type="email" id="email" placeholder="Email" name="email" required>

          <div class="password-wrapper">
              <input type="password" id="password" placeholder="Password" name="password" required>
              <img class="eyeIcon" id="eyeIcon" src="images/eye-close.jpg" alt="Toggle Password">
          </div>

          <div id="regButton">
            <a href="Registration.php">SIGN UP</a>
            <div class="error-section">
              <img class="errorIcon" id="errorIcon" src="images/warning.jpg" alt="!">
            <div id="errorSummary" class="errorSummary"></div>
        </div>
          </div>
        </div>

        <div class="submit-section">
          <button class="submit-btn" type="submit" name="login">Sign In</button>
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
