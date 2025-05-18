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
  $sql = "SELECT studentID FROM student WHERE userID = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('s',$creatorID);
  $stmt->execute();
  $res = $stmt->get_result();
  $row = $res->fetch_assoc();
  $_SESSION['studentID'] = $row['studentID'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>CALLA Student Dashboard</title>
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
      height: 100%;
      width: 100%;
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
      transition: transform 0.2s;
    }
    
    .SearchButton:hover{
      transition: transform 0.2s;
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

    .search-icon-link{
      display: flex;

      width: 78px;
      height: auto;

      justify-content: center;
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


    /* JOIN CLASS */

    .join-overlay{
      display: none;
      position: absolute;
      border: 2px solid white;
      border-radius: 6px 6px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
      top: 10%;
      left: 30%;
      height: fit-content;
      max-height: 55%;
      width: 35%;
      background: rgba(241, 241, 241, 0.85);
      backdrop-filter: blur(5px);
      z-index: 20;
      padding: 20px;
      overflow-y: auto;
    }

    #joinHeader{
      display: flex;
      flex-direction: column;
      gap: 20px;
      margin-bottom: 20px;
    }

    #joinTitle{
      display: flex;
      gap: 10px;
      align-items: center;
    }

    #joinDesc{
      background-color: gainsboro;
      border-radius: 15px;
      padding: 15px;
    }

    #joinClass{
      background:rgb(255, 255, 255);
      border: none;
      color: #7b0000;
      font-weight: bold;
      cursor: pointer;
      padding: 10px 20px;
      border-radius: 15px;
      font-size: 15px;
      transition: transform 0.2s;
    }

    #joinClass:hover{
      background-color: #fff;
      transform: scale(1.1);
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

    .join-con, #joinCode{
      width: auto;
      height: fit-content%;
    }

    #joinCode input[type="text"]{
      height: auto;
      width: 100%;

      padding: 10px;
      margin-bottom: 20px;

      border: none;
      border-radius: 10px;
    }

    .join-SC{
      display: flex;
      gap: 15px;
      height: fit-content;
      width: 100%;
      justify-content: right;
    }
  
    .join-SC button{
      background: #e6e6e6;
      border: none;
      color: #7b0000;
      font-weight: bold;
      cursor: pointer;
      padding: 10px 30px;
      border-radius: 6px 6px;
      font-size: 20px;
    }

    /* SEARCH INPUTS */
    .search-container {
      position: relative;
      display: flex;
      align-items: center;
      gap: 5px;
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
    <div class="title"><img id="logo" src="images/logo.png"><div id="role"><span>STUDENT</span></div></div>
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
        Welcome Student, <?php echo $_SESSION['username']?> !
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
            <div class="search-container">
              <input type="text" placeholder="Search..." class="search-input">
              <label class="SearchButton" onclick="toggleSearch(this)">Search</label>
            </div>
          </div>
        </div>
        
        <div class="list-wrapper">
          <div class="dynamic-list">
            <?php
              $studentID = $_SESSION['studentID'];
              $sql = "SELECT c.*, u.username, 
                      CASE WHEN es.studentID IS NOT NULL THEN 'owned' ELSE 'joinable' END as class_status
                      FROM classroom c
                      JOIN instructor i ON c.instID = i.instID
                      JOIN users u ON i.userID = u.userID
                      LEFT JOIN enrolledstudent es ON es.classroomID = c.classroomID AND es.studentID = ?
                      ORDER BY class_status, c.className";
              $stmt = $conn->prepare($sql);
              $stmt->bind_param('s', $studentID);
              $stmt->execute();
              $result = $stmt->get_result();

              while ($row = $result->fetch_assoc()) {
                $classroomID = htmlspecialchars($row['classroomID']);
                $className = htmlspecialchars($row['className']);
                $classDesc = htmlspecialchars($row['classDesc']);
                $classCode = htmlspecialchars($row['classCode']);
                $creatorName = htmlspecialchars($row['username']);
                $classStatus = $row['class_status'];
            ?>
                <div class="classroom-card" 
                     class-type="<?php echo $classStatus; ?>"
                     classroom-id="<?php echo $classroomID; ?>"
                     classroom-name="<?php echo $className; ?>"
                     classroom-desc="<?php echo $classDesc; ?>"
                     classroom-code="<?php echo $classCode; ?>"
                     classroom-creator="<?php echo $creatorName; ?>">
                    
                    <img src="images/Class_Icon.jpg" alt="Class Icon" class="classroom-icon">
                    <div class="classroom-info">
                      <div class="classroom-title"><?php echo $className; ?></div>
                      <div class="classroom-creator"><?php echo $creatorName; ?></div>
                    </div>
                    
                    <?php if ($classStatus === 'joinable'): ?>
                        <Button id="joinClass" onclick="showJoinOverlay(this)">Join</Button>
                    <?php else: ?>
                        <div class="search-icon-link user-search" onclick="showClassDetails(this)">
                          <img src="images/Search_Icon.jpg" alt="View Class" class="search-image-icon">
                        </div>
                    <?php endif; ?>
                </div>
            <?php } ?>
          </div>
        </div>
      </div>

      <!-- Join Classroom Overlay -->
      <div id="joinOverlay" class="join-overlay">
        <button class="close-btn" onclick="hideClassSubOverlay('joinOverlay')">×</button>
        <div class="join-con">
          <div id="joinHeader">
            <div id="joinClassInfo">
              <div id="joinTitle">
                <div id="joinClassIcon">
                  <img src="images/Class_Icon.jpg" alt="">
                </div>
                <div>
                  <div style="color: black; font-size: 20px; font-weight: bold;" id="joinName">null</div>
                  <div style="color: black; font-size: 13px; font-style: italic;" id="joinCreator">null</div>
                </div>
              </div>
            </div>
            <div id="joinDesc">Null</div>
          </div>

          <div id="joinCode">
            <input type="text" id="classCode" name="classCode" placeholder="Enter Class Code" required>
          </div>

          <div class="join-SC">
            <button type="button" onclick="joinClassroom()">Join</button>
            <button type="button" onclick="hideClassSubOverlay('joinOverlay')">Cancel</button>
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
  // Toggle logout dropdown
  function toggleLogoutDropdown() {
    const dropdown = document.getElementById('logoutDropdown');
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
  }

  // Close dropdown when clicking outside
  document.addEventListener('click', function(e) {
    const profileContainer = document.querySelector('.profile-container');
    const dropdown = document.getElementById('logoutDropdown');
    if (!profileContainer.contains(e.target)) {
      dropdown.style.display = 'none';
    }
  });

  // Show overlay function
  function showOverlay(targetId, backgroundId = null) {
    const overlays = ['classroomOverlay', 'moduleOverlay', 'joinOverlay'];
    const bg = document.getElementById('backgroundContent');

    overlays.forEach(id => {
      const overlay = document.getElementById(id);
      const shouldShow = (id === targetId || (backgroundId && id === backgroundId));
      overlay.classList.toggle('show', shouldShow);
    });

    bg.style.display = 'none';
  }

  // Hide overlay function
  function hideOverlay(targetId) {
    const target = document.getElementById(targetId);
    target.classList.remove('show');

    const anyOpen = document.querySelectorAll('.show').length > 0;
    if (!anyOpen) {
      document.getElementById('backgroundContent').style.display = 'flex';
    }
  }

  // Hide sub-overlay function
  function hideClassSubOverlay(targetId) {
    const target = document.getElementById(targetId);
    target.classList.remove('show');

    const anyOpen = document.querySelectorAll('.show').length > 0;
    if (!anyOpen) {
      document.getElementById('classroomOverlay').classList.add('show');
    }
  }

  // Set class filter function
  function setClassFilter(typeFilter) {
    const cards = document.querySelectorAll('.classroom-card');
    const tabs = document.querySelectorAll('#classroomOverlay .tab');
    const searchInput = document.querySelector('#classroomOverlay .search-input');

    // Update tab styling
    tabs.forEach(tab => {
      tab.classList.toggle('active', tab.textContent.trim().toLowerCase() === typeFilter);
    });

    // Clear any active search
    if (searchInput) {
      searchInput.value = '';
      if (typeof closeInput === 'function') {
        closeInput(searchInput);
      }
    }

    // Filter cards
    cards.forEach(card => {
      const cardType = card.getAttribute('class-type');
      const shouldShow = typeFilter === 'all' || 
                       (typeFilter === 'joinable' && cardType === 'joinable') ||
                       (typeFilter === 'owned' && cardType === 'owned');
      
      card.style.display = shouldShow ? 'flex' : 'none';
    });
  }

  // Show join overlay function
  function showJoinOverlay(element) {
    const classCard = element.closest('.classroom-card');
    if (!classCard) {
      console.error("Error: Classroom card not found.");
      return;
    }

    // Get data attributes
    const classroomData = {
      name: classCard.getAttribute('classroom-name'),
      desc: classCard.getAttribute('classroom-desc'),
      code: classCard.getAttribute('classroom-code'),
      creator: classCard.getAttribute('classroom-creator'),
      id: classCard.getAttribute('classroom-id')
    };

    // Validate required data
    if (!classroomData.id || !classroomData.code) {
      console.error("Missing required classroom data");
      alert("Error: Missing classroom information");
      return;
    }

    // Update overlay fields
    document.getElementById('joinName').textContent = classroomData.name || 'No name provided';
    document.getElementById('joinDesc').textContent = classroomData.desc || 'No description provided';
    document.getElementById('joinCreator').textContent = `Created by: ${classroomData.creator || 'Unknown'}`;
    
    // Store data in global variable
    window.currentJoinClassroom = {
      id: classroomData.id,
      code: classroomData.code
    };

    // Show the overlay
    document.getElementById('joinOverlay').classList.add('show');
    document.getElementById('classCode').value = '';
    document.getElementById('classCode').focus();
  }

  // Join classroom function
  function joinClassroom() {
    if (!window.currentJoinClassroom) {
      console.error("No classroom data available");
      alert("Error: Classroom data missing");
      return;
    }

    const inputCode = document.getElementById('classCode').value.trim();
    const expectedCode = window.currentJoinClassroom.code;
    const classId = window.currentJoinClassroom.id;

    if (inputCode !== expectedCode) {
      alert('The class code you entered is incorrect.');
      return;
    }

    // Prepare the data to send
    const data = {
      classid: classId
    };

    fetch('studentFunctions.php', {
      method: 'POST',
      headers: { 
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        action: 'joinClassroom',
        data: data
      })
    })
    .then(response => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then(result => {
      if (result.success) {
        alert('Classroom joined successfully!');
        hideClassSubOverlay('joinOverlay');
        location.reload(); // Refresh to show updated classroom list
      } else {
        alert('Failed to join classroom: ' + (result.message || 'Unknown error'));
      }
    })
    .catch(error => {
      console.error('Error joining classroom:', error);
      alert('An error occurred while joining the classroom. Please try again.');
    });
  }

  // Show class details function
  function showClassDetails(element) {
    const classCard = element.closest('.classroom-card');
    if (!classCard) {
      console.error("Error: Classroom card not found.");
      return;
    }

    const classid = classCard.getAttribute('classroom-id');
    if (!classid) {
      console.error("Error: Classroom ID not found.");
      return;
    }

    window.location.href = `classroomDetails.php?classid=${encodeURIComponent(classid)}`;
  }

  // Search functionality (keep your existing search functions)
</script>

</body>
</html>

<?php
  mysqli_close($conn);
?>
