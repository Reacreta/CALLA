<?php
  require_once 'database.php';
  require_once 'authFunctions.php';

  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);

  ob_start();
  session_start();
  sessionCheck();

  // fetch instructor Id on load
  $creatorID = $_SESSION['userID'];
  $sql = "SELECT instID FROM instructor WHERE userID = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('s',$creatorID);
  $stmt->execute();
  $res = $stmt->get_result();
  $row = $res->fetch_assoc();
  $_SESSION['instID'] = $row['instID'];

  // Insert into Classroom
  if(isset($_POST['createClassroom'])){
    $creatorID= array_values($row)[0];

    $className = $_POST['className'];
    $classDesc = $_POST['classDesc'];
    $classCode = generateID("CC",5);
    $classID = generateID("C", 9);
    $dateCreated = date("Y/m/d");
    $sql = "INSERT INTO classroom (classroomID,instID,className,classDesc,classCode,dateCreated) VALUES(?,?,?,?,?,?)";
    
    $stmt = $conn->prepare($sql); 
    $stmt->bind_param('ssssss', $classID, $creatorID,$className,$classDesc,$classCode,$dateCreated);
    $stmt->execute();

    // Insert into Classinst
    $classinstID = generateID("CI", 8);
    $sql = "INSERT INTO classinstructor (classinstID, instID, classroomID) VALUES(?,?,?)";
    $stmt = $conn->prepare($sql); 
    $stmt->bind_param("sss",$classinstID,$creatorID, $classID);
    $stmt->execute();

    logAction($conn, $_SESSION['userID'], "Created Classroom: ".$className);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
  }

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>CALLA Instructor Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
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
    .title{
      display: flex;
      flex-direction: row;
    }

    .title #role{
      display: flex;
      align-items: end;
    }

    .title #role span{
      font-size: 35px;
      font-family: 'Goudy Bookletter 1911', serif;
    }

    .title #logo{
      height: 70px;
      width: auto;
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
      opacity: 70%;
      background-size: cover;
      background-position: center;
      height: 100%;
      width: 100%;
      display: flex;
      justify-content: center;
      align-items: center;
      color: white;
      font-size: 30px;
      font-family: 'Goudy Bookletter 1911', serif;
      font-style: italic;
      font-weight: bold; 
    }

    /* OVERLAYS */
    .module-overlay { 
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
    
    .module-overlay.show, .create-overlay.show, .join-overlay.show {
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

    .list-wrapper{
      flex: 1;
      height: 87%;
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

    /* Tabs */
    .tabs { 
      display: flex;
      flex-direction: row;
      gap: 20px;
      margin-bottom: 10px;
      width: 100%;
    }

    .right-buttons {
    display: flex;
    flex-direction: row;
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
    
    .tabs .tab.add:hover, .SearchButton:hover{
      transform: scale(1.1); 
    }
    
    .tabs .tab:not(.search, .add):hover {
      background-color: #fff;
      border-bottom: 2px solid #7b0000;
    }

    .tabs .tab:not(.search, .add):focus {
      background-color: #fff;
      border-bottom: 2px solid #7b0000;
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

    .classroom-card {
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

    /* CREATE CLASS */

    .create-overlay { 
      display: none;
      position: absolute;
      border: 2px solid white;
      border-radius: 6px 6px;
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

    .create-con form {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    .create-SC{
      display: flex;
      justify-content: right;
      gap: 15px;
    }

    .create-SC .creates{
      background: #e6e6e6;
      border: none;
      color: #7b0000;
      font-weight: bold;
      cursor: pointer;
      margin-top: 50px;
      padding: 10px 50px;
      border-radius: 6px 6px;
      font-size: 20px;
    }

    .create-SC .creates:hover{
      background-color: #fff;
    }

    #className {
      width: 500px;
      padding: 15px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }

    /* JOIN CLASS */

    .join-overlay{
      display: none;
      position: absolute;
      border: 2px solid white;
      border-radius: 6px 6px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
      top: 10%;
      left: 30%;
      height: 45%;
      width: 35%;
      background: rgba(241, 241, 241, 0.85);
      backdrop-filter: blur(5px);
      z-index: 20;
      padding: 20px;
      overflow-y: auto;
    }

    #joinHeader{
      display: flex;
      flex-direction: row;
      gap: 20px;
      margin-bottom: 20px;
    }

    #joinTitle{
      display: flex;
      gap: 10px;
      align-items: end;
    }

    #joinClassIcon img{
      width: 75px;
      height: 75px;
      border-radius: 50%;
    }

    #joinClassInfo{
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .join-con{
      width: auto;
      height: 100%;
    }

    /* SEARCH INPUTS */
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

    textarea {
      width: 625px;
      height: 200px;
      padding: 15px;
      resize: none;
      border: 1px solid #ccc;
      border-radius: 4px;
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

    .partners-list {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }


    
  </style>
</head>

<body>
  <div class="header">
    <div class="title"><img id="logo" src="images/logo.png"><div id="role"><span>INSTRUCTOR</span></div></div>
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
      <button class="nav-btn" onclick="showOverlay('classroomOverlay')"><img src="images/Class_Icon.jpg" class="User-icon" alt="Classroom Icon"> Classrooms</button>
      <button class="nav-btn" onclick="showOverlay('moduleOverlay')"><img src="images/Module_Icon.jpg" class="User-icon" alt="Module Icon"> Modules</button>
      </div>
    </div>
  
    <div class="main-content">
      <!-- Background Main Content -->
      <div id="backgroundContent" class="background-content">
        Welcome Instructor, <?php echo $_SESSION['username']?> !
      </div>

      <!-- Classroom Overlay -->
      <div id="classroomOverlay" class="module-overlay" overlay-type="classroom">
        <button class="close-btn" onclick="hideOverlay('classroomOverlay')">×</button>
        <h2 style="color: #7b0000; margin-bottom: 20px;">Classrooms</h2>

        <div class="tabs">
          <div class="left-buttons">
            <button class="tab active" onclick="setClassFilter('all')">All</button>
            <button class="tab" onclick="setClassFilter('joinable')">Joinable</button>
            <button class="tab" onclick="setClassFilter('owned')">Owned</button>
          </div>

          <div class="right-buttons">
            <button class="tab" onclick="showOverlay('createOverlay','classroomOverlay')">Create Classroom</button>
            <div class="search-container">
              <input type="text" placeholder="Search..." class="search-input">
              <label class="SearchButton" onclick="toggleSearch(this)">Search</label>
            </div>
          </div>

        </div>
        
        <div class="list-wrapper">
          <div class="dynamic-list">
            <?php
              // get table of all classrooms and their creator
              $sql = "SELECT * 
                      FROM classroom 
                      JOIN instructor ON classroom.instID = instructor.instID 
                      JOIN users ON instructor.userID = users.userID;";
              $result = $conn->query($sql);

              while ($row = $result->fetch_assoc()) {
                $instID = $_SESSION['roleID'];
                $classroomID = htmlspecialchars($row['classroomID']);
                $className = htmlspecialchars($row['className']);
                $classDesc = htmlspecialchars($row['classDesc']);
                $classCode = htmlspecialchars($row['classCode']);
                $creatorName = htmlspecialchars($row['username']);
                
                // Check if joinable
                $sql = "SELECT * FROM classinstructor ci WHERE ci.instID = ? AND ci.classroomID = ?;";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ss',$instID,$classroomID);
                $stmt->execute();
                $res = $stmt->get_result();

                // Not Joinable 
                if($res->num_rows <> 1){ // if query 
            ?>
                  <div class="classroom-card" class-type = "joinable"
                    classroom-id = "<?php echo $classroomID?>"
                    classroom-name = "<?php echo $className?>"
                    classroom-desc = "<?php echo $classDesc?>"
                    classroom-code = "<?php echo $classCode?>"
                    classroom-creator = "<?php echo $creatorName?>"
                    >

                    <img src="images/Class_Icon.jpg" alt="Class Icon" class="classroom-icon">
                    <div class="classroom-info">
                      <div class="classroom-title"><?php echo $className; ?></div>
                      <div class="classroom-creator"><?php echo $creatorName; ?></div>
                    </div>
                    <Button id="joinClass" onclick="showJoinOverlay(this)">Join</Button>
                  </div>
            <?php 
                }
                else{
            ?>
                  <div class="classroom-card" class-type = "owned">
                      <img src="images/Class_Icon.jpg" alt="Class Icon" class="classroom-icon">
                      <div class="classroom-info">
                        <div class="classroom-title"><?php echo $className; ?></div>
                        <div class="classroom-creator"><?php echo $creatorName; ?></div>
                      </div>
                      <div class="search-icon-link user-search" onclick="showClassDetails(this)">
                        <img src="images/Search_Icon.jpg" alt="View User" class="search-image-icon">
                      </div>
                    </div>
            <?php
                }
            }
            ?>
          </div>
        </div>

      </div> <!-- End Classroom Overlay -->

      <div id="createOverlay" class="create-overlay">
        <button class="close-btn" onclick="hideClassSubOverlay('createOverlay')">×</button>
        <h2 style="color: #7b0000; margin-bottom: 20px;">Create a Class</h2>

        <div class = create-con>

          <form action="" method= post>

          <div class = create-item1>
            <div class = create-info>
              <input type="text" id="className" name="className"placeholder="Class Name" required>
            </div>
          </div>

          <div class = create-item2>
            <div class = create-info>
              <textarea rows="20" cols="100" id="classDesc" name="classDesc" placeholder="Class Description" required></textarea>
            </div>
          </div>

          <div class = create-SC>
            <button class = "creates" type="submit" name="createClassroom">Create</button>
            <button class = "creates" onclick="hideClassSubOverlay('createOverlay')">Cancel</button>
          </div>
          </form>

        </div>

      </div> <!-- End Classroom Creation-->

      <!-- Join Classroom -->
      <div id="joinOverlay" class="join-overlay">
      <button class="close-btn" onclick="hideClassSubOverlay('joinOverlay')">×</button>
        <div class = join-con>

          <div id="joinHeader">
            <div id="joinClassIcon">
              <img src="images/Class_Icon.jpg" alt="">
            </div>
            <div id="joinClassInfo">
              <div id="joinTitle">
                <div style="color: black; font-size: 20px; font-weight: bold;" id="joinName">null</div>
                <div style="color: black; font-size: 13px; font-style: italic;" id="joinCreator">null</div>
              </div>
              <div>
                <p id="joinDesc" style="color: black; margin-bottom: 20px;">null</p>
              </div>
            </div>
          </div>

          <div id="joinCode">
            <input type="text" id="classCode" name="classCode"placeholder="Code" required>
          </div>

          <div class = create-SC>
            <button class = creates type="button" onclick= "joinClassroom();">Join</button>
            <button class = creates type="submit" onclick="hideClassSubOverlay('joinOverlay')">Cancel</button>
          </div>

        </div>
      </div>

      <!-- Modules Overlay -->
      <div id="moduleOverlay" class="module-overlay" overlay-type ="module">
        <button class="close-btn" onclick="hideOverlay('moduleOverlay')">×</button>
        <h2 style="color: #7b0000; margin-bottom: 20px;">Modules</h2>

        <div class="tabs">
          <div id="tabHeader">Owned</div>
          <div class="right-buttons">
            <button onclick="toggleModuleCreation()">New Module</button>
            <div class="search-container">
              <input type="text" placeholder="Search..." class="search-input">
              <label class="SearchButton" onclick="toggleSearch(this)">Search</label>
            </div>
          </div>
        </div>

        <div class="list-wrapper">
          <div class="dynamic-list">
              <?php
              
                $sql = "
                SELECT 
                    lm.langID, 
                    lm.moduleName, 
                    u.username, 
                    'Classroom'
                FROM classmodule cm 
                JOIN classinstructor ci ON cm.classInstID = ci.classInstID
                JOIN instructor i ON i.instID = ci.instID
                JOIN users u ON u.userID = i.userID
                JOIN languagemodule lm ON lm.langID = cm.langID
                WHERE i.instID = ?;
                ";

                $stmt = $conn->prepare($sql); 
                $stmt->bind_param('s', $_SESSION['instID']);
                $stmt->execute();
                 
                debug_console("InstructorID: ".$_SESSION['instID']);

                $result = $stmt->get_result();
                while($row = $result->fetch_assoc()){
              ?>
                  <div class="module-card">
                    <img src="images/Module_Icon.jpg" alt="Module Icon" class="module-icon">
                    <div class="module-info">
                    <div class="module-title"><?= htmlspecialchars($row['moduleName']) ?></div>
                    <div class="module-creator">By <?= htmlspecialchars($row['username']) ?></div>
                    </div>
                    <button>
                      <img src="images/Search_Icon.jpg" alt="View Module" class="search-image-icon">
                    </button>
                </div>    
              <?php
                }
              ?>
          </div>
        </div>

      </div><!-- End Module Overlay-->

      <!-- Module Creation -->
      
    </div><!-- End Main Content-->
  </div><!-- End dashboard-container-->



<script>
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

  // Search Funcs
  function toggleSearch(label) {
    // get container and input field
    const container = label.closest('.search-container');
    const input = container.querySelector('.search-input');
    // get overlay-type
    const overlay = label.closest('[overlay-type]');
    const overlayType = overlay ? overlay.getAttribute('overlay-type') : null;
    // check bool for expanded
    const isOpen = input.style.width === '200px';

    // flex function inherit
    const functionMap = {
      "classroom": searchClassroom,
      "module": searchModule
    };

    const flexSearch = functionMap[overlayType];

    if (isOpen) {
      if (input.value.trim()) {
        flexSearch(input.value);
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
          flexSearch(input.value);
          input.removeEventListener('keydown', handleKey);
        }
      };
      input.addEventListener('keydown', handleKey);

      // Real-time filtering
      input.addEventListener('input', function () {
        flexSearch(input.value);
      });
    }
  }

    // Funcs
  function searchClassroom(query) {
    const activeTabElement = document.querySelector('#classroomOverlay .tab.active');
    const cards = document.querySelectorAll('.classroom-card');
    const searchValue = query.toLowerCase();

    // Normalize tab name
    let activeTab = activeTabElement ? activeTabElement.textContent.trim().toLowerCase() : 'all';
    if (activeTab.endsWith('s') && activeTab !== 'all') {
      activeTab = activeTab.slice(0, -1); // remove trailing 's' for matching
    }

    cards.forEach(card => {
      const className = card.querySelector('.classroom-title').textContent.toLowerCase();
      const type = card.getAttribute('class-type').toLowerCase();

      const matchesSearch = className.includes(searchValue);
      const matchesTab = (activeTab === 'all') || (role === activeTab);

      card.style.display = (matchesSearch && matchesTab) ? 'flex' : 'none';
    });
  }

  function searchModule(query){
    const cards = document.querySelectorAll('.module-card');
    const searchValue = query.toLowerCase();

    cards.forEach(card => {
      const moduleName = card.querySelector('.module-title').textContent.toLowerCase();
      const matchesSearch = moduleName.includes(searchValue);
      card.style.display = (matchesSearch) ? 'flex' : 'none';
    });
  }
  
  // input funcs
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

  function setClassFilter(typeFilter) {
    const cards = document.querySelectorAll('.classroom-card');
    const tabs = document.querySelectorAll('#classroomOverlay .tab');
    const searchInput = document.querySelector('#classroomOverlay .search-input');

    // Update tab styling
    tabs.forEach(tab => {
      if (tab.textContent.trim() === typeFilter) {
        tab.classList.add('active');
        tab.focus(); // This will apply the focus styling
      } else {
        tab.classList.remove('active');
      }
    });

    // Clear any active search
    if (searchInput) {
      searchInput.value = '';
      if (typeof closeInput === 'function') {
        closeInput(searchInput);
      }
    }

    // Filter cards based on typeFilter
    if (typeFilter === 'all') {
      cards.forEach(card => card.style.display = 'flex');
    } else {
      cards.forEach(card => {
        const cardType = card.getAttribute('class-type');
        card.style.display = (cardType === typeFilter) ? 'flex' : 'none';
      });
    }
  }

  function showOverlay(targetId, backgroundId = null) {
    const overlays = ['classroomOverlay', 'moduleOverlay', 'createOverlay', 'joinOverlay'];
    const bg = document.getElementById('backgroundContent');

    overlays.forEach(id => {
      const overlay = document.getElementById(id); // gets element with corresponding name from overlay array

      const shouldShow = (id === targetId || (backgroundId && id === backgroundId));// BOOLEAN MAN DIAY NI PUTANGINA MO

      overlay.classList.toggle('show', shouldShow);
    });

    bg.style.display = 'none';
  }

  function hideOverlay(targetId) {
    const target = document.getElementById(targetId); // gets element with corresponding name 
    
    target.classList.remove('show');

    // If no overlays are visible, show the background
    const anyOpen = document.querySelectorAll('.show').length > 0;

    if (!anyOpen) {
      document.getElementById('backgroundContent').style.display = 'flex';
    }

  }

  function hideClassSubOverlay(targetId) {

    const target = document.getElementById(targetId);

    target.classList.remove('show');

    const anyOpen = document.querySelectorAll('.show').length > 0;

    if (!anyOpen) {
      document.getElementById('classroomOverlay').classList.add('show');
    }

  }

    var name = "";
    var desc = "";
    var code = "";
    var creator = "";
    var classid = "";
  
  function showJoinOverlay(element) {
    console.log("Join Overlay");

    // Find the parent classroom-card element
    const classCard = element.closest('.classroom-card');
    if (!classCard) {
      console.error("Error: Classroom card not found.");
      return;
    }

    console.log("Join Overlay2");

    // Get data attributes directly
    name = classCard.getAttribute('classroom-name');
    desc = classCard.getAttribute('classroom-desc');
    code = classCard.getAttribute('classroom-code');
    creator = classCard.getAttribute('classroom-creator');
    classid = classCard.getAttribute('classroom-id');

    console.log(name + " " + desc + " " + code + " " + creator + " " + classid);

    // Update overlay fields
    document.getElementById('joinName').textContent = name;
    document.getElementById('joinDesc').textContent = desc;
    document.getElementById('joinCreator').textContent = creator;

    // Show the overlay
    showOverlay('joinOverlay', 'classroomOverlay');
  }

  function joinClassroom() {
    console.log("Join Classroom");

    // Get the value of the input field #classCode
    const inputCode = document.getElementById('classCode').value;
    console.log(inputCode +" = "+code);


    console.log(inputCode === code);
    // Compare the input value with the variable `code`
    if (inputCode !== code) {
      alert('The class code you entered is incorrect.');
      return; // Stop execution if the codes don't match
    }

    const data = {
      name: name,
      desc: desc,
      code: code,
      creator: creator,
      classid: classid
    };

    console.log(data);

    fetch('instructorFunctions.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'joinClassroom',
        data: data
      })
    })
      .then(response => {
        if (!response.ok) {
          throw new Error('Network response was not ok');
        }
        return response.json(); // Parse JSON response
      })
      .then(result => {
        if (result.success) {
          alert('Classroom joined successfully!');
          location.reload(); // Optionally refresh the page
        } else {
          alert('Failed to join classroom: ' + result.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred: ' + error.message);
      });
  }

</script>

</body>
</html>

<?php
  mysqli_close($conn);
?>
