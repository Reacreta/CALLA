<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>CALLA Student Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter&family=Goudy+Bookletter+1911&display=swap" rel="stylesheet">
  <style>
    :root {
      --gradient: linear-gradient(65deg,  #330000, #4A0303, #7B0000, #A30505, #C0660E, #D59004);
      --gradient2: linear-gradient(85deg,  #330000, #4A0303, #7B0000, #A30505, #C0660E, #D59004);
    }

    @keyframes grad-anim {
      0%{
        background-position: 0% 0%;}
      100%{
          background-position: 100% 0%;
        }
    }
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
    }

    .header {
      background-image: var(--gradient);
      background-size: 300% 100%;
      background-repeat: no-repeat;
      animation: grad-anim 10s infinite alternate;
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

    .dashboard-container {
      display: flex;
      height: 100%;
    }

    .sidebar {
      background-image: var(--gradient2);
      background-size: 300% 100%;
      background-repeat: no-repeat;
      animation: grad-anim 10s infinite alternate;
      animation-delay: 2s;
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

    .tabs .tab.search {
      position: absolute;
      right: 0;
    }

    .tabs .tab {
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

    .tabs .tab.active, .tab:hover {
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
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>

<body>
  <div class="header">
    <div class="title">CALLA <span>student</span></div>
    <div class="profile-container" onclick="toggleLogoutDropdown()">
      <div class="profile-pic" style="background-image: url('images/profile.jpg');"></div>
      <div class="logout-dropdown" id="logoutDropdown">
        <a href="logout.html">Logout</a>
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
        Student Dashboard
      </div>

      <!-- Classroom Overlay -->
      <div id="classroomOverlay" class="user-overlay">
        <button class="close-btn" onclick="hideOverlay('classroomOverlay')">×</button>
        <h2 style="color: #7b0000; margin-bottom: 20px;">Classrooms</h2>
        <div class="tabs">
          <button class="tab active">All</button>
          <button class="tab">Joined</button>
          <button class="tab search">Search</button>
        </div>
        <div class="classroom-list">
          
          <div class="classroom-item">
            <img src="images/Class_Icon.jpg" alt="Class Icon" class="classroom-icon">
            <div class="classroom-info">
              <div class="classroom-title">English 101</div>
              <div class="classroom-creator">Ms. Reyes</div>
            </div>
            <a href="classroom-details.html?classId=english101" class="search-icon-link">
              <img src="images/Search_Icon.jpg" alt="View Classroom" class="search-image-icon">
            </a>
          </div>

          <div class="classroom-item">
            <img src="images/Class_Icon.jpg" alt="Class Icon" class="classroom-icon">
            <div class="classroom-info">
              <div class="classroom-title">Math 4</div>
              <div class="classroom-creator">Mr. Santos</div>
            </div>
            <a href="classroom-details.html?classId=math4" class="search-icon-link">
              <img src="images/Search_Icon.jpg" alt="View Classroom" class="search-image-icon">
            </a>
          </div>

          <!-- Add more classroom-item divs as needed -->
        </div>
      </div>

      <!-- Modules Overlay -->
      <div id="moduleOverlay" class="user-overlay">
        <button class="close-btn" onclick="hideOverlay('moduleOverlay')">×</button>

        <div class="tabs">
          <button class="tab active">All</button>
          <button class="tab">Personal</button>
          <button class="tab">Classroom</button>
        </div>

        <div class="module-list">
          <div class="module-card">
            <img src="images/Module_Icon.jpg" alt="Module Icon" class="module-icon">
            <div class="module-info">
              <div class="module-title">English Basics</div>
              <div class="module-creator">By Ms. Lim</div>
            </div>
            <a href="module-details.html?moduleId=engbasics" class="search-icon-link">
              <img src="images/Search_Icon.jpg" alt="View Module" class="search-image-icon">
            </a>
          </div>

          <!-- More module-card entries as needed -->
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

  function showOverlay(targetId) {
    const overlays = ['classroomOverlay',  'moduleOverlay'];
    const bg = document.getElementById('backgroundContent');

    overlays.forEach(id => document.getElementById(id).classList.remove('show'));

    const target = document.getElementById(targetId);
    target.classList.add('show');

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

  // Aliases for buttons
  function toggleClassroomOverlay() {
    showOverlay('classroomOverlay');
  }

  function toggleModuleOverlay() {
  showOverlay('moduleOverlay');
  }



</script>

</body>
</html>
