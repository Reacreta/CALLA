<?php
  ob_start();
  session_start();
  require_once 'database.php';
  require_once 'authFunctions.php';

  // Read Tables
  $sql = "SELECT * FROM tbl_users";
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
    
    .user-overlay.show, .create-overlay.show {
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

    .left-buttons{
      
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

    .create-SC .creates:hover{
      background-color: #fff;
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

    .classroom-list {
      display: flex;
      flex-direction: column;
      gap: 15px;
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

    /* CREATE CLASS */
    .create-list {
      display: flex;
      flex-direction: column;
      gap: 30px;
    }

    #className {
      width: 500px;
      padding: 15px;
      border: 1px solid #ccc;
      border-radius: 4px;
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


    .module-list {
      display: flex;
      flex-direction: column;
      gap: 15px;
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
    <div class="title">CALLA <span>instructor</span></div>
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
      <button class="nav-btn" onclick="toggleClassroomOverlay()"><img src="images/Class_Icon.jpg" class="User-icon" alt="Classroom Icon"> Classrooms</button>
      <button class="nav-btn" onclick="toggleModuleOverlay()"><img src="images/Module_Icon.jpg" class="User-icon" alt="Module Icon"> Modules</button>
      </div>
    </div>
  
    <div class="main-content">
      <!-- Background Main Content -->
      <div id="backgroundContent" class="background-content">
        Welcome to the Instructor Dashboard
      </div>

      <!-- Classroom Overlay -->
      <div id="classroomOverlay" class="user-overlay">
        <button class="close-btn" onclick="hideOverlay('classroomOverlay')">×</button>
        <h2 style="color: #7b0000; margin-bottom: 20px;">Classrooms</h2>

        <div class="tabs">

          <div class="left-buttons">
            <button class="tab" onclick="setUserTab('All')">All</button>
            <button class="tab" onclick="setUserTab('Joinable')">Joinable</button>
            <button class="tab" onclick="setUserTab('Owned')">Owned</button>
          </div>

          <div class="right-buttons">
            <button class="tab" onclick="toggleCreateOverlay()">Create Classroom</button>
            <div class="search-container">
              <input type="text" placeholder="Search..." class="search-input">
              <label class="SearchButton" onclick="toggleSearch(this)">Search</label>
            </div>
          </div>

        </div>
        
        <div class="classroom-list">
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

        <div id="createOverlay" class="create-overlay">
          <button class="close-btn" onclick="hideCreateOverlay('createOverlay')">×</button>
          <h2 style="color: #7b0000; margin-bottom: 20px;">Create a Class</h2>

          <div class = create-list>
            <div class = create-item1>
              <div class = create-info>
                <label for="className">Class Name:</label>
                <input type="text" id="className" name="className"placeholder="Class Name" required>
              </div>
            </div>
            <div class = create-item2>
              <div class = create-info>
                <textarea rows="20" cols="100" id="classDesc" name="classDesc" placeholder="Class Description" required></textarea>
              </div>
            </div>
            <div class = create-SC>
              <button class = creates type="submit">Create</button>
              <button class = creates onclick="hideCreateOverlay('createOverlay')">Cancel</button>
            </div>
          </div>


        </div>

      <!-- Modules Overlay -->
      <div id="moduleOverlay" class="user-overlay">
        <button class="close-btn" onclick="hideOverlay('moduleOverlay')">×</button>
        <h2 style="color: #7b0000; margin-bottom: 20px;">Modules</h2>

        <div class="tabs">
          <button class="tab active">All</button>
          <button class="tab">Partner</button>
          <button class="tab">Classroom</button>
          <div class="right-buttons">
            <div class="search-container">
              <input type="text" placeholder="Search..." class="search-input">
              <label class="SearchButton" onclick="toggleSearch(this)">Search</label>
            </div>
          </div>
        </div>

        <div class="module-list">

          <?php
            $sql = "SELECT username, userType FROM users WHERE usertype <> 'Administrator'";
            $result = $conn->query($sql);

            while ($row = $result->fetch_assoc()) {
              $displayName = htmlspecialchars($row['username']);
              $role = htmlspecialchars($row['userType']);
          ?>
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
          <?php } ?>
        </div>
      </div>
    </div>
  </div>



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

  function toggleSearch(label) {
  const container = label.closest('.search-container');
  const input = container.querySelector('.search-input');
  const isOpen = input.style.width === '200px';

  if (isOpen) {
    closeInput(input);
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
        console.log(input.value); 
        closeInput(input);
        input.removeEventListener('keydown', handleKey); 
      }
    });
  }
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

  function showOverlay(targetId, backgroundId = null) {
  const overlays = ['classroomOverlay', 'moduleOverlay', 'createOverlay'];
  const bg = document.getElementById('backgroundContent');
  overlays.forEach(id => {
    const overlay = document.getElementById(id);
    const shouldShow = (
      id === targetId || 
      (backgroundId && id === backgroundId)
    );
    overlay.classList.toggle('show', shouldShow);
  });
  bg.style.display = 'none';
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

  function hideCreateOverlay(targetId) {
    const target = document.getElementById(targetId);
    target.classList.remove('show');
    // If no overlays are visible, show the background
    const anyOpen = document.querySelectorAll('.user-overlay.show').length > 0;
    if (!anyOpen) {
      document.getElementById('classroomOverlay').classList.add('show');
    }
  }


  // Aliases for buttons
  function toggleClassroomOverlay() {
    showOverlay('classroomOverlay');
  }

  function toggleModuleOverlay() {
  showOverlay('moduleOverlay');
  }
  function toggleCreateOverlay() {
  showOverlay('createOverlay', 'classroomOverlay');
  }

  function setUserTab(role) {
    const cards = document.querySelectorAll('.user-card');
    const tabs = document.querySelectorAll('.tab');

    cards.forEach(card => {
      const cardRole = card.getAttribute('data-role');
      card.style.display = (role === 'All' || cardRole === role) ? 'flex' : 'none';
    });

    tabs.forEach(tab => tab.classList.remove('active'));
    const activeTab = Array.from(tabs).find(t => t.textContent === role);
    if (activeTab) activeTab.classList.add('active');
  }

  // Automatically select "All" when the page loads
  window.addEventListener('DOMContentLoaded', () => {
    setUserTab('All');
  });

</script>

</body>
</html>

<?php
  mysqli_close($conn);
?>
