<?php
  ob_start();
  session_start();
  require_once 'database.php';
  require_once 'authFunctions.php';

  if(isset($_POST["createPartner"])){
    $partnerName = $_POST["partnerName"];
    $partnerDesc = $_POST["partnerDesc"];
    $partnerEmail = $_POST["partnerEmail"];
    $partnerContact = $_POST["partnerContact"];
    $partnerID = generateID("P",9);

    $stmt = $conn->prepare("INSERT INTO partner VALUES(?,?,?,?,?)"); // preparation 
    $stmt->bind_param('sssss', $partnerID,$partnerName,$partnerDesc,$partnerEmail,$partnerContact); // subtitute ? with variable
    $stmt->execute(); 
    $stmt->close();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
  }
  // Fetch all users
  $sql = "SELECT * FROM users";
  $result = $conn->query($sql);
  // Setup
  $items_per_page = 10;
  $current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
  $search = isset($_GET['search']) ? trim($_GET['search']) : '';
  $search_param = "%" . $conn->real_escape_string($search) . "%";

  // Filter clause
  $where_clause = "usertype IN ('Student', 'Instructor')";
  if (!empty($search)) {
      $where_clause .= " AND username LIKE '$search_param'";
  }

  // Fetch matching users
  $sql = "SELECT * FROM users WHERE $where_clause";
  $result = $conn->query($sql);

  // Optional: Count total users (if you still need it)
  $total_users = $result ? $result->num_rows : 0;

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>CALLA Admin Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter&family=Goudy+Bookletter+1911&display=swap" rel="stylesheet">
  <style>
    /* ANIMATION */
    :root {
      --gradient: linear-gradient(45deg,  #330000, #4A0303, #7B0000, #A30505, #C0660E, #D59004);
    }

    @keyframes grad-anim {
      0%{
        background-position: left;}
      100%{
          background-position: right;
        }
    }
    /* ---------------------------------------------------------------------- */

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
      display: flex;
      flex-direction: column;
      background-image: var(--gradient);
      background-size: 300% 100%;
      animation: grad-anim 10s infinite alternate;
    }

    .header {
      border: none;
      color: white;
      padding: 15px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .header .title {
      font-size: 28px;
      font-family: 'Goudy Bookletter 1911', serif;
    }

    .header .title span {
      font-size: 14px;
      margin-left: 10px;
      font-style: italic;
    }

    .header .profile {
      width: 35px;
      height: 35px;
      border-radius: 50%;
      background-image: url('images/profile.jpg');
      background-size: cover;
      background-position: center;
    }

    .profile-container {
      margin-left: auto;
      position: relative;
      cursor: pointer;
    }

    .profile-pic {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background-size: cover;
      background-position: center;
      border: 2px solid white;
    }

    .logout-dropdown {
      display: none;
      position: absolute;
      right: 0;
      top: 50px;
      background-color: white;
      border: 1px solid #ccc;
      padding: 10px;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
      z-index: 9999;
    }

    .logout-dropdown a {
      text-decoration: none;
      color: #7b0000;
      font-weight: bold;

    }

    /* DASH */
    .dashboard-container {
      display: flex;
      height: 100%;
    }

    .sidebar {
      border: none;
      width: 250px;
      padding: 20px 10px;
      display: flex;
      flex-direction: column;
      gap: 25px;
    }

    .User-icon {
      height: 100%;
      aspect-ratio: 1 / 1;
      object-fit: cover;
      border-radius: 50%; /* Makes it circular */
    }

    .nav-group {
      background-color: rgba(193, 113, 113, 0.3); /* add transparency */
      padding: 15px;
      display: flex;
      flex-direction: column;
      gap: 15px;
      border-radius: 10px;
    }

    .nav-btn {
      background-color: rgba(255, 255, 255, 0.15);
      color: white;
      height: 90px;
      border: none;
      padding: 20px;
      border-radius: 10px;
      text-align: left;
      font-size: 16px;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 30px;
    }

    .nav-btn:hover {
      background-color: rgba(255, 255, 255, 0.2);
    }


    .main-content {
      flex: 1;
      position: relative;
      background-color: lightgray;
    }

    .background-content {
      background-image: url('images/USeP_eagle.jpg');
      background-color: rgb(255, 255, 255, 0.25);
      background-blend-mode: lighten;

      background-size: cover;
      background-position: center;

      height: 100%;
      width: 100%;
      display: flex;
      justify-content: center;
      align-items: center;
      color:rgb(255, 255, 255);
      font-size: 35px;
      font-family: 'Goudy Bookletter 1911', serif;
      font-style: italic;
      font-weight: bolder;
      
    }

    /* User Overlays */
    .user-overlay { 
      display: none;
      position: absolute;
      top: 0;
      left: 0;
      height: 100%;
      width: 100%;
      background: #f1f1f1;
      z-index: 10;
      padding: 20px;
      overflow:hidden;
    }

    .list-wrapper {
      flex: 1;
      height: 720px;
    }

    .dynamic-list {
      display: flex;
      flex-direction: column;
      gap: 10px;
      height: 100%;
      overflow-y: scroll;
      scrollbar-width: thin; /* Firefox */
      scrollbar-color: #a00 #f0f0f0; /* Firefox */
    }

    .user-overlay.show {
      display: block;
    }

    .close-btn {
      float: right;
      background: none;
      border: none;
      font-size: 24px;
      color: #7b0000;
      font-weight: bold;
      cursor: pointer;
    }

    .tabs {
      display: flex;
      gap: 10px;
      margin-bottom: 10px;
    }

    .right-buttons {
    display: flex;
    gap: 10px;
    margin-left: auto; 
    }

    .tab {
      background: none;
      border: none;
      color: #7b0000;
      font-weight: bold;
      cursor: pointer;
      padding: 8px 12px;
      border-radius: 6px 6px 0 0;
      border-bottom: 2px solid transparent;
      font-size: 20px;
    }

    .tab:hover{
      background-color: lightgray;
    }

    .tab:focus{
      background-color: #fff;
      border-bottom: 2px solid #7b0000;
    }

    .user-card {
      background: #e0e0e0;
      padding: 10px 15px;
      border-radius: 8px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .user-info {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .user-info i {
      font-size: 24px;
    }


    .search-container {
      position: relative;
      display: flex;
      align-items: center;
    }

    .search-input {
      width: 0;
      padding: 0;
      border: none;
      outline: none;
      transition: all 0.3s ease;
      overflow: hidden;
    }

    .SearchButton {
      background-color: #7b0000;
      margin-right: 15px;
      color: white;
      border: none;
      border-radius: 20px;
      padding: 10px 15px;
      cursor: pointer;
      z-index: 1;
    }

    .search-input {
      transition: width 0.3s ease, padding 0.3s ease, border 0.3s ease;
    }

    .search-image-icon {
      width: 35px;
      height: 35px;
      cursor: pointer;
      border-radius: 50%;
      object-fit: cover;
      transition: transform 0.2s;
    }

    .search-image-icon:hover {
      transform: scale(1.1);
    }

    /* User details overlay */

     .user-details-content {
      padding: 20px;
      width: 100%;
      background: white;
      border-radius: 8px;
    }


    .user-header-flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 3px;        
      }

    .user-profile-info {
      flex: 1;
      margin-top: 10px;
    }

    .user-detail-name {
      font-size: 20px;
      font-weight: bold;
      color: #000;
      margin-bottom: 5px;
    }

    .user-detail-role {
      font-size: 14px;
      color: #666;
      margin-bottom: 5px;
    }

    .user-profile-header {
      display: flex;
      align-items: flex-start;
      gap: 15px;
      margin-bottom: 20px;
      padding: 15px;
      flex-wrap: wrap;
    }

    .uid-display {
      font-size: 13px;
      color: #333;
      font-style: italic;
      margin-right: 80px;
    }


    .user-profile-img {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      object-fit: cover;
      border: 1px solid #ccc;
    }

    .user-details-grid {
      background: #f0f0f0;
      padding: 20px;
      border-radius: 8px;
      margin-bottom: 20px;
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 15px;
    }

    .detail-item {
      display: grid;
      grid-template-columns: auto 1fr;
      align-items: center;
      gap: 10px;
      margin-bottom: 10px;
    }

    .detail-item label {
      font-weight: 600;
      color: #000;
      min-width: 120px;
    }

    .detail-item div {
      color: #333;
    }

    .user-actions {
      display: flex;
      justify-content: flex-start;
      gap: 10px;
      padding: 0 20px;
    }

    .action-btn {
      padding: 8px 16px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-weight: normal;
      font-size: 14px;
      background-color: #e0e0e0;
      color: #000;
    }

    .action-btn:hover {
      opacity: 0.9;
    }

    .delete-btn {
      background-color: #7b0000;
      color: white;
    }

    .deactivate-btn {
      background-color: #7b0000;
      color: white;
    }

    #userDetailsOverlay {
      background: rgba(255, 255, 255, 0.95);
      max-width: 800px;
      width: 90%;
      margin: auto;
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .close-btn {
      position: absolute;
      right: 10px;
      top: 10px;
      background: none;
      border: none;
      font-size: 20px;
      cursor: pointer;
      color: #666;
    }

    /* Add Edit Profile link */
    .edit-profile-link {
      color: #7b0000;
      text-decoration: none;
      font-size: 14px;
      position: absolute;
      right: 20px;
      bottom: 20px;
    }

    /* Classroom Overlays */

    .classroom-item {
      background-color: #e0e0e0;
      padding: 15px 20px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .classroom-icon {
      height: 50px;
      width: 50px;
      border-radius: 50%;
      object-fit: cover;
    }

    .classroom-info {
      flex: 1;
      margin-left: 15px;
    }

    .classroom-title {
      font-weight: bold;
      font-size: 16px;
      color: #000;
    }

    .classroom-creator {
      font-size: 14px;
      color: #444;
    }

    .classroom-search-icon {
      font-size: 20px;
      color: #333;
      cursor: pointer;
    }

    .module-card {
      background-color: #e0e0e0;
      padding: 15px 20px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .module-icon {
      height: 50px;
      width: 50px;
      border-radius: 10px;
      object-fit: cover;
    }

    .module-info {
      flex: 1;
      margin-left: 15px;
    }

    .module-title {
      font-weight: bold;
      font-size: 16px;
      color: #000;
    }

    .module-creator {
      font-size: 14px;
      color: #444;
    }

    .partners-card {
      background-color: #e0e0e0;
      padding: 15px 20px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .partners-icon {
      height: 50px;
      width: 50px;
      border-radius: 10px;
      object-fit: cover;
    }

    .partners-info {
      flex: 1;
      margin-left: 15px;
    }

    .partners-title {
      font-weight: bold;
      font-size: 16px;
      color: #000;
    }

    .partners-role {
      font-size: 14px;
      color: #444;
    }

    .create-overlay { 
      display: none;
      position: absolute;
      border: 2px solid white;
      border-radius: 6px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
      top: 10%;
      left: 30%;
      height: fit-content;
      width: fit-content;
      background: rgba(241, 241, 241, 0.85);
      backdrop-filter: blur(5px);
      z-index: 20;
      padding: 20px;
      overflow-y: auto;
    }

    .create-overlay.show {
      display: block;
    }

    .create-list {
      display: flex;
      flex-direction: column;
      gap: 30px;
    }

    .create-item1,
    .create-item2 {
      display: flex;
      flex-direction: column;
      gap: 5px;
    }

    .create-info {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .create-info label {
      font-weight: bold;
      color: #333;
    }

    .create-info input,
    .create-info textarea {
      width: 500px;
      padding: 15px;
      border: 1px solid #ccc;
      border-radius: 4px;
      font-size: 16px;
    }

    /* Specific textarea styling */
    .create-info textarea {
      height: 150px;
      resize: none;
    }

    .create-SC {
      display: flex;
      gap: 20px;
      justify-content: center;
    }

    .create-SC .creates {
      background: #e6e6e6;
      border: none;
      color: #7b0000;
      font-weight: bold;
      cursor: pointer;
      margin-top: 20px;
      padding: 10px 50px;
      border-radius: 6px;
      font-size: 20px;
    }

    .create-SC .creates:hover {
      background-color: #fff;
    }
    
    .items-per-page {
      display: flex;
      align-items: center;
      gap: 5px;
    }
    
    .items-per-page select {
      padding: 5px;
      border-radius: 4px;
      border: 1px solid #ccc;
    }

  </style>
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>

<body>

  <div class="header">
    <div class="title">CALLA <span>admin</span></div>
    <div class="profile-container" onclick="toggleLogoutDropdown()">
      <div class="profile-pic" style="background-image: url('images/profile.jpg');"></div>
      <div class="logout-dropdown" id="logoutDropdown">
        <a href="logout.php">Logout</a>
      </div>
    </div>
  </div>

  <div class="dashboard-container">
    <div class="sidebar">
      <div class="nav-group">
      <button class="nav-btn" onclick="toggleUserOverlay()"><img src="images/Human_Icon.jpg" class="User-icon" alt="User Icon"> Users</button>
      <button class="nav-btn" onclick="toggleClassroomOverlay()"><img src="images/Class_Icon.jpg" class="User-icon" alt="Classroom Icon"> Classrooms</button>
      <button class="nav-btn" onclick="toggleModuleOverlay()"><img src="images/Module_Icon.jpg" class="User-icon" alt="Module Icon"> Modules</button>
      <button class="nav-btn" onclick="togglePartnersOverlay()"><img src="images/Partners_Icon.jpg" class="User-icon" alt="Partners Icon"> Partners</button>
      </div>
    </div>

    <div class="main-content">
      <!-- Background Main Content -->
      <div id="backgroundContent" class="background-content">
          Welcome Admin, <?php echo $_SESSION['username']?>!
      </div>

        <!-- Users Overlay -->
        <div id="userOverlay" class="user-overlay">
          <!-- Close Button -->
          <button class="close-btn" onclick="hideOverlay('userOverlay')">×</button>

          <!-- Title -->
          <h2 style="color: #7b0000; margin-bottom: 20px;">Users</h2>

          <!-- Tabs and Search -->
          <div class="tabs">
            <!-- User Type Tabs -->
            <button class="tab" onclick="setUserTab('All')">All</button>
            <button class="tab" onclick="setUserTab('Student')">Student</button>
            <button class="tab" onclick="setUserTab('Instructor')">Instructor</button>

            <!-- Search Box -->
            <div class="right-buttons">
              <div class="search-container">
                <input type="text" placeholder="Search..." class="search-input">
                <label class="SearchButton" onclick="toggleSearch(this)">Search</label>
              </div>
            </div>
          </div>

          <!-- User List Section -->
          <div class="list-wrapper">
            <div class="dynamic-list">
              <?php 
                if ($result && $result->num_rows > 0) {
                  // Loop through users and display each
                  while ($row = $result->fetch_assoc()) {
                    $displayName = htmlspecialchars($row['username']);
                    $role = htmlspecialchars($row['userType']);
              ?>
              <!-- User Card -->
              <div class="user-card" 
                 data-role="<?php echo $role; ?>"
                 data-name="<?php echo $displayName; ?>"
                 data-fname="<?php echo htmlspecialchars($row['firstName']); ?>"
                 data-lname="<?php echo htmlspecialchars($row['lastName']); ?>"
                 data-gender="<?php echo htmlspecialchars($row['sex']); ?>"
                 data-email="<?php echo htmlspecialchars($row['email']); ?>"
                 data-contact="<?php echo htmlspecialchars($row['contact']); ?>"
                 data-dob="<?php echo htmlspecialchars($row['dateOfBirth']); ?>"
                 data-uid="<?php echo htmlspecialchars($row['userID']); ?>">
              <div class="user-info">
                <div>
                  <div><strong><?php echo $displayName; ?></strong></div>
                  <div><?php echo $role; ?></div>
                </div>
              </div>
              <div class="search-icon-link user-search" onclick="showUserDetails(this)">
                <img src="images/Search_Icon.jpg" alt="View User" class="search-image-icon">
              </div>
            </div>
              <?php 
                  }
                } else {
                  echo "<div style='text-align:center;padding:20px;'>No users found</div>";
                }

                // Debugging Info
                echo "<script>console.log('Number of users loaded: " . ($result ? $result->num_rows : 0) . "');</script>";
              ?>
            </div>
          </div>

          <!-- User Details Overlay -->
          <div id="userDetailsOverlay" class="create-overlay">
            <button class="close-btn" onclick="hideCreateOverlay('userDetailsOverlay')">×</button>
            <h2 style="color: #7b0000; margin-bottom: 20px;">User Details</h2>
            
            <div class="user-details-content">
              <div class="user-profile-section">
                <div class="user-profile-header">
                    <img src="images/user-profile.jpg" alt="User Photo" class="user-profile-img" />
                  <div class="user-profile-info">
                    <div class="user-header-flex">
                      <div>
                        <div id="userDetailName" class="user-detail-name">Joshua Gatmin</div>
                        <div id="userDetailRole" class="user-detail-role">Instructor</div>
                      </div>
                      <div class="uid-display" id="userDetailUID">UID: Thr5Tr4pY3s</div>
                    </div>
                  </div>
                </div>
                
                <div class="user-details-grid">
                  <div class="detail-item">
                    <label>First Name:</label>
                    <div id="userDetailFirstName">Joshua</div>
                  </div>
                  <div class="detail-item">
                    <label>Last Name:</label>
                    <div id="userDetailLastName">Gatmin</div>
                  </div>
                  <div class="detail-item">
                    <label>Gender:</label>
                    <div id="userDetailGender">Male</div>
                  </div>
                  <div class="detail-item">
                    <label>Email:</label>
                    <div id="userDetailEmail">jg17@gmail.com</div>
                  </div>
                  <div class="detail-item">
                    <label>Contact:</label>
                    <div id="userDetailContact">09827653342</div>
                  </div>
                  <div class="detail-item">
                    <label>Date of Birth:</label>
                    <div id="userDetailDOB">07/17/2003</div>
                  </div>
                </div>
              </div>
              
              <div class="user-actions">
                <button class="action-btn" onclick="checkUserLogs()">Check Logs</button>
                <button class="action-btn delete-btn" onclick="deleteUser()">Delete</button>
                <button class="action-btn deactivate-btn" onclick="deactivateUser()">Deactivate</button>
              </div>
            </div>
          </div>
        </div>
                    

        <!-- Classroom Overlay -->
        <div id="classroomOverlay" class="user-overlay">
          <button class="close-btn" onclick="hideOverlay('classroomOverlay')">×</button>
          <h2 style="color: #7b0000; margin-bottom: 20px;">Classrooms</h2>

          <div class="tabs">
            <div class="right-buttons">
              <div class="search-container">
                <input type="text" placeholder="Search..." class="search-input">
                <label class="SearchButton" onclick="toggleSearch(this)">Search</label>
              </div>
            </div>
          </div>
          <div class="list-wrapper">
            <div class="dynamic-list">

              <!-- Dynamic Classroom  Table -->
              <?php
                $sql = "SELECT classroom.className, users.username 
                        FROM classroom 
                        JOIN instructor ON classroom.instID = instructor.instID 
                        JOIN users ON instructor.userID = users.userID;";
                $result = $conn->query($sql);

                while ($row = $result->fetch_assoc()) {
                  $className = htmlspecialchars($row['className']);
                  $creatorName = htmlspecialchars($row['username']);
                  debug_console($className);
                  debug_console($creatorName);
              ?>
                <div class="classroom-item">
                  <img src="images/Class_Icon.jpg" alt="Class Icon" class="classroom-icon">
                  <div class="classroom-info">
                    <div class="classroom-title"><?php echo $className; ?></div>
                    <div class="classroom-creator"><?php echo $creatorName; ?></div>
                  </div>
                  <a href="classroom-details.html?classId=math4" class="search-icon-link">
                    <img src="images/Search_Icon.jpg" alt="View Classroom" class="search-image-icon">
                  </a>
                </div>
              <?php } ?>
            </div>
          </div>
        </div>

        
        <!-- Modules Overlay -->
        <div id="moduleOverlay" class="user-overlay">
          <button class="close-btn" onclick="hideOverlay('moduleOverlay')">×</button>
          <h2 style="color: #7b0000; margin-bottom: 20px;">Modules</h2>

          <div class="tabs">
            <button class="tab active" onclick="loadModules('All', this)">All</button>
            <button class="tab" onclick="loadModules('Partner', this)">Partner</button>
            <button class="tab" onclick="loadModules('Classroom', this)">Classroom</button>
            <div class="right-buttons">
              <div class="search-container">
                <input type="text" placeholder="Search..." class="search-input">
                <label class="SearchButton" onclick="toggleSearch(this)">Search</label>
              </div>
            </div>
          </div>

              <!--Dynamic Module List-->
          <div class="list-wrapper">
            <div class="dynamic-list" id="moduleContainer">
                <!-- Loading Modules -->
            </div>
          </div>
        </div>
        

        <!-- Partners Overlay -->
        <div id="partnersOverlay" class="user-overlay">
          <button class="close-btn" onclick="hideOverlay('partnersOverlay')">x</button>
          <h2 style="color: #7b0000; margin-bottom: 20px;">Partners</h2>
          
          <div class="tabs">
            <button class="tab active">Partners</button>
            <button class="tab" onclick="toggleCreatePartnersOverlay()">New</button>
            <div class="right-buttons">
              <div class="search-container">
                <input type="text" placeholder="Search..." class="search-input">
                <label class="SearchButton" onclick="toggleSearch(this)">Search</label>
              </div>
            </div>
          </div>

          <!-- Partner Dynamic Table-->
          <div class="list-wrapper">
            <div class="dynamic-list">
            <?php
                $sql = "SELECT * FROM partner";
                $result = $conn->query($sql);

                while ($row = $result->fetch_assoc()) {
                  $partnerName = htmlspecialchars($row['partnerName']);
                  $partnerEmail = htmlspecialchars($row['email']);
              ?>
                <div class="partners-card">
                <img src="images/Partners_Icon.jpg" alt="Partners Icon" class="partners-icon">
                <div class="partners-info">
                  <div class="partners-title"><?php echo $partnerName?></div>
                  <div class="partners-role"><?php echo $partnerEmail?></div>
                </div>
                <a href="partners-details.html?partnersId=DepEd" class="search-icon-link">
                  <img src="images/Search_Icon.jpg" alt="View Partners" class="search-image-icon">
                </a>
              </div>
              <?php } ?>
            </div>
          </div>

          <!-- Partner Creation-->
          <div id="createPartnersOverlay" class="create-overlay">
            <button class="close-btn" onclick="hideCreateOverlay('createPartnersOverlay')">×</button>
            <h2 style="color: #7b0000; margin-bottom: 20px;">Create a Partner</h2>

            <div class="create-list">
              <form action="" method="post">
                <div class="create-item1">
                  <div class="create-info">
                    <label for="partnerName">Partner Name:</label>
                    <input type="text" id="partnerName" name="partnerName" placeholder="Partner Name" required>
                  </div>
                </div>
                  <div class="create-item1">
                  <div class="create-info">
                    <label for="partnerContact">Contact Number:</label>
                    <input type="tel" id="partnerContact" name="partnerContact" placeholder="Contact Number" required>
                  </div>
                </div>
                <div class="create-item1">
                  <div class="create-info">
                    <label for="partnerEmail">Email:</label>
                    <input type="email" id="partnerEmail" name="partnerEmail" placeholder="Partner Email" required>
                  </div>
                </div>
                <div class="create-item2">
                  <div class="create-info">
                    <label for="partnerDesc">Partner Description:</label>
                    <textarea id="partnerDesc" name="partnerDesc" placeholder="Partner Description" required></textarea>
                  </div>
                </div>
                <div class="create-SC">
                  <button class="creates" type="submit" name="createPartner">Create</button>
                  <button class="creates" onclick="hideCreateOverlay('createPartnersOverlay')">Cancel</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>  


  <script>

  const lastOpened = "";



  // On Webpage Load
  window.addEventListener('DOMContentLoaded', () => {
    setUserTab('All');
    setModuleTab('All');
  });



  // ------------------------- Logout Dropdown
  function toggleLogoutDropdown() { 
    const dropdown = document.getElementById('logoutDropdown');
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
  }

  document.addEventListener('click', function (e) { 
    const profileContainer = document.querySelector('.profile-container');
    const dropdown = document.getElementById('logoutDropdown');
    if (!profileContainer.contains(e.target)) {
      dropdown.style.display = 'none';
    }
  });


  function showOverlay(targetId) {
    const overlays = ['userOverlay', 'classroomOverlay',  'moduleOverlay', 'partnersOverlay'];
    const bg = document.getElementById('backgroundContent');

    overlays.forEach(id => document.getElementById(id).classList.remove('show'));

    const target = document.getElementById(targetId);
    target.classList.add('show');

    bg.style.display = 'none';

    setUserTab('All');
    setModuleTab('All');
  }

  function hideOverlay(targetId) {
    const target = document.getElementById(targetId);
    target.classList.remove('show');

    // If no overlays are visible, show the background
    const anyOpen = document.querySelectorAll('.user-overlay.show').length > 0;
    if (!anyOpen) {
      document.getElementById('backgroundContent').style.display = 'flex';
    }
  }

 function toggleSearch(label) {
  const container = label.closest('.search-container');
  const input = container.querySelector('.search-input');
  const isOpen = input.style.width === '200px';

  if (isOpen) {
    if (input.value.trim()) {
      searchUsers(input.value);
    } else {
      closeInput(input);
    }
  } else {
    openInput(input);

    // Handle outside click
    document.addEventListener('click', function handleOutsideClick(e) {
      if (!container.contains(e.target)) {
        closeInput(input);
        document.removeEventListener('click', handleOutsideClick);
      }
    });

    // Enter key handler
    const handleKey = function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        searchUsers(input.value);
        input.removeEventListener('keydown', handleKey);
      }
    };
    input.addEventListener('keydown', handleKey);

    // Real-time filtering
    input.addEventListener('input', function () {
      searchUsers(input.value);
    });
  }
}

function searchUsers(query) {
  const activeTabElement = document.querySelector('#userOverlay .tab.active');
  const cards = document.querySelectorAll('.user-card');
  const searchValue = query.toLowerCase();

  // Normalize tab name (e.g. "Students" -> "student")
  let activeTab = activeTabElement ? activeTabElement.textContent.trim().toLowerCase() : 'all';
  if (activeTab.endsWith('s') && activeTab !== 'all') {
    activeTab = activeTab.slice(0, -1); // remove trailing 's' for matching
  }

  cards.forEach(card => {
    const username = card.querySelector('.user-info strong').textContent.toLowerCase();
    const role = card.getAttribute('data-role').toLowerCase(); // e.g., "student" or "instructor"

    const matchesSearch = username.includes(searchValue);
    const matchesTab = (activeTab === 'all') || (role === activeTab);

    card.style.display = (matchesSearch && matchesTab) ? 'flex' : 'none';
  });
}

function openInput(input) {
  input.style.width = '200px';
  input.style.padding = '10px';
  input.style.border = '1px solid #ccc';
  input.style.borderRadius = '20px';
  input.focus();
}

function closeInput(input) {
  input.style.width = '0';
  input.style.padding = '0';
  input.style.border = 'none';
  input.value = '';
}

  // Aliases for buttons
  function toggleUserOverlay() {
    showOverlay('userOverlay');
  }

  function toggleClassroomOverlay() {
    showOverlay('classroomOverlay');
  }

  function toggleModuleOverlay() {
  showOverlay('moduleOverlay');
  }

  function togglePartnersOverlay() {
  showOverlay('partnersOverlay'); 
  }

  function toggleCreatePartnersOverlay() {
    const overlay = document.getElementById('createPartnersOverlay');
    if (overlay.classList.contains('show')) {
      hideCreateOverlay('createPartnersOverlay');
    } else {
      showCreateOverlay('createPartnersOverlay');
    }
  }

  function setUserTab(role) {
    const cards = document.querySelectorAll('.user-card');
    const tabs = document.querySelectorAll('#userOverlay .tab');
    const searchInput = document.querySelector('#userOverlay .search-input');

    // Update tab styling
    tabs.forEach(tab => {
      if (tab.textContent.trim() === role) {
        tab.classList.add('active');
        tab.focus(); // This will apply the focus styling
      } else {
        tab.classList.remove('active');
      }
    });

    // Clear any active search
    if (searchInput) {
      searchInput.value = '';
      closeInput(searchInput);
    }

    // Filter cards based on role
    if (role === 'All') {
      cards.forEach(card => card.style.display = 'flex');
    } else {
      cards.forEach(card => {
        const cardRole = card.getAttribute('data-role');
        card.style.display = (cardRole === role) ? 'flex' : 'none';
      });
    }
  }
  
  function loadModules(type, clickedBtn) {
    // Remove 'active' from all tabs
    document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
    // Add 'active' to the clicked one
    clickedBtn.classList.add('active');

    // Send AJAX request
    fetch('adminFunctions.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'type=' + encodeURIComponent(type)
    })
    .then(res => res.text())
    .then(html => {
      document.getElementById('moduleContainer').innerHTML = html;
    });
  }

  function showCreateOverlay(targetId) {
    const target = document.getElementById(targetId);
    target.classList.add('show');
  }

  function hideCreateOverlay(targetId) {
    const target = document.getElementById(targetId);
    target.classList.remove('show');
  }

  /* userdetail overlay */

  function showUserDetails(element) {
  const userCard = element.closest('.user-card');

  // Get data attributes directly
  const name = userCard.dataset.name;
  const role = userCard.dataset.role;
  const fname = userCard.dataset.fname;
  const lname = userCard.dataset.lname;
  const gender = userCard.dataset.gender;
  const email = userCard.dataset.email;
  const contact = userCard.dataset.contact;
  const dob = userCard.dataset.dob;
  const uid = userCard.dataset.uid;

  // Update overlay fields
  document.getElementById('userDetailName').textContent = name;
  document.getElementById('userDetailRole').textContent = role;
  document.getElementById('userDetailFirstName').textContent = fname;
  document.getElementById('userDetailLastName').textContent = lname;
  document.getElementById('userDetailGender').textContent = gender;
  document.getElementById('userDetailEmail').textContent = email;
  document.getElementById('userDetailContact').textContent = contact;
  document.getElementById('userDetailDOB').textContent = dob;
  document.getElementById('userDetailUID').textContent = uid;

  // Show the overlay
  document.getElementById('userDetailsOverlay').classList.add('show');
}

  

  

// Load default on page load
window.onload = () => loadModules('All', document.querySelector('.tab.active'));

</script>
</body>
</html>