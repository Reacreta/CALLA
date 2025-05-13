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
      overflow-y: auto;
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

    .dynamic-list{
      flex-grow: 1;
      display: flex;
      flex-direction: column;
      gap: 10px;
      overflow-y: scroll;
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

    .scrollable-user-list {
      max-height: 750px; /* or whatever height you want */
      overflow-y: auto;
      
      padding-right: 10px; /* add padding to avoid scrollbar overlap */
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
        Welcome, <?php echo $_SESSION['username']?>!
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
          <button class="tab" onclick="setUserTab('Student')">Students</button>
          <button class="tab" onclick="setUserTab('Instructor')">Instructors</button>

          <!-- Search Box -->
          <div class="right-buttons">
            <div class="search-container">
              <input type="text" placeholder="Search..." class="search-input">
              <label class="SearchButton" onclick="toggleSearch(this)">Search</label>
            </div>
          </div>
        </div>

        <!-- User List Section -->
        <div class="user-list-wrapper">
          <div class="scrollable-user-list">
            <div class="user-list">
              <?php 
                if ($result && $result->num_rows > 0) {
                  // Loop through users and display each
                  while ($row = $result->fetch_assoc()) {
                    $displayName = htmlspecialchars($row['username']);
                    $role = htmlspecialchars($row['userType']);
              ?>
              <!-- User Card -->
              <div class="user-card" data-role="<?php echo $role; ?>">
                <div class="user-info">
                  <i class="fas fa-user-circle"></i>
                  <div>
                    <div><strong><?php echo $displayName; ?></strong></div>
                    <div><?php echo $role; ?></div>
                  </div>
                </div>
                <a href="user-details.html" class="search-icon-link user-search">
                  <img src="images/Search_Icon.jpg" alt="View User" class="search-image-icon">
                </a>
              </div>
              <?php 
                  }
                } else {
                  echo "<div style='text-align:center;padding:20px;'>No users found</div>";
                }
          <div class="dynamic-list">
            <?php 
              if ($result && $result->num_rows > 0) {
                // Loop through users and display each
                while ($row = $result->fetch_assoc()) {
                  $displayName = htmlspecialchars($row['username']);
                  $role = htmlspecialchars($row['userType']);
            ?>
            <!-- User Card -->
            <div class="user-card" data-role="<?php echo $role; ?>">
              <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <div>
                  <div><strong><?php echo $displayName; ?></strong></div>
                  <div><?php echo $role; ?></div>
                </div>
              </div>
              <a href="user-details.html" class="search-icon-link user-search">
                <img src="images/Search_Icon.jpg" alt="View User" class="search-image-icon">
              </a>
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
              // Debugging Info
              echo "<script>console.log('Number of users loaded: " . ($result ? $result->num_rows : 0) . "');</script>";
            ?>
          </div>
        </div>
      </div>
                  

      <!-- Classroom Overlay -->
      <div id="classroomOverlay" class="user-overlay">
        <button class="close-btn" onclick="hideOverlay('classroomOverlay')">×</button>
        <h2 style="color: #7b0000; margin-bottom: 20px;">Classrooms</h2>

        <div class="tabs">
          <button class="tab active">All</button>
          <button class="tab">Partner</button>
          <div class="right-buttons">
            <div class="search-container">
              <input type="text" placeholder="Search..." class="search-input">
              <label class="SearchButton" onclick="toggleSearch(this)">Search</label>
            </div>
          </div>
        </div>
        
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

      
      <!-- Modules Overlay -->
      <div id="moduleOverlay" class="user-overlay">
        <button class="close-btn" onclick="hideOverlay('moduleOverlay')">×</button>
        <h2 style="color: #7b0000; margin-bottom: 20px;">Modules</h2>

        <div class="tabs">
          <button class="tab active" onclick="loadModules('All', this)">All</button>
          <button class="tab" onclick="loadModules('Partner', this)">Partner</button>
          <button class="tab" onclick="loadModules('Classroom', this)">Classroom</button>
        </div>

            <!--Dynamic Module List-->
        <div class="dynamic-list" id="moduleContainer">

          <?php
            $type = $_POST['type'] ?? 'All';
            $sql='';

            if ($type === 'Partner') {
                $sql = "SELECT * FROM partnermodule pm 
                        JOIN partner p ON p.partnerID = pm.partnerID 
                        JOIN languagemodule l on l.langID = pm.langID;";

            } elseif ($type === 'Classroom') {
                $sql = "SELECT * FROM classmodule cm 
                        join instructor i ON cm.classInstID = i.instID
                        join users u ON i.userID = u.userID
                        JOIN languagemodule lm ON lm.langID = cm.langID;";
            } else {
                $sql = "
                  SELECT 
                        l.langID, 
                        l.moduleName, 
                        p.partnerName, 
                        'Partner'
                    FROM partnermodule pm 
                    JOIN partner p ON p.partnerID = pm.partnerID 
                    JOIN languagemodule l ON l.langID = pm.langID

                    UNION

                    SELECT 
                        lm.langID, 
                        lm.moduleName, 
                        u.username, 
                        'Classroom'
                    FROM classmodule cm 
                    JOIN classinstructor ci ON cm.classInstID = ci.classInstID
                    JOIN instructor i ON i.instID = ci.instID
                    JOIN users u ON u.userID = i.userID
                    JOIN languagemodule lm ON lm.langID = cm.langID;
                    ";
            }

            $result = $conn->query($sql);

            while ($row = $result->fetch_assoc()) {
            ?>
              <div class="module-card">
                <img src="images/Module_Icon.jpg" alt="Module Icon" class="module-icon">
                <div class="module-info">
                  <div class="module-title"><?= htmlspecialchars($row['title']) ?></div>
                  <div class="module-creator">By <?= htmlspecialchars($row['username']) ?></div>
                </div>
                <a href="module-details.php?moduleId=<?= $row['moduleID'] ?>" class="search-icon-link">
                  <img src="images/Search_Icon.jpg" alt="View Module" class="search-image-icon">
                </a>
              </div>
            <?php
            }
            ?>

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
        <div class="partners-list">
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

    // Outside click handler
    document.addEventListener('click', function handleOutsideClick(e) {
      if (!container.contains(e.target)) {
        closeInput(input);
        document.removeEventListener('click', handleOutsideClick);
      }
    });

    // Listen for Enter key
    input.addEventListener('keydown', function handleKey(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        searchUsers(input.value);
        // Don't close the input after search so user can modify
        input.removeEventListener('keydown', handleKey); 
      }
    });
    
    // Also add input event for real-time search
    input.addEventListener('input', function() {
      searchUsers(input.value);
    });
  }
}

function searchUsers(query) {
  const activeTab = document.querySelector('#userOverlay .tab.active')?.textContent.trim() || 'All';
  const cards = document.querySelectorAll('.user-card');
  
  cards.forEach(card => {
    // Get the username and role from the card
    const username = card.querySelector('.user-info strong').textContent.toLowerCase();
    const role = card.getAttribute('data-role');
    
    // Check if the card matches both the search query and the active tab filter
    const matchesSearch = username.includes(query.toLowerCase());
    const matchesTab = (activeTab === 'All') || (role === activeTab);
    
    // Show the card only if it matches both conditions
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
    
    // Reset to first page when changing tabs
    if (window.location.search.includes('page=')) {
      const url = new URL(window.location);
      url.searchParams.set('page', '1');
      window.history.replaceState({}, '', url);
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


  

  

// Load default on page load
window.onload = () => loadModules('All', document.querySelector('.tab.active'));

</script>
</body>
</html>