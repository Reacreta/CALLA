<?php
    require_once 'database.php';
    require_once 'authFunctions.php';

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    ob_start();
    session_start();

    $classid = $_GET['classid'] ?? null;
    $accountRole = $_SESSION['accountRole'];

    if (!$classid) {
        die('Class ID is missing.');
    }

    // Set page based on accountRole
    if ($accountRole === 'Administrator') { // if admin
        $title = 'CALLA Admin Dashboard';
        $logoHeader = 'ADMIN';
    }
    else if ($accountRole === 'Instructor') { // if instructor
        $title = 'CALLA Instructor Dashboard';
        $logoHeader = 'INSTRUCTOR';
    }
    else if ($accountRole === 'Student') { // if student
        $title = 'CALLA Student Dashboard';
        $logoHeader = 'STUDENT';
    }
    else {
        die('Role does not comply.');
    }


    // Fetch classroom details from the database
    $sql = "SELECT * FROM classroom c 
            JOIN instructor i ON c.instID = i.instID
            JOIN users u ON u.userID = i.userID
            WHERE c.classroomID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $classid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $classroom = $result->fetch_assoc();

        // debug_console($classroom);
    } 

    // checking ownership
    $checkOwner = 'false'; // set initial
    $instUID = null;
    if ($accountRole === 'Instructor') {
        $uid = $_SESSION['userID'];
        $sql = "SELECT * FROM instructor i
                JOIN users u ON i.userID = u.userID
                WHERE i.userID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $uid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $instUID = $result->fetch_assoc()['instID'];
            // debug_console('Owner ID: '. $instUID);
        }
        $instID = $classroom['instID'];
        $checkOwner = ($instUID === $instID) ? 'true' : 'false';
    }
    

    // fetch instructors
    $sql= "SELECT *
        FROM classinstructor ci
        JOIN instructor i ON i.instID = ci.instID
        JOIN users u ON u.userID = i.userID
        join classroom c ON c.classroomID = ci.classroomID
        WHERE ci.classroomID = ?";
    $stmt->bind_param('s', $classid);
    $stmt->execute();
    $results = $stmt->get_result();
    $instructorsArray = [];
    while ($row = $results->fetch_assoc()) {
        $instructorsArray[] = $row;
    }

    // fetch enrolled students
    $sql= "SELECT *
        FROM enrolledstudent es
        JOIN student s ON es.studentID = s.studentID
        JOIN users u ON u.userID = s.studentID 
        WHERE es.classroomID = ?;";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $classid);
    $stmt->execute();
    $result = $stmt->get_result();
    $studentsArray = [];
    while ($row = $results->fetch_assoc()) {
        $studentsArray[] = $row;
    }

    // fetch classroom modules
    $sql= "SELECT *
        FROM classmodule cm 
        JOIN classinstructor ci ON cm.classInstID = ci.classInstID
        JOIN instructor i ON i.instID = ci.instID
        JOIN users u ON u.userID = i.userID
        JOIN languagemodule lm ON lm.langID = cm.langID
        WHERE cm.classroomID = ?";

    $stmt->bind_param('s', $classid);
    $stmt->execute();
    $result = $stmt->get_result();
    $modulesArray = [];
    while ($row = $results->fetch_assoc()) {
        $modulesArray[] = $row;
    }

    // fetch classroom instructors

    // -- FUNCTIONS --
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        // update classroom
        if (isset($data['updateClass'])) {
            $className = trim($data['className']);
            $classDesc = trim($data['classDesc']);
            $classID = trim($data['classID']);

            $sql = "UPDATE classroom SET className = ?, classDesc = ? WHERE classroomID = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("sss", $className, $classDesc, $classID);
                $stmt->execute();
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => $conn->error]);
            }
            exit;
        }
        // delete classroom
        if (isset($data['deleteClass'])) {
            $classID = trim($data['classID']);

            $sql = "DELETE FROM classroom WHERE classroomID = ?";
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $stmt->bind_param("s", $classID);
                $stmt->execute();
                echo json_encode(['success' => true]);

            } else {
                echo json_encode(['success' => false, 'error' => $conn->error]);
            }
            exit;
        }

        // leave classroom
        if (isset($data['leaveClassroom'])) {
            $classID = trim($data['classID']);
            $role = $data['role'];
            $owner = $data['owner'];
            $userID = $_SESSION['userID'];

            if ($owner === true || $owner === 'true') { 
                // Owner leaves → delete classroom
                $sql = "DELETE FROM classroom WHERE classroomID = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $classID);
                $stmt->execute();
            } else {
                // Normal instructor or student leaving
                if ($role === 'Instructor') {
                    $sql = "DELETE FROM classinstructor WHERE instID = ? AND classroomID = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ss", $instUID, $classID);
                    $stmt->execute();
                } elseif ($role === 'Student') {
                    $sql = "DELETE FROM enrolledstudent WHERE userID = ? AND classroomID = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ss", $userID, $classID);
                    $stmt->execute();
                } else {
                    echo json_encode(['success' => false, 'error' => 'Invalid role']);
                    exit;
                }

                
            }

            echo json_encode([
                'success' => true,
                'redirect' => ($role === 'Instructor') ? 'Instructor.php' : 'Student.php'
            ]);
            exit;
        }
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?php echo $title ?></title>
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

    /*------------------Header---------------------*/
    .header {
      border: none;
      color: white;
      padding: 15px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .logoTitle{
      display: flex;
      flex-direction: row;
    }

    .logoTitle #role{
      display: flex;
      align-items: end;
    }

    .logoTitle #role span{
      font-size: 35px;
      font-family: 'Goudy Bookletter 1911', serif;
    }

    .logoTitle #logo{
      height: 70px;
      width: auto;
    }

    .header .logoTitle span {
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
    /*------------------Dashboard---------------------*/

    .tab {
        font: 20px;
        border: none;
        text-decoration: underline;
        color: #7B0000;
    }

    .dashboard-container {
      display: flex;
      height: 100%;
    }

    .dash-head {
      border: none;
      width: 15%;
      padding: 20px 10px;
      display: flex;
      flex-direction: column;
      gap: 25px;
    }

    .nav-group button {
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;

      font-size: 15px;
    }

    .nav-group {
      background-color: rgba(193, 113, 113, 0.3); 
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

    .editable-input {
        font-size: 1.2rem;
        padding: 6px;
        width: 100%;
    }

    .editable-textarea {
        width: 100%;
        resize: none;
        min-height: 300px;
        font-size: 1rem;
        padding: 6px;
    }


    .dash-main{
        background-color: whitesmoke;
        background-size: cover;
        background-position: center;

        height: 100%;
        width: 100%;
        display: flex;
    }

    #classroomDetails {
        display: flex;
        flex-direction: column;

        width: 70%;
        padding: 20px;
    }

    #classroomHeader {
        
        display: flex;
        align-items: center;
        height: 20%;
        
    }

    #classroomHeader img {
        width: 150px;
        height: 150px;
        margin-right: 20px;
    }

    #classTitle{
        font-size: 30px;
        font-weight: bold;
    }

    #classCreator{
        font-size: 25px;
        font-style: italic;
    }

    #classroomMain {
        background-color: lightgrey;
        display: flex;
        height: 60%;
        border-radius: 30px;
    }

    #classroomDescription {
        display: flex;
        flex-direction: column;
        width: 80%;
        padding: 20px;
    }
    .title{
        font-size: 20px;
        font-weight: bold;
        color: #7b0000;
        margin-bottom: 10px;
    }

    #classDesc{
        height: 100%;
        padding:5px 0;
    }

    #classinfo{
        font-size: 14px;
        font-style: italic;
    }

    #classroomMisc {
        display: flex;
        flex-direction: column;
        width: 20%;
        padding: 20px;
    }

    .list-wrapper{
        height: 85%;
        scrollbar-width: thin;
        scroll-behavior: smooth;
        background-color: lightgrey;
        border-radius: 20px;
    }

    .dynamic-list {
      display: flex;
      flex-direction: column;
      gap: 10px;
      height: 100%;
      overflow-y: scroll;
    }

    #listViews {
        display: flex;
        flex-direction: column;
        width: 30%;
        padding: 20px;
    }

    .user-card{
        background-color: #e0e0e0;
        padding: 10px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .list-icon {
      height: 40px;
      width: 40px;
      border-radius: 50%;
      object-fit: cover;
    }

    .search-image-icon {
      width: 25px;
      height: 25px;
      cursor: pointer;
      border-radius: 50%;
      object-fit: cover;
      transition: transform 0.2s;
    }

    #instructorList{
        display: flex;
        flex-direction: column;
        width: 30%;
        padding: 20px;
    }
    
    #modHeader,#studHeader{
        display: flex;
        justify-content: space-between;
        font-size: 20;
    }

    #moduleList,#studentList{
        height: 100%;
        
    }
    
  </style>
</head>

<body>
    <div class="header">
        <div class="logoTitle"><img id="logo" src="images/logo.png"><div id="role"><span><?php echo $logoHeader ?></span></div></div>
        <div class="profile-container" onclick="toggleLogoutDropdown()">
            <div class="profile-pic" style="background-image: url('images/Human_Icon.jpg');"></div>
            <div class="logout-dropdown" id="logoutDropdown">
            <a href="logout.php">Logout</a>
            </div>
        </div>
    </div>

    <div class="dashboard-container">

        <div class="dash-head">
            <div class="nav-group">
                <button class="nav-btn" id="back" onclick="backToDashboard()">Back</button>
                <!-- Adapts to the accountRole -->
                <?php if ($accountRole === 'Administrator' || $accountRole === 'Instructor' && $checkOwner === 'true'): ?>
                    <button class="nav-btn" id="editBtn" onclick="editClass()">Edit</button>
                    <button id='cancel' class="nav-btn" style="display:none;" onclick="cancelEdit()">Cancel</button>
                    <button id='save' class="nav-btn" style="display:none;" onclick="saveEdit()">Save</button>
                    <button class="nav-btn" id="deleteBtn" onclick="deleteClass()">Delete</button>
                <?php endif; ?>
                <?php if ($accountRole === 'Instructor' || $accountRole === 'Student'): ?>
                    <button class="nav-btn" id="leaveBtn" onclick="leaveClass()">Leave</button>
                <?php endif; ?>
            </div>
        </div>

        <div class="dash-main">
            <div id="classroomDetails">
                <div id="classroomHeader">
                    <img src="images/Class_Icon.jpg" alt="">
                    <div id= "classTitleCon">
                        <div id="classTitle"><?php echo $classroom['className']?></div>
                        <div id="classCreator"><?php echo $classroom['username']?></div>
                    </div>
                </div>
                <div id="classroomMain">
                    <div id="classroomDescription">
                        <div class="title">Description:</div>
                        <div id="classDesc"><?php echo $classroom['classDesc']?></div>
                        <div id="classinfo">
                            <div id="classID">Classroom ID: <?php echo $classroom['classroomID']?></div>
                            <div id="classCode">Code: <?php echo $classroom['classCode']?></div>
                        </div>
                    </div>
                    <div id="instructorList">
                        <div id="instructorHeader">
                            <div class="title">Instructor:</div>
                        </div>
                        <div class="list-wrapper">
                            <div class="dynamic-list">
                                <?php
                                    foreach ($instructorsArray as $row){
                                ?>
                                    <div class="user-card">
                                        <img src="images/Human_Icon.jpg" alt="Module Icon" class="list-icon">
                                        <div class="user-info">
                                            <div class="username"><?= htmlspecialchars($row['username']) ?></div>
                                            <div class="progress">0.0%</div>
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
                    </div>
                </div>
            </div>
            <div id="listViews">
                <div id="studentList">
                    <div id="studHeader">
                        <div class="title"> Student: </div>
                        <button class="tab" type="button" onclick="">View All</button>
                    </div>
                    <div class="list-wrapper">
                        <div class="dynamic-list">
                            <?php
                                foreach ($studentsArray as $row){
                            ?>
                                <div class="user-card">
                                    <img src="images/Human_Icon.jpg" alt="Module Icon" class="module-icon">
                                    <div class="user-info">
                                        <div class="username"><?= htmlspecialchars($row['username']) ?></div>
                                        <div class="progress">0.0%</div>
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
                </div><!-- End Module List -->

                <div id="moduleList">
                    <div id="modHeader">
                        <div class="title"> Modules: </div>
                        <button class="tab" type="button" onclick="">View All</button>
                    </div>
                    <div class="list-wrapper">
                        <div class="dynamic-list">
                            <?php
                                foreach ($modulesArray as $row){
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
                </div><!-- End Module List -->

            </div>
        </div>
    </div><!-- End dashboard-container-->

    <script>
        // Event Listeners
        document.addEventListener('click', function (e) {
            const profileContainer = document.querySelector('.profile-container');
            const dropdown = document.getElementById('logoutDropdown');
            if (!profileContainer.contains(e.target)) {
            dropdown.style.display = 'none';
            }
        });

        // Functions
        function toggleLogoutDropdown() {
            const dropdown = document.getElementById('logoutDropdown');
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        }

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

                // Search Sub Funcs
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

        // -- Classroom Functions --

        const accountRole = "<?php echo htmlspecialchars($accountRole, ENT_QUOTES); ?>"; // declare role variable
        const checkOwner = "<?php echo htmlspecialchars($checkOwner, ENT_QUOTES); ?>";

        // Back Button
        function backToDashboard() {
            if (accountRole === 'Administrator') {
                window.location.href = 'Admin.php';
            } else if (accountRole === 'Instructor') {
                window.location.href = 'Instructor.php';
            } else if (accountRole === 'Student') {
                window.location.href = 'Student.php';
            } else {
                alert('No account role detected. Please login again.');
            }
        }

        // when using the functions (except back)
        function notifyAndRedirect(message, redirectUrl) {
            const successDiv = document.createElement('div');
            successDiv.textContent = message;

            successDiv.style.position = 'absolute';
            successDiv.style.display = 'flex';
            successDiv.style.margin = '20px auto';
            successDiv.style.padding = '15px 25px';
            successDiv.style.backgroundColor = '#d4edda';
            successDiv.style.color = '#155724';
            successDiv.style.border = '1px solid #c3e6cb';
            successDiv.style.borderRadius = '8px';
            successDiv.style.width = 'fit-content';
            successDiv.style.fontFamily = 'Inter, sans-serif';
            successDiv.style.fontSize = '16px';
            successDiv.style.textAlign = 'center';
            successDiv.style.boxShadow = '0 0 10px rgba(0, 0, 0, 0.1)';
            successDiv.style.zIndex = '1000';
            successDiv.style.left = '0';
            successDiv.style.right = '0';
            successDiv.style.top = '30px';
            successDiv.style.justifyContent = 'center';

            document.body.appendChild(successDiv);

            if (redirectUrl === 'reload')
                setTimeout(() => {
                successDiv.remove();
                window.location.reload();
            }, 3000);
            else
                setTimeout(() => {
                    successDiv.remove();
                    window.location.href = redirectUrl;
                }, 3000);
        }


        // Editing
        let originalTitle = '';
        let originalDesc = '';

        function editClass() {
            const titleEl = document.getElementById('classTitle');
            const descEl = document.getElementById('classDesc');
            const editBtn = document.getElementById('editBtn');
            const deleteBtn = document.getElementById('deleteBtn');
            const leaveBtn = document.getElementById('leaveBtn');
            const editControls = document.getElementById('editControls');

            // Store original values
            originalTitle = titleEl.innerText;
            originalDesc = descEl.innerText;

            // Replace with editable fields
            titleEl.outerHTML = `<input id="editTitle" type="text" value="${originalTitle}" class="editable-input">`;
            descEl.outerHTML = `<textarea id="editDesc" class="editable-textarea">${originalDesc}</textarea>`;

            // Hide other buttons and show Save/Cancel
            document.querySelectorAll('.nav-btn').forEach(btn => {
                btn.style.display = 'none';
            });
            document.getElementById('cancel').style.display = 'flex';
            document.getElementById('save').style.display = 'flex';
        }

        function cancelEdit() {
            // Revert back to original content
            document.getElementById('editTitle').outerHTML = `<div id="classTitle">${originalTitle}</div>`;
            document.getElementById('editDesc').outerHTML = `<div id="classDesc">${originalDesc}</div>`;

            // Show original buttons again
            document.querySelectorAll('.nav-btn').forEach(btn => {
                btn.style.display = 'flex';
            });
            document.getElementById('cancel').style.display = 'none';
            document.getElementById('save').style.display = 'none';

        }

        function saveEdit() {
            const classTitle = document.getElementById('editTitle').value;
            const classDesc = document.getElementById('editDesc').value;

            if (classTitle.trim() === '' || classDesc.trim() === '') {
                alert('Title and description cannot be empty.');
                return;
            }

            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    updateClass: true,
                    className: classTitle,
                    classDesc: classDesc,
                    classID: <?= json_encode($classid) ?>
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    notifyAndRedirect('Changes updated sucessfully!', 'reload');
                } else {
                    alert('Update failed.');
                    console.error(data.error);
                }
            });

            
        }

        // Deletion
        function deleteClass() {
            const confirmed = confirm("Are you sure you want to delete this classroom?");
            message = 'Classroom Deleted Successfully.';
            if (confirmed) {
                // Add classroom deletion process here
                fetch('', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        deleteClass: true,
                        classID: <?= json_encode($classid) ?>
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // redirection
                        if (accountRole === 'Administrator') {
                            notifyAndRedirect(message, 'Admin.php');
                            exit();
                        } else if (accountRole === 'Instructor') {
                            notifyAndRedirect(message, 'Instructor.php');
                            exit();
                        } else {
                            alert('No account role detected. Please login again.');
                        }
                    } else {
                        alert('Deletion failed.');
                        console.error(data.error);
                    }
                });
                

            }
        }

        // Leaving
        function leaveClass() {
            // check if owner
            const isOwner = checkOwner === 'true';
            let confirmed = false;

            if (isOwner) {
                confirmed = confirm("Are you sure you want to leave this classroom? As the owner, leaving will permanently delete the classroom and all its contents.");
            } else {
                confirmed = confirm("Are you sure you want to leave this classroom?");
            }

            if (!confirmed) return;

            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    leaveClassroom: true,
                    classID: <?= json_encode($classroom['classroomID']) ?>,
                    role: "<?= $accountRole ?>",
                    owner: isOwner
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const message = isOwner 
                        ? 'Classroom deleted successfully.' 
                        : 'You have left the Classroom Successfully.';
                    // Redirect based on role
                    if (accountRole === 'Instructor') {
                        notifyAndRedirect(message, 'Instructor.php');
                    } else if (accountRole === 'Student') {
                        notifyAndRedirect(message, 'Student.php');
                    } else {
                        alert('No account role detected. Please login again.');
                    }
                } else {
                    alert('Failed to leave the classroom.');
                    console.error(data.error);
                }
            })
            .catch(err => {
                console.error('Error leaving classroom:', err);
                alert('An error occurred while trying to leave the classroom.');
            });
        }
      
    </script>
</body>
</html>

<?php
  mysqli_close($conn);
?>
