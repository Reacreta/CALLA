<?php
  session_start();
  include('database.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>CALLA Registration</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter&family=Goudy+Bookletter+1911&display=swap" rel="stylesheet">
  
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Inter', sans-serif;
      letter-spacing: 2px;

    }

    body, html {
      height: 100%;
    }

    body {
      background-image: url('images/USeP_eagle.jpg'); 
      background-size: cover;
      background-position: center;
      display: flex;
      flex-direction: column;
    }

    .header {
      background-color: #7b0000;
      color: white;
      padding: 15px 30px;
      font-family: 'Goudy Bookletter 1911', serif;
      font-size: 30px;
    }

    .register-container {
      background-color: rgba(123, 0, 0, 0.95); 
      color: white;
      width: 800px;
      height: 700px;
      margin: 40px auto;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 0 10px rgba(0,0,0,0.4);
    }

    .register-container h2 {
      font-size: 24px;
      margin-bottom: 20px;
    }

    .form-group {
      display: flex;
      gap: 10px;
      margin: 15px 0 50px 0;
    }

    .form-group input,
    .form-group select {
      flex: 1;
      padding: 12px;        
      border: none;
      border-radius: 6px;
      font-size: 16px;
    }

    .errorIconContainer {

    }

    .error-icon {
      position: absolute;
      right: 110px;
      bottom: 45px;
    }

    .error-summary {
      position: absolute;
      bottom: 85px;
      right: 30px;
      background: #fff4f4;
      color: #7b0000;
      padding: 15px;
      border-radius: 10px;
      border: 1px solid #cc0000;
      max-width: 300px;
      font-size: 14px;
      z-index: 999;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }



    .full-width {
      width: 100%;
    }

    .bottom-group {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .bottom-group a {
      font-size: 14px;
      color: #ddd;
      text-decoration: underline;
      font-style: italic;
      margin-left: 10px;
    }

    .submit-btn {
      padding: 15px 20px;
      border: none;
      border-radius: 6px;
      background-color: white;
      color: #7b0000;
      font-weight: bold;
      cursor: pointer;
    }
  </style>

</head>

<body>
  <div class="header">CALLA</div>

  <div class="register-container">
    <h2>REGISTER</h2>

    <form action="authenticationSys.php" method="POST">

      <div class="form-group">
        <input type="text" placeholder="First Name" name="first_name" required>
        <input type="text" placeholder="Last Name" name="last_name" required>
        <input type="text" placeholder="Display Name" name="display_name" required>
      </div>

      <label>Birthday</label>
      <div class="form-group">
        <select placeholder="Month" name="birth_month" required>
          <option value="" disabled selected>Choose A Month</option>
          <option value="January">January</option>
          <option value="February">February</option>
          <option value="March">March</option>
          <option value="April">April</option>
          <option value="May">May</option>
          <option value="June">June</option>
          <option value="July">July</option>
          <option value="August">August</option>
          <option value="September">September</option>
          <option value="October">October</option>
          <option value="November">November</option>
          <option value="December">December</option>
        </select>
        <input type="text" placeholder="Day" name="birth_day" required>
        <input type="text" placeholder="Year" name="birth_year" required>
      </div>

      <div class="form-group">
        <select id="role" name="role" required>
          <option value="" disabled selected>Role</option>
          <option value="Administrator">Admin</option>
          <option value="Instructor">Instructor</option>
          <option value="Student">Student</option>
        </select>
        <input type="text" placeholder="Token" name="token" id="tokenInput" style="display: none;">
        <select name="gender" class="custom-select" required>
        <option value="" disabled selected>Select Gender</option>
        <option value="male">Male</option>
        <option value="female">Female</option>
        <option value="other">Other</option>
      </select>
      </div>

      <div class="form-group">
        <input type="email" placeholder="Email*" name="email" required>
        <input type="text" placeholder="Contact Number" name="contact" required>
      </div>

      <div class="form-group">
        <input class="full-width" type="password" placeholder="Password*" name="password" required>
      </div>

      <div class="bottom-group">
  <a href="index.php">Login</a>
  <div style="display: flex; align-items: center; gap: 10px;">
    <img id="errorIcon" src="images/warning.jpg" alt="!" 
         style="display:none; width: 30px; height: 30px; cursor: pointer;">
    <button class="submit-btn" type="submit" name="register">SUBMIT</button>
  </div>
</div>

<div id="errorSummary" style="
  display: none;
  position: absolute;
  bottom: 90px;
  right: 100px;
  width: 300px;
  background-color: #fff;
  color: #7b0000;
  border: 1px solid #7b0000;
  border-radius: 10px;
  padding: 15px;
  font-size: 14px;
  box-shadow: 0 0 10px rgba(0,0,0,0.3);
  z-index: 1000;
"></div>


  <script>
    // -- Role Event Listener --
    function toggleTokenField() {
      const roleSelect = document.getElementById('role');
      const tokenInput = document.getElementById('tokenInput');

      if (roleSelect.value === 'Administrator') {
        tokenInput.style.display = 'block';
      } else {
        tokenInput.style.display = 'none';
      }
    }
    // Add an event listener to trigger the function when the role is changed
    document.getElementById('role').addEventListener('change', toggleTokenField);

    // -- Format Validation Listener -- NEW
    document.querySelector('form').addEventListener('submit', function (e) {
    let valid = true;
    let errorMessages = [];

    document.querySelectorAll('.error').forEach(el => el.style.display = 'none');
    document.querySelectorAll('input, select').forEach(el => el.classList.remove('invalid'));

    const errorIcon = document.getElementById('errorIcon');
    const errorSummary = document.getElementById('errorSummary');
    errorIcon.style.display = 'none';
    errorSummary.style.display = 'none';
    errorSummary.innerHTML = "";

    function showError(input, message) {
      input.classList.add('invalid');
      valid = false;
      errorMessages.push(message);
    }

    const firstName = document.getElementsByName('first_name')[0];
    if (!/^[A-Za-z\s]+$/.test(firstName.value)) {
      showError(firstName, "First name: only letters and spaces allowed.");
    }

    const lastName = document.getElementsByName('last_name')[0];
    if (!/^[A-Za-z\s]+$/.test(lastName.value)) {
      showError(lastName, "Last name: only letters and spaces allowed.");
    }

    const displayName = document.getElementsByName('display_name')[0];
    if (displayName.value.length > 20) {
      showError(displayName, "Display name must be 20 characters or fewer.");
    }

    const birthMonth = document.getElementsByName('birth_month')[0];
    if (!birthMonth.value) {
      showError(birthMonth, "Birth month must be selected.");
    }

    const birthDay = document.getElementsByName('birth_day')[0];
    const dayVal = parseInt(birthDay.value);
    if (!/^\d+$/.test(birthDay.value) || dayVal < 1 || dayVal > 31) {
      showError(birthDay, "Enter a valid birth day (1-31).");
    }

    const birthYear = document.getElementsByName('birth_year')[0];
    const yearVal = parseInt(birthYear.value);
    const currentYear = new Date().getFullYear();
    if (!/^\d+$/.test(birthYear.value) || yearVal > currentYear) {
      showError(birthYear, "Enter a valid birth year.");
    }

    const role = document.getElementById('role');
    if (!role.value) {
      showError(role, "Role must be selected.");
    }

    const token = document.getElementById('tokenInput');
    if (role.value === 'Administrator' && token.value.trim() !== 'ADMIN123') {
      showError(token, "Invalid admin token.");
    }

    const gender = document.getElementsByName('gender')[0];
    if (!gender.value) {
      showError(gender, "Gender must be selected.");
    }

    const email = document.getElementsByName('email')[0];
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(email.value)) {
      showError(email, "Invalid email format.");
    }

    const contact = document.getElementsByName('contact')[0];
    if (!/^\d{11}$/.test(contact.value)) {
      showError(contact, "Contact must be exactly 11 digits.");
    }

    const password = document.getElementsByName('password')[0];
    if (password.value.length < 8) {
      showError(password, "Password must be at least 8 characters.");
    }

    if (!valid) {
      e.preventDefault();
      errorIcon.style.display = 'inline';
      errorSummary.innerHTML = `<strong>Please fix the following:</strong><ul>${errorMessages.map(e => `<li>${e}</li>`).join('')}</ul>`;
    }
  });

  document.getElementById('errorIcon').addEventListener('click', function () {
    const summary = document.getElementById('errorSummary');
    summary.style.display = summary.style.display === 'none' ? 'block' : 'none';
  });

  </script>
</body>
</html>

<?php
  mysqli_close($conn);
?>
