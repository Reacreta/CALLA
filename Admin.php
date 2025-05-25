<?php
ob_start();
session_start();
require_once 'database.php';
require_once 'authFunctions.php';

sessionCheck('Administrator');

// Fetch all users
$sql = "SELECT * FROM users";
$result = $conn->query($sql);

// Setup search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_param = "%" . $conn->real_escape_string($search) . "%";

// WHERE clause
$where_clause = "usertype IN ('Student', 'Instructor')";
if (!empty($search)) {
    $where_clause .= " AND username LIKE '$search_param'";
}

// Filtered user fetch
$sql = "SELECT * FROM users WHERE $where_clause ORDER BY username ASC";
$result = $conn->query($sql);


// classroom details
if (isset($_GET['classroomID'])) {
    $classroomID = $_GET['classroomID'];
    
    // Debug log
    error_log("Fetching classroom details for ID: " . $classroomID);

    // Fetch classroom
    $sql = "SELECT classroom.className, classroom.code, classroom.createdAt, classroom.description, users.username AS creator, instructor.instID
            FROM classroom
            JOIN instructor ON classroom.instID = instructor.instID
            JOIN users ON instructor.userID = users.userID
            WHERE classroom.classroomID = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        echo json_encode(['error' => 'Database error: ' . $conn->error]);
        exit;
    }
    
    $stmt->bind_param("i", $classroomID);
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        echo json_encode(['error' => 'Database error: ' . $stmt->error]);
        exit;
    }
    
    $result = $stmt->get_result();
    $classroom = $result->fetch_assoc();

    if (!$classroom) {
        error_log("No classroom found for ID: " . $classroomID);
        echo json_encode(['error' => 'Classroom not found']);
        exit;
    }

    error_log("Found classroom: " . json_encode($classroom));

    // Instructor name
    $instructorName = $classroom['creator'];

    // Fetch students
    $students = [];
    $sql = "SELECT users.username FROM student
            JOIN users ON student.userID = users.userID
            WHERE student.classroomID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $classroomID);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $students[] = [
            'name' => $row['username'],
            'role' => 'Student'
        ];
    }

    // Fetch modules
    $modules = [];
    $sql = "SELECT title, source FROM module WHERE classroomID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $classroomID);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $modules[] = [
            'title' => $row['title'],
            'source' => $row['source']
        ];
    }

    // ✅ Respond with JSON
    $response = [
        'className' => $classroom['className'],
        'code' => $classroom['code'],
        'created' => $classroom['createdAt'],
        'creator' => $classroom['creator'],
        'instructor' => $instructorName,
        'description' => $classroom['description'], // now real!
        'students' => $students,
        'modules' => $modules
    ];

    header('Content-Type: application/json');
    echo json_encode($response);
    exit; // ✅ stop here so rest of page doesn't load
}

if(isset($_POST["createPartner"])) {
    $partnerName = $_POST["partnerName"];
    $partnerDesc = $_POST["partnerDesc"];
    $partnerEmail = $_POST["partnerEmail"];
    $partnerContact = $_POST["partnerContact"];
    $partnerID = generateID("P",9);

    $stmt = $conn->prepare("INSERT INTO partner VALUES(?,?,?,?,?)");
    $stmt->bind_param('sssss', $partnerID,$partnerName,$partnerDesc,$partnerEmail,$partnerContact);
    $stmt->execute(); 
    $stmt->close();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<script>
  const allLogs = <?php echo json_encode($allLogs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
</script>

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

  button {
    border: none;
    background: none;
    padding: 0;
    margin: 0;
    outline: none;
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
    border-radius: 50%; 
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

  .search-icon-link {
    border: none;
    background: none;
    padding: 0;
    margin: 0;
    outline: none;
    display: flex;
    align-items: center;
    justify-content: center;
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

  .list-wrapper{
    flex: 1;
    height: 100%;
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
    padding: 15px;
    border-radius: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .user-info,.user-title {
    display: flex;
    align-items: center;
    gap: 12px;
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

  textarea {
      width: 600px;
      height: 200px;
      padding: 15px;
      resize: none;
      border: 1px solid #ccc;
      border-radius: 4px;
    }

  .search-image-icon {
    width: 35px;
    height: 35px;
    cursor: pointer;
    border-radius: 50%;
    object-fit: cover;
    transition: transform 0.2s;
    border: none;
    outline: none !important;
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

  .edit-profile-link { /* non functioning */ 
    color: #7b0000;
    text-decoration: none;
    font-size: 14px;
    position: absolute;
    right: 20px;
    bottom: 20px;
  }

  .user-title img {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
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
    gap: 20px;
    padding: 0 10px;
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

  .delete-btn, #viewPartnerOverlay .cd-edit-btn {
    background-color: #7b0000;
    color: white;
  }

  .deactivate-btn {
    background-color: #7b0000;
    color: white;
  }


  /* userchecklogs */ 
  #userChecklogsOverlay {
    display: none;
    background: rgba(255, 255, 255, 0.95);
    max-width: 1000px;
    width: 90%;
    height: 700px;
    margin: auto;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    z-index: 1000;
  }

  .overlay-wrapper {
    position: relative;
    width: 100%;
    height: 100%;
    padding: 20px;
    box-sizing: border-box;
  }

  #userChecklogsOverlay .overlay-content {
    background: #fff;
    border-radius: 8px;
    padding: 20px;
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 70px;
    display: flex;
    flex-direction: column;
    overflow: hidden; /* prevent double scrollbar */
  }

  .check {
    height: 750px; /* enough for ~10 entries */
    overflow-y: auto;
    padding: 0 10px;
    margin-top: 10px;
    scrollbar-width: thin; /* Firefox */
    scrollbar-color: #a00 #f0f0f0; /* Firefox */
  }

  .logs-header {
    display: grid;
    grid-template-columns: 1.5fr 3fr 1.5fr;
    padding: 12px 20px;
    background-color: #dcdcdc;
    border-radius: 12px;
    font-weight: bold;
    font-size: 16px;
    color: #7b0000;
    margin-bottom: 15px;
    text-align: center;
  }

  #log-entry {
    display: grid;
    grid-template-columns: 1.5fr 3fr 1.5fr;
    padding: 10px 20px; /* add vertical padding */
    background-color: #f1f1f1;
    border-radius: 12px;
    margin-bottom: 10px;
    font-size: 15px;
    align-items: center;
    gap: 10px;
    width: 100%; /* ensure full width */
    box-sizing: border-box; /* includes padding in total size */
  }

  .log-entry:nth-child(even) {
    background-color: #e0e0e0;
  }

  .user-col,
  .action-col,
  .date-col {
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
  }

  .date-col {
    color: #7b0000;
    font-weight: bold;
    font-size: 14px;
  }

  #userChecklogsOverlay .logs-close-btn {
    position: absolute;
    bottom: 20px;
    right: 20px;
    background-color: #7b0000;
    color: #fff;
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    display: inline-block;
    width: auto;
    height: auto;
    line-height: 1.2;
  }

  #userChecklogsOverlay .logs-close-btn:hover {
    background-color: #5a0000;
  }


  /* Classroom Overlays */
  .classroom-item {
    background-color: #e0e0e0;
    padding: 15px;
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


  /* Classroom Details Overlay */
  #viewClassroomDetailsOverlay {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 60%;
    height: 80%;
    background: rgba(255, 255, 255, 0.95);
    border: 2px solid white;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    z-index: 10;
    padding: 20px;
  }

  .cd-content-wrapper {
    height: 100%;
    padding: 20px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }

  /* Header Section */
  #cd-header-section {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 40px;
  }

  .cd-icon-wrapper {
    width: 80px;
    height: 80px;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  .cd-class-icon {
    width: 50px;
    height: 50px;
  }

  #cdClassName {
    font-size: 20px;
    font-weight: 600;
    color: #333;
  }

  #cdCreator {
    font-size: 16px;
    color: #666;
  }

  #cd-course-title {
    color: #333;
    font-size: 24px;
    font-weight: 600;
  }

  #cd-creator-name {
    color: #666;
    font-size: 16px;
  }

  /* Main Grid */
  .cd-main-grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: 30px;
    height: 100%;
  }

  /* Left Column */
  .cd-left-column {
    display: flex;
    flex-direction: column;
    gap: 20px;
  }

  /* Right Column - remove global scroll */
  .cd-right-column {
    display: flex;
    flex-direction: column;
    gap: 20px;
  }

  /* Card Styles */
  .cd-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  }

  /* Section Title */
  .cd-section-title {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 15px;
  }

  /* Metadata */
  #cd-metadata {
    display: flex;
    gap: 20px;
    margin: 15px 0;
    padding: 15px 0;
    border-top: 1px solid #eee;
    border-bottom: 1px solid #eee;
  }

  .cd-metadata-item {
    color: #666;
  }

  .cd-label {
    font-weight: 500;
    margin-right: 5px;
  }

  /* Description */
  #cd-description-container {
    background: #f8f8f8;
    border-radius: 6px;
    padding: 10px;
    position: relative;
  }

  #editBtn {
    position: absolute;
    top: 12px;
    right: 10px;
  }

  .cd-edit-btn {
    padding: 6px 12px;
    font-size: 14px;
    border-radius: 4px;
    height: 100%;
    background-color: #007bff;
    color: white;
    border: none;
    cursor: pointer;
    width: 130px; /* Set your desired width */
    text-align: center;
  }

  .cd-edit-btn:hover {
    background-color: #0056b3;
  }

  #cd-description-text {
    color: #555;
    line-height: 1.6;
    margin-bottom: 20px;
  }

  /* Editable inputs */
  .editable-input {
    width: 100%;
    font-size: 18px;
    padding: 6px 10px;
    margin-bottom: 10px;
    border-radius: 4px;
    border: 1px solid #ccc;
  }

  .editable-textarea {
    width: 100%;
    height: 85%;
    font-size: 16px;
    padding: 10px;
    border-radius: 4px;
    border: 1px solid #ccc;
    resize: vertical;
  }

  .cd-instructorlist,
  .cd-studentlist,
  .cd-modulelist {
    height: 150px;
    width: 100%;
    overflow-y: auto;
    padding-right: 8px;
    scrollbar-width: thin;
    scrollbar-color: #a00 #f0f0f0;
  }

  .cd-instructorlist::-webkit-scrollbar,
  .cd-studentlist::-webkit-scrollbar,
  .cd-modulelist::-webkit-scrollbar {
    width: 6px;
  }
  .cd-instructorlist::-webkit-scrollbar-track,
  .cd-studentlist::-webkit-scrollbar-track,
  .cd-modulelist::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
  }
  .cd-instructorlist::-webkit-scrollbar-thumb,
  .cd-studentlist::-webkit-scrollbar-thumb,
  .cd-modulelist::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
  }
  .cd-instructorlist::-webkit-scrollbar-thumb:hover,
  .cd-studentlist::-webkit-scrollbar-thumb:hover,
  .cd-modulelist::-webkit-scrollbar-thumb:hover {
    background: #999;
  }

  /* List Item Styles */
  .cd-student-card, .cd-module-card {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 12px;
    justify-content: left;
    border-radius: 6px;
    background-color: #f8f8f8;
    margin-bottom: 10px;
    width: 100%;
  }

  .cd-modulelist .module-card {
    margin: 0;
    padding: 0;
    background: none;
  }

  .cd-list-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
  }

  .cd-student-info h3, .cd-module-info h3 {
    font-size: 16px;
    color: #333;
  }

  .cd-student-info p, .cd-module-info p {
    font-size: 14px;
    color: #666;
  }

  /* Footer */
  .cd-actions {
    display: flex;
    justify-content: flex-end;
    padding-top: 10px;
  }

  .cd-actions-right {
    display: flex;
    gap: 10px;
  }

  .cd-btn {
    padding: 10px 25px;
    border-radius: 4px;
    border: none; 
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
  }

  .cd-btn-logs, .cd-btn-delete {
    background: #8b0000;
    color: white;
  }

  .cd-btn-close {
    background: #ccc;
    color: #333;
  }

    /* Create Module Overlay */
  .view-lesson {
    border: none;
    background: none;
    padding: 0;
    margin: 0;
    cursor: pointer;
    outline: none;
  }

  .create-module-overlay {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);

    border: 2px solid white;
    border-radius: 6px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    height: fit-content;
    width: 50%;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(5px);
    z-index: 20;
    padding: 20px;
    overflow-y: auto;
}

  #cdDesc.cd-card{
    height: 70%;
  }

  #cd-description-container{
    height: 85%;
  }

  #cd-description-text{
    height: 90%;
    overflow-y: auto;
  }

  #template{
    border-radius: 15px;
    background-color: lightgray;
    padding: 15px;
  }

  .create-module-SC, .create-module-con, .view-module-SC{
    margin-top: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    justify-content: right;
  }

  #classroomIDField{
    height: auto;
    width: 100%;
    padding: 10px;

    border: lightgray 1px solid;
    border-radius: 10px;
  }

  #files{
    height: auto;
    width: 100%;
    padding: 10px;

    border: lightgray 1px solid;
    border-radius: 10px;
  }

  #partnerIDField,
  #files {
    padding: 10px 14px;
    border: lightgray 1px solid;
    border-radius: 10px;
    width: 100%;          /* Makes it fill the .create-module-con */
    box-sizing: border-box;
    font-size: 12px;
    font-family: 'Segoe UI', sans-serif;
  }

  #files::-webkit-file-upload-button,
  #files::file-selector-button {
    background-color: #7b0000;
    color: white;
    border: none;
    padding: 8px 12px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
  }

  #files:hover::file-selector-button,
  #files:hover::-webkit-file-upload-button {
    background-color: #5e0000;
  }
  
  .create-mod-btn{
    background-color: #7b0000;
    border: none;
    color: white;
    font-weight: bold;
    cursor: pointer;
    padding: 10px 10px;
    border-radius: 6px 6px;
    font-size: 15px;
  }

  #viewModule{
    background: #e6e6e6;
    border: none;
    color: #7b0000;
    font-weight: bold;
    cursor: pointer;
    border-radius: 100%;
    font-size: 15px;
    height: auto;
    transition: transform 0.2s;
  }

  /* View Module Overlay */
  #viewModuleOverlay {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 50%;
    max-height: 80vh;
    background: rgba(255, 255, 255, 0.95);
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.25);
    padding: 25px;
    overflow-y: auto;
    z-index: 15;
    backdrop-filter: blur(5px);
  }
    
  #viewModuleInfo{
    display: flex;
    flex-direction: column;
    gap: 20px;
    margin-bottom: 20px;
  }

  #viewModuleTitle{
    display: flex;
    gap: 10px;
    align-items: center;
    font-size: 25px;
    font-weight: bold;
    color: black;
  }

  #viewModuleInfoText{
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  #viewModuleTitle img{
    width: 100px;
    height: 100px;
  }

  #viewModuleDesc{
    background-color: gainsboro;
    border-radius: 15px;
    padding: 15px;
    font-size: 13px;
    color: #444;
  }
    
  #viewModuleClass{
    font-size: 15px;
    color: #444;
  }

  #lessonList .list-wrapper{
    height: 260px;
  }
 
  #lessonListTitle{
    font-size: 20px;
    font-weight: bold;
    color: #7b0000;
    margin-bottom: 10px;
  }

  /* View Lesson Overlay */
  #viewLessonOverlay {
    display: none;
    position: fixed;
    border: 2px solid white;
    border-radius: 6px 6px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    height: fit-content;
    width: 50%;
    background: rgba(241, 241, 241, 0.85);
    backdrop-filter: blur(5px);
    z-index: 20;
    padding: 20px;
    overflow-y: auto;
  }

  #viewLessonTitle {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
  }

  #viewLessonTitle img {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
  }

  #viewLessonName {
    font-size: 1.5rem;
    font-weight: bold;
    color: #333;
  }
    
  .view-lesson{
    background: none;
    border: none;
  }
   
  #viewLessonDesc {
    margin-bottom: 20px;
    height: auto;
  }

  #viewLessonDescText {
    font-size: 1rem;
    color: #555;
    line-height: 1.5;
    background: #f9f9f9;
    padding: 10px;
    border-radius: 8px;
    border: 1px solid #ddd;
  }

  /* Vocabulary section styling */
  #viewLessonWords {
    margin-top: 20px;
  }

  #viewLessonWordsTitle {
    font-size: 1.2rem;
    font-weight: bold;
    margin-bottom: 10px;
    color: #444;
  }

  .list-wrapper {
    overflow-x: auto;
    height: 85%; 
  }

  .dynamic-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
  }

  .dynamic-table th, .dynamic-table td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
  }

  .dynamic-table th {
    background-color: #f4f4f4;
    font-weight: bold;
  }

  .dynamic-table tr:nth-child(even) {
    background-color: #f9f9f9;
  }

  .dynamic-table tr:hover {
    background-color: #f1f1f1;
  }

  /* Close button styling */
  #viewLessonOverlay .close-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    background: none;
    border: none;
    font-size: 1.5rem;
    font-weight: bold;
    color: #555;
    cursor: pointer;
  }

  #viewLessonOverlay .close-btn:hover {
    color: #000;
  }
    
  /* partners CSS */ 

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

  .NewP {
    background-color: #7b0000;
    margin-right: 15px;
    color: white;
    border: none;
    border-radius: 20px;
    padding: 10px 15px;
    cursor: pointer;
    z-index: 1;
  }

  .NewP:hover {
    transition: transform 0.2s;
    transform: scale(1.1);
  }

  .create-overlay { 
    display: none;
    position: fixed;
    border: 2px solid white;
    border-radius: 6px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
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

  #viewPartnerOverlay {
    display: none;
    position: fixed;
    border: 2px solid white;
    border-radius: 6px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    height: fit-content;
    width: 30%;
    background: rgba(241, 241, 241, 0.85);
    backdrop-filter: blur(5px);
    z-index: 20;
    padding: 20px;
    overflow-y: auto;
  }

  #viewPartnerHeader {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
  }

  #viewPartnerHeader h2 {
    color: #7b0000; /* Dark red color for the title */
    font-size: 24px;
    font-weight: bold;
  }

  #viewPartnerHeader .close-btn {
    background: none;
    border: none;
    font-size: 20px;
    font-weight: bold;
    color: #666;
    cursor: pointer;
  }

  #viewPartnerHeader .close-btn:hover {
    color: #000; /* Darker color on hover */
  }

  #viewPartnerMain {
    display: flex;
    flex-direction: column;
    gap: 20px;
  }

  #viewPartnerInfo {
    display: flex;
    flex-direction: column;
    gap: 15px;
  }

  #viewPartnerTitle {
    display: flex;
    align-items: center;
    gap: 15px;
  }

  #viewPartnerTitle img {
    width: 50px;
    height: 50px;
    border-radius: 10px; /* Slightly rounded corners */
    object-fit: cover;
  }

  #viewPartnerName {
    font-size: 20px;
    font-weight: bold;
    color: #333; /* Dark gray for text */
  }

  #viewPartnerDesc {
    background: #f8f8f8; /* Light gray background */
    padding: 15px;
    border-radius: 8px;
    border: 1px solid #ddd; /* Subtle border */
  }

  #viewPartnerDescText {
    font-size: 14px;
    color: #555; /* Medium gray for text */
    line-height: 1.6;
  }

  #viewPartnerContact,
  #viewPartnerEmail {
    display: flex;
    flex-direction: column;
    gap: 5px;
  }
  #viewPartnerContactInfo{
    display: flex;
    justify-content: left;
    gap: 15px;
  }

  #viewPartnerContactTitle,
  #viewPartnerEmailTitle {
    font-size: 16px;
    font-weight: bold;
    color: #7b0000; /* Dark red for section titles */
  }

  #viewPartnerContactText,
  #viewPartnerEmailText {
    font-size: 14px;
    color: #333; /* Dark gray for text */
  }

  #viewPartnerSC {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
  }

  #viewPartnerSC .create-mod-btn {
    background: #e6e6e6; /* Light gray background */
    border: none;
    color: #7b0000; /* Dark red text */
    font-weight: bold;
    cursor: pointer;
    padding: 10px 20px;
    border-radius: 6px;
    font-size: 14px;
  }

  #viewPartnerSC .create-mod-btn:hover {
    background: #fff; /* White background on hover */
    color: #5a0000; /* Darker red text on hover */
  }
        
  .user-overlay.show, .create-overlay.show, #userChecklogsOverlay .show, #viewClassroomDetailsOverlay.show, #viewModuleOverlay.show, #viewLessonOverlay.show, #viewPartnerOverlay.show {
    display: block;
  }

  @media (max-width: 1100px) {
      .nav-group, .sidebar {
        width: fit-content;
      }
      .nav-btn{
        width: fit-content;
        background-color: none;
      }
      .nav-btn div {
        display: none;
      }

      .cd-main-grid {
        overflow-y: auto;
        grid-template-columns: 1fr;
        scrollbar-width: thin; /* Firefox */
        scrollbar-color: #a00 #f0f0f0; /* Firefox */
      }
    }

  </style>
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>

<body>

  <div class="header">
    <div class="title"><img id="logo" src="images/logo.png"><div id="role"><span>ADMIN</span></div></div>
    <div class="profile-container" onclick="toggleLogoutDropdown()">
      <div class="profile-pic" style="background-image: url('images/profile.jpg');"></div>
      <div class="logout-dropdown" id="logoutDropdown">
        <a onclick="notifyAndRedirect('You have been successfully logged out.', 'logout.php')">Logout</a>
      </div>
    </div>
  </div>

  <div class="dashboard-container">
    <div class="sidebar">
      <div class="nav-group">
      <!-- Users -->
      <button class="nav-btn" onclick="showOverlay('userOverlay')">
        <img src="images/Human_Icon.jpg" class="User-icon" alt="User Icon"> <div>Users</div>
      </button>
      
      <!-- Classrooms -->
      <button class="nav-btn" onclick="showOverlay('classroomOverlay')">
        <img src="images/Class_Icon.jpg" class="User-icon" alt="Classroom Icon"><div>Classrooms</div> 
      </button>
      
      <!-- Modules -->
      <button class="nav-btn" onclick="showOverlay('moduleOverlay')">
        <img src="images/Module_Icon.jpg" class="User-icon" alt="Module Icon"><div>Modules</div> 
      </button>
      
      <!-- Partners -->
      <button class="nav-btn" onclick="showOverlay('partnersOverlay')">
        <img src="images/Partners_Icon.jpg" class="User-icon" alt="Partners Icon"><div>Partners</div> 
      </button>
      </div>
    </div>

    <div class="main-content">
      <!-- Background Main Content -->
      <div id="backgroundContent" class="background-content">
          Welcome Admin, <?php echo $_SESSION['username']?>!
      </div>

      <div id="userOverlay" class="user-overlay" overlay-type="user">
          <button class="close-btn" onclick="hideOverlay('userOverlay')">×</button>
          <h2 style="color: #7b0000; margin-bottom: 20px;">Users</h2>

          <div class="tabs">
            <button class="tab" onclick="setUserTab('All')">All</button>
            <button class="tab" onclick="setUserTab('Student')">Student</button>
            <button class="tab" onclick="setUserTab('Instructor')">Instructor</button>
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
                 data-uid="<?php echo htmlspecialchars($row['userID']); ?>"
                 data-active="<?php echo htmlspecialchars($row['active']); ?>">
              <div class="user-info">
                <div class="user-title">
                  <img src="images/Human_Icon.jpg" alt="User Photo" class="user-icon">
                  <div>
                    <div><strong><?php echo $displayName; ?></strong></div>
                    <div><?php echo $role; ?></div>
                  </div>
                  
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
      </div>

    <!-- User Details Overlay -->
      <div id="userDetailsOverlay" class="create-overlay">
        <button class="close-btn" onclick="closeOverlay('userDetailsOverlay')">×</button>
        <h2 style="color: #7b0000; margin-bottom: 20px;">User Details</h2>
        
        <div class="user-details-content">
          <div class="user-profile-section">
            <div class="user-profile-header">
              <img src="images/Human_Icon.jpg" alt="User Photo" class="user-profile-img" />
              <div class="user-profile-info">
                <div class="user-header-flex">
                  <div>
                    <div id="userDetailName" class="user-detail-name"></div>
                    <div id="userDetailRole" class="user-detail-role"></div>
                  </div>
                  <div class="uid-display" id="userDetailUID"></div>
                </div>
              </div>
            </div>
            
            <div class="user-details-grid">
              <div class="detail-item">
                <label>First Name:</label>
                <div id="userDetailFirstName"></div>
              </div>
              <div class="detail-item">
                <label>Last Name:</label>
                <div id="userDetailLastName"></div>
              </div>
              <div class="detail-item">
                <label>Gender:</label>
                <div id="userDetailGender"></div>
              </div>
              <div class="detail-item">
                <label>Email:</label>
                <div id="userDetailEmail"></div>
              </div>
              <div class="detail-item">
                <label>Contact:</label>
                <div id="userDetailContact"></div>
              </div>
              <div class="detail-item">
                <label>Date of Birth:</label>
                <div id="userDetailDOB"></div>
              </div>
            </div>
          </div>
          
          <div class="user-actions">
            <button class="action-btn" onclick="checkUserLogs()">Check Logs</button>
            <button class="action-btn delete-btn" onclick="deleteUser()">Delete</button>
            <button id="deactivateBtn" class="action-btn deactivate-btn" onclick="deactivateUser()">Deactivate</button>
          </div>
        </div>
      </div>

      <!-- User Check Logs Overlay -->
      <div id="userChecklogsOverlay" class="checklogs-overlay">
        <div class="overlay-wrapper">
          <div class="overlay-content">
            <div class="logs-header">
              <div>User</div>
              <div>Action</div>
              <div>Date</div>
            </div>
            <div class="check">
              <div id="log-entry">
                <!-- Inject log entry -->
              </div>
            </div>
          </div>
          <button class="logs-close-btn" onclick="closeOverlay('userChecklogsOverlay')">Close</button>
        </div>
      </div>              

      <!-- Classroom Overlay -->
      <div id="classroomOverlay" class="user-overlay" overlay-type="classroom">
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
          
        <!-- Main Classroom List (default view) -->
        <div id="classroomListView" class="list-wrapper">
          <div class="dynamic-list">
            <?php
              $sql = "SELECT classroom.className, users.username, classroom.classroomID 
                      FROM classroom 
                      JOIN instructor ON classroom.instID = instructor.instID 
                      JOIN users ON instructor.userID = users.userID
                       ORDER BY classroom.className ASC";

              $result = $conn->query($sql);

              while ($row = $result->fetch_assoc()) {
                $className = htmlspecialchars($row['className']);
                $creatorName = htmlspecialchars($row['username']);
                $classroomID = htmlspecialchars($row['classroomID']);
            ?>
              <div class="classroom-item" classroom-id="<?php echo $classroomID;?>" data-classID="<?php echo $classroomID; ?>">
                <img src="images/Class_Icon.jpg" alt="Class Icon" class="classroom-icon">
                <div class="classroom-info">
                  <div class="classroom-title"><?php echo $className; ?></div>
                  <div class="classroom-creator"><?php echo $creatorName; ?></div>
                </div>
                <button class="search-icon-link" onclick="showClassDetails(this)">
                  <img src="images/Search_Icon.jpg" alt="View Classroom" class="search-image-icon">
                </button>
              </div>
            <?php } ?>
          </div>
        </div>
      </div>

      <!-- View Classroom Overlay -->
      <div id="viewClassroomDetailsOverlay" class="create-overlay">

        <div class="cd-content-wrapper">

          <!-- Main Content Grid -->
          <div class="cd-main-grid">

            <!-- Left Column -->
            <div class="cd-left-column">
              <div id="cd-header-section">

                <div class="cd-icon-wrapper">
                  <img src="images/Class_Icon.jpg" alt="Course Icon" class="cd-class-icon">
                </div>
                <div id="cd-title-wrapper"> 
                    <!-- Inject Here -->
                </div>
              </div>

              <div id="cdDesc" class="cd-card">
                
                <div id="cd-description-container">
                  <!-- Inject Here -->
                </div>

                <div id="cd-metadata">
                  <!-- Inject Here -->
                </div>

              </div>
              
            </div> <!-- End Left column-->

            <!-- Right Column -->
            <div class="cd-right-column">

              <!-- Instructors Section -->
              <div id="cdInstructorList" class="cd-card">
                <!-- Inject Here -->
                
              </div>

              <!-- Students Section -->
              <div id="cdStudentList"  class="cd-card" >
                <!-- Inject Here -->
              </div>

              <!-- Modules Section -->
              <div id="cdModuleList" class="cd-card">
                <!-- Inject Here -->
              </div>

            </div><!-- End Right column-->

          </div><!-- End Content-grid  -->

          <!-- Footer Actions -->
          <div class="cd-actions">
            <div class="cd-actions-right">
              <button class="cd-btn cd-btn-delete" onclick="deleteClass()">Delete</button>
              <button class="cd-btn cd-btn-close" onclick="closeOverlay('viewClassroomDetailsOverlay')">Close</button>
            </div>
          </div>

        </div><!-- End Content-wrapper  -->

      </div> <!-- End viewClassroomOverlay  -->

      <!-- Modules Overlay Main -->
      <div id="moduleOverlay" class="user-overlay" overlay-type="module">
        <button class="close-btn" onclick="hideOverlay('moduleOverlay')">×</button>
        <h2 style="color: #7b0000; margin-bottom: 20px;">Modules</h2>

        <div class="tabs">
          <button class="tab active" onclick="loadModules('All', this)">All</button>
          <button class="tab" onclick="loadModules('Partner', this)">Partner</button>
          <button class="tab" onclick="loadModules('Classroom', this)">Classroom</button>
          <div class="right-buttons">
            <div class="search-container">
              <button onclick="showSubOverlay('createModuleOverlay')" class="SearchButton">New Partner Module</button>
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

      <!-- Module Creation -->
      <div id="createModuleOverlay" class="create-module-overlay">
        <div id="createModuleMain">
          <button class="close-btn" onclick="closeOverlay('createModuleOverlay')">×</button>
          <h2 style="color: #7b0000; margin-bottom: 20px;">Upload a Module</h2>
          <div id="template">
            <h3 style="color: #7b0000; margin-bottom: 10px;">Template:</h3>
                <pre>
Module Name, Module Description{
  {Lesson 1 Name, Lesson 1 Description{
    {Word 1, Meaning},
    {Word 2, Meaning}, ...
  }},
  {Lesson 2 Name, Lesson 2 Description{
    {Word 1, Meaning},
    {Word 2, Meaning}, ...
  }}, ...	
}</pre>
          </div>
          <form action="adminFunctions.php" method="post" enctype="multipart/form-data">
            <div class="create-module-con">
              <select name="partnerIDField" id="partnerIDField" placeholder="partnerIDField" required>
                <option value="" disabled selected>Select a Partner</option>
                  <?php
                    
                    $results = $conn->query("SELECT * FROM partner");
                    while($rows = $results->fetch_assoc()){
                      $partnerID = $rows['partnerID'];
                      $partnerName = $rows['partnerName'];
                      echo "<option value='$partnerID'>$partnerName - $partnerID</option>";
                    }
                  ?>

              </select>
              <input id="files" type="file" name="files[]" multiple>
            </div>

            <div class = create-module-SC>
              <button type="submit" class="create-mod-btn" name="upload">Upload</button>
              <button type="button" class="create-mod-btn" onclick="closeOverlay('createModuleOverlay')">Cancel</button>
            </div>
          </form>
        </div>
      </div><!-- End Module Creation -->

      <!-- View Module Overlay -->
      <div id="viewModuleOverlay" class="view-module-overlay">
        <div id="viewModuleCon">
          <div id="viewModuleHeader">
            <button class="close-btn" onclick="closeOverlay('viewModuleOverlay')">×</button>
            <h2 style="color: #7b0000; margin-bottom: 20px;">View Module</h2>
          </div>
          <div id="viewModuleMain">
                  <!-- I Edit ni siya sa adtong scipt sa java script i love jollibee -->
          </div>
          <div id="viewModuleSC" class="view-module-SC">
            <button type="button" class="create-mod-btn" onclick="deleteModule(this)">Delete</button>
            <button type="button" class="create-mod-btn" onclick="closeOverlay('viewModuleOverlay')">Close</button>
          </div>
        </div>
      </div><!-- End View Module Overlay -->

      <!-- View Lesson Overlay -->
      <div id="viewLessonOverlay" class="view-lesson-overlay" >
        <div id="viewLessonCon">

          <div id="viewLessonHeader">
            <button class="close-btn" onclick="closeOverlay('viewLessonOverlay')">×</button>
            <h2 style="color: #7b0000; margin-bottom: 20px;">View Lesson</h2>
          </div>

          <div id="viewLessonMain">
            <div id="viewLessonInfo">
                  <!-- Inject SQL-->
            </div>

            <div id="viewLessonSC" class="view-lesson-SC">
                <button type="button" class="create-mod-btn" onclick="closeOverlay('viewModuleOverlay')">Close</button>
            </div>
          </div>

        </div>
      </div>
        
        <!-- Partners Overlay Main -->
      <div id="partnersOverlay" class="user-overlay" overlay-type="partners">
        <button class="close-btn" onclick="hideOverlay('partnersOverlay')">x</button>
        <h2 style="color: #7b0000; margin-bottom: 20px;">Partners</h2>
        
        <div class="tabs">
          <button class="NewP" onclick="showSubOverlay('createPartnersOverlay')">Add New</button>
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
              <div class="partners-card" partner-id="<?php echo $row['partnerID']; ?>">
                <img src="images/Partners_Icon.jpg" alt="Partners Icon" class="partners-icon">
                <div class="partners-info">
                  <div class="partners-title"><?php echo $partnerName?></div>
                  <div class="partners-role"><?php echo $partnerEmail?></div>
                </div>
                <button type="button" onclick="showViewPartner(this)" class="search-icon-link">
                  <img src="images/Search_Icon.jpg" alt="View Partners" class="search-image-icon">
                </button>
            </div>
            <?php } ?>
          </div>
        </div>
      </div>

      <!-- Partner Creation-->
      <div id="createPartnersOverlay" class="create-overlay">
          <button class="close-btn" onclick="closeOverlay('createPartnersOverlay')">×</button>
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
                <button class="creates" onclick="closeOverlay('createPartnersOverlay')">Cancel</button>
              </div>
            </form>
          </div>
      </div>

      <div id="viewPartnerOverlay" class="view-partner-overlay">
        <div id="viewPartnerCon">
          <div id="viewPartnerHeader">
            <button class="close-btn" onclick="closeOverlay('viewPartnerOverlay')">×</button>
            <h2 style="color: #7b0000; margin-bottom: 20px;">View Partner</h2>
          </div>
          <div id="viewPartnerMain">
            <div id="viewPartnerInfo">
                  
            </div>

            <div id="viewPartnerSC" class="view-partner-SC">
                <div id="editPartnerBtn"><button class="cd-edit-btn" onClick="editPartner()">Edit Details</button></div>
                <button class="cd-btn cd-btn-delete" onclick="deletePartner()">Delete</button>
                <button class="close-btn" onclick="closePartnerModal()">×</button>
            </div>
          </div>

      </div>
      </div>
    </div>  

  <script>
  // On Webpage Load
  window.addEventListener('DOMContentLoaded', () => {
    setUserTab('All');
  });

  // Logout Dropdown
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

  function showOverlay(targetId, backgroundIds = null) {
    console.log("Show Overlay: " + targetId); // Debug line to show which overlay is being opened
    const overlays = [
    'userOverlay',
    'userDetailsOverlay',
    'userChecklogsOverlay',

    'classroomOverlay',
    'viewClassroomDetailsOverlay',

    'moduleOverlay',
    'viewModuleOverlay',
    'viewLessonOverlay',
    'createModuleOverlay',

    'partnersOverlay',
    'createPartnersOverlay',
    'viewPartnerOverlay'
    ];
    const bg = document.getElementById('backgroundContent');

    overlays.forEach(id => {
      const overlay = document.getElementById(id); 

      const shouldShow = (id === targetId || (Array.isArray(backgroundIds) && backgroundIds.includes(id)) || (backgroundIds === id));
      overlay.style.display = shouldShow ? 'block' : 'none';
    });
    bg.style.display = 'none';
  }

  function showSubOverlay ($subOverlay){
    const subOverlay = document.getElementById($subOverlay);
    subOverlay.style.display = 'block';
  }

  function closeOverlay (targetID) {
    document.getElementById(targetID).style.display = "none";
  }

  function hideOverlay(targetId) {
    const target = document.getElementById(targetId);
    if (target) {
      target.style.display = 'none';
    }

    // Hide all overlays
    document.querySelectorAll('.user-overlay, .create-overlay').forEach(overlay => {
      overlay.style.display = 'none';
    });

    // If no overlays are visible, show the background
    const anyOpen = Array.from(document.querySelectorAll('.user-overlay, .create-overlay'))
      .some(el => el.style.display === 'block');

    if (!anyOpen) {
      document.getElementById('backgroundContent').style.display = 'flex';
    }
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
      "user": searchUser,
      "classroom": searchClassroom,
      "module": searchModule,
      "partners": searchPartners
    };

    const flexSearch = functionMap[overlayType];
    console.log("Overlay Type: " + overlayType); // Debug line to show which overlay type is being searched
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
  function searchUser(query) {
    const cards = document.querySelectorAll('.user-card');
    const searchQuery = query.toLowerCase();

    cards.forEach(card =>{
      // Values to check
      const username = card.dataset.name.toLowerCase(); 
      const email = card.dataset.email.toLowerCase(); 

      // check ni siya if ga match
      const matchesQuery = username.includes(searchQuery) || email.includes(searchQuery);
      
      // display if match
      card.style.display = matchesQuery ? 'flex' : 'none';
    });
  }
  
  function searchClassroom(query) {
    const cards = document.querySelectorAll('.classroom-item');
    const searchValue = query.toLowerCase();
    const searchID = query;

    cards.forEach(card => {
      const className = card.querySelector('.classroom-title').textContent.toLowerCase();
      const creatorName = card.querySelector('.classroom-creator').textContent.toLowerCase();

      const matchesSearch = className.includes(searchValue) || creatorName.includes(searchValue);
      card.style.display = matchesSearch ? 'flex' : 'none';
    });
  }

  function searchModule(query) {
    const cards = document.querySelectorAll('.module-card');
    const searchValue = query.toLowerCase();

    cards.forEach(card => {
      const moduleName = card.querySelector('.module-title').textContent.toLowerCase();
      const moduleCreator = card.querySelector('.module-creator').textContent.toLowerCase();
      const matchesSearch = moduleName.includes(searchValue) || moduleCreator.includes(searchValue);
      card.style.display = matchesSearch ? 'flex' : 'none';
    });
  }
  
  function searchPartners(query) {
    const cards = document.querySelectorAll('.partners-card');
    const searchValue = query.toLowerCase();

    cards.forEach(card => {
      const partnerName = card.querySelector('.partners-title').textContent.toLowerCase();
      const partnerEmail = card.querySelector('.partners-role').textContent.toLowerCase();
      const matchesSearch = partnerName.includes(searchValue) || partnerEmail.includes(searchValue);
      card.style.display = (matchesSearch) ? 'flex' : 'none';
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

  function notifyAndRedirect(message, redirectUrl) { // for redirection, if any in the future (only used for reload for now)
            const successDiv = document.createElement('div');
            successDiv.textContent = message;

            successDiv.style.position = 'fixed';
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

            const fadeOutAndRemove = () => {
              successDiv.style.opacity = '0'; // trigger fade-out
              setTimeout(() => successDiv.remove(), 1000); // remove after fade
            };

            if (redirectUrl === 'error' || redirectUrl === '' || redirectUrl == null){
                // Fade out and stay on the same page when error
                successDiv.style.backgroundColor = '#edd4d4';
                successDiv.style.color = '#571515';
                setTimeout(() => {
                fadeOutAndRemove();
            }, 3000);
            }
            else if (redirectUrl === 'reload' )
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
    fetch('fetchModules.php', {
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
  var selectedUser = '';
  let active = 1;
  function showUserDetails(element) {
    const userCard = element.closest('.user-card');
    showSubOverlay('userDetailsOverlay');

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

    selectedUser = uid;
    active = parseInt(userCard.dataset.active);
    console.log(active);
    if (active === 0) document.getElementById('deactivateBtn').outerHTML = `<button id="deactivateBtn" class="action-btn deactivate-btn" onclick="activateUser()">Activate</button>`;
    else document.getElementById('deactivateBtn').outerHTML = `<button id="deactivateBtn" class="action-btn deactivate-btn" onclick="deactivateUser()">Deactivate</button>`;

    console.log("Selected User ID: " + selectedUser); // Debug line to show selected user ID
  }

  // -- User Functions -- 
  
    // Delete User

    function deleteUser() {
        const confirmed = confirm("Are you sure you want to delete this user?");
        if (confirmed) {
            // Add user deletion process here
            fetch('adminFunctions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
                ,
                body: JSON.stringify({
                    action: 'deleteUser',
                    userID: selectedUser
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
                    notifyAndRedirect('User deleted succesfully!', 'reload');
                } else {
                    notifyAndRedirect('User deletion Failed. An error has occurred.', 'error');
                }
            })
            .catch(error => {
              console.error('Error:', error);
              alert('An error occurred: ' + error.message);
            });

        }
        else return;
    }

    // Deactivate User
    // <button class="action-btn deactivate-btn" onclick="deactivateUser()">Deactivate</button>
    function deactivateUser() {
        const confirmed = confirm("Are you sure you want to deactivate this user?");
        if (confirmed) {
            // Add user deactivation process here
            fetch('adminFunctions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
                ,
                body: JSON.stringify({
                    action: 'deactivateUser',
                    userID: selectedUser
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
                    notifyAndRedirect('User deactivated succesfully!', 'reload');
                } else {
                    notifyAndRedirect('User deactivation Failed. An error has occurred.', 'error');
                }
            })
            .catch(error => {
              console.error('Error:', error);
              alert('An error occurred: ' + error.message);
            });

        }
        else return;
    }
    
    function activateUser() {
        const confirmed = confirm("Are you sure you want to activate this user?");
        if (confirmed) {
            // Add user reactivation process here
            fetch('adminFunctions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
                ,
                body: JSON.stringify({
                    action: 'activateUser',
                    userID: selectedUser
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
                    notifyAndRedirect('User activated succesfully!', 'reload');
                } else {
                    notifyAndRedirect('User activation failed. An error has occured.', 'error');
                }
            })
            .catch(error => {
              console.error('Error:', error);
              alert('An error occurred: ' + error.message);
            });

        }
        else return;
    }

    // checkuserlogs
    function checkUserLogs() {
    console.log("Fetch Activity Logs");
    showSubOverlay('userChecklogsOverlay');

    fetch('adminFunctions.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'getActivityLogs',
        data: { selectedUser: selectedUser }
      })
    })
    .then(response => {
      console.log("Raw response:", response);
      return response.json();
    })
    .then(data => {
      console.log("Parsed data:", data);
      const { logs } = data;
      console.log("Logs: ", logs);

      const htmlContent = logs.map(log => `
        <div class="user-col">${log.username + " - " + log.userID}</div>
        <div class="action-col">${log.action}</div>
        <div class="date-col">${log.dateTimeCreated}</div>
      `).join('');

      document.getElementById('log-entry').innerHTML = htmlContent;
    })
    .catch(error => {
      console.error("Fetch error:", error);
      document.getElementById('log-entry').innerHTML = `
        <div class="error">An error occurred while fetching lesson details.</div>
      `;
    });
  }

    var selectedClassroomID = '';
    function showClassDetails(element) {
    console.log("Show Classroom Details");
    showSubOverlay('viewClassroomDetailsOverlay');

    const classCard = element.closest('.classroom-item');
    const classID = classCard.getAttribute('classroom-id');
    selectedClassroomID = classID;
    if (!classID) {
      console.error("Error: Classroom ID not found.");
      return;
    }

    // Fetch classroom details
    console.log('Sending Fetch Request to instructorFunctions.php');
    fetch('adminFunctions.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'getClassroomDetails', // Correct action
        data: { classID: classID }
      })
    })
      .then(response => {
        if (!response.ok) {
          throw new Error('Network response was not ok');
        }
        return response.json();
      })
      .then(data => {
        if (!data.success) {
          throw new Error(data.message || 'Failed to load classroom data.');
        }

        const { classroomDetails, instructors, students, modules } = data;
        console.log("Classroom Details:", classroomDetails);
        console.log("Instructors:", instructors);
        console.log("Students:", students);
        console.log("Modules:", modules);

        // Generate HTML content
        const headerContent = `
          <div id="cdClassName">${classroomDetails.className}</div>
          <div id="cdCreator">${classroomDetails.username}</div>
        `;
        const descriptionContent = `
          <h2 class="cd-section-title">Description:</h2>
          <div id="cd-description-text">${classroomDetails.classDesc}</div>
          <div id="editBtn"><button class="cd-edit-btn" onClick="editClass()">Edit Details</button></div>
          <div id="saveBtn" style="display:none;"><button class="cd-save-btn" onClick="saveEdit()">Save Changes</button></div>
        `;
        const metadataContent = `
          <div class="cd-metadata-item">
            <span class="cd-label">Created On:</span>
            <span id="cd-created-date">${classroomDetails.dateCreated}</span>
          </div>
          <div class="cd-metadata-item">
            <span class="cd-label">Code:</span>
            <span id="cd-access-code">${classroomDetails.classCode}</span>
          </div>
        `;
        const instructorContent = instructors.length > 0 ? `
          <div class="cd-section-title">Instructors</div>
          <div class="cd-instructorlist">
            ${instructors.map(instructor => `
              <div class="cd-student-card">
                <img src="images/Human_Icon.jpg" alt="Instructor" class="cd-list-icon">
                <div class="cd-student-info">
                  <h3>${instructor.username}</h3>
                  <p>${instructor.userID}</p>
                </div>
              </div>
            `).join('')}
          </div>
        ` : `<p>No instructors available.</p>`;

        const studentContent = students.length > 0 ? `
          <div class="cd-section-title">Students</div>
          <div class="cd-studentlist">
            ${students.map(student => `
              <div class="cd-student-card">
                <img src="images/Human_Icon.jpg" alt="Student" class="cd-list-icon">
                <div class="cd-student-info" onClick="showUserDetails(this)">
                  <h3>${student.username}</h3>
                  <p>${student.userID}</p>
                </div>
              </div>
            `).join('')}
          </div>
        ` : `<p>No students available.</p>`;

        const moduleContent = modules.length > 0 ? `
          <div class="cd-section-title">Modules</div>
          <div class="cd-modulelist">
            ${modules.map(module => `
              <div class="module-card" module-id="${module.langID}" onclick="showViewModule(this, ['classroomOverlay', 'viewClassroomDetailsOverlay'])">
                <div class="cd-module-card">
                  <img src="images/Module_Icon.jpg" alt="Module Icon" class="cd-list-icon">
                  <div class="cd-module-info">
                    <h3>${module.moduleName}</h3>
                    <p>${module.username}</p>
                  </div>
                </div>
              </div>
            `).join('')}
          </div>
        ` : `<p>No modules available.</p>`;

        document.getElementById('cd-title-wrapper').innerHTML = headerContent;
        document.getElementById('cd-description-container').innerHTML = descriptionContent;
        document.getElementById('cd-metadata').innerHTML = metadataContent;
        document.getElementById('cdInstructorList').innerHTML = instructorContent;
        document.getElementById('cdStudentList').innerHTML = studentContent;
        document.getElementById('cdModuleList').innerHTML = moduleContent;
      })

      .catch(error => {
        console.error("Fetch error:", error);
        document.getElementById('cd-title-wrapper').innerHTML = `<div class="error">Failed to load classroom details. Please try again later.</div>`;
      });
  }

  // Show View Module
  var selectedModuleID = "";
  var selectedModuleType = "";

  function showViewModule(element, $current) {
  console.log("View Module");
  showSubOverlay('viewModuleOverlay');

  const moduleCard = element.closest('.module-card');
  const moduleID = moduleCard.getAttribute('module-id');

  selectedModuleID = moduleID;
  console.log("Module ID: " + moduleID);

  if (!moduleID) {
    console.error("Error: Module ID not found.");
    return;
  }

  console.log('Sending Fetch Request to instructor.php');
  fetch('adminFunctions.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      action: 'getModuleDetails',
      data: { moduleID: moduleID }
    })
  })
  .then(response => {
    if (!response.ok) {
      throw new Error('Network response was not ok');
    }
    return response.json(); 
  })
  .then(data => {

    const { moduleName, moduleDesc, className, lessons: lessonArray, moduleType } = data;

    selectedModuleType = moduleType;

    const htmlContent = `
      <div id="viewModuleInfo">
        <div id="viewModuleTitle">
          <img src="images/Module_Icon.jpg" alt="Module Icon" class="view-module-icon">
          <div id="viewModuleInfoText">
            <div id="viewModuleTitle">${moduleName}</div>
            <div id="viewModuleClass">${className}</div>
          </div>
        </div>

        <div id="viewModuleDesc">
          <div id="viewModuleDescText">${moduleDesc}</div>
        </div>

        <div id="lessonList">
          <div id="lessonListTitle">Lessons</div>
          <div class="list-wrapper">
            <div class="dynamic-list">
              ${lessonArray.map(lesson => `
                <div class="module-card" lesson-id="${lesson.lessID}">
                  <img src="images/Module_Icon.jpg" alt="Module Icon" class="module-icon">
                  <div class="module-info">
                    <div class="module-title">${lesson.lessonName}</div>
                    <div class="module-creator">In ${moduleName}</div>
                  </div>
                  <button class="view-lesson" onclick="showViewLesson(this, ['viewModuleOverlay','moduleOverlay'])">
                    <img src="images/Search_Icon.jpg" alt="View Lesson" class="search-image-icon">
                  </button>
                </div>
              `).join('')}
            </div>
          </div>
        </div>
      </div>
    `;

    document.getElementById('viewModuleMain').innerHTML = htmlContent;
  })
  .catch(error => {
    console.error("Fetch error:", error);
    document.getElementById('viewModuleMain').innerHTML = `<div class="error">Failed to load module details.</div>`;
  });
  }

  function deleteModule(element) {
    console.log("Delete Module");
    const moduleCard = element.closest('.module-card');
    const moduleID = selectedModuleID;
    const moduleType = selectedModuleType;

    if (!moduleID) {
      console.error("Error: Module ID not found.");
      return;
    }

    fetch('adminFunctions.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'deleteModule',
        data: { 
          moduleID: moduleID, 
          moduleType: moduleType
        }
      })
  })
  .then(response => {
    if (!response.ok) {
      throw new Error('Network response was not ok');
    }
    return response.json();
  })
  .then(result => {
    if (result.success) {
      notifyAndRedirect('Module has been deleted succesfully!', 'reload');
    } else {
      notifyAndRedirect('Failed to delete module. An error has occured.', 'error');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('An error occurred: ' + error.message);
  });
  }

  // Show View Lesson
  function showViewLesson(element) {
  console.log("View Lesson");
  showSubOverlay('viewLessonOverlay');

  // Get the lesson ID from the clicked element
  const lessonCard = element.closest('.module-card');
  const lessonID = lessonCard.getAttribute('lesson-id');

  console.log("Lesson ID: " + lessonID);

  if (!lessonID) {
  console.error("Error: Lesson ID not found.");
  return;
  }

  // Fetch lesson details from the server
  fetch('adminFunctions.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    action: 'getLessonDetails',
    data: { lessonID: lessonID }
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
      const lesson = result.data;

      // Format the lesson details as HTML
      const htmlContent = `
        <div id="viewLessonTitle">
              <img src="images/Module_Icon.jpg" alt="">
              <div id="viewLessonName">${lesson.lessonName}</div>
            </div>

            <div id="viewLessonDesc">
              <div id="viewLessonDescText">${lesson.lessonDesc}</div>
            </div>

            <div id="viewLessonWords">
              <div id="viewLessonWordsTitle">Vocabulary</div>
              <div id="viewLessonWordsList">
                <div class="list-wrapper">
                  <table class="dynamic-table">
                    <thead>
                      <tr>
                        <th>Word</th>
                        <th>Meaning</th>
                      </tr>
                    </thead>
                    <tbody>
                      ${lesson.vocabulary.map(word => `
                        <tr>
                          <td>${word.word}</td>
                          <td>${word.meaning}</td>
                        </tr>
                      `).join('')}
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
      `;

      // Inject the HTML into the overlay
      document.getElementById('viewLessonMain').innerHTML = htmlContent;
    } else {
      document.getElementById('viewLessonMain').innerHTML = `
        <div class="error">Failed to load lesson details: ${result.message}</div>
      `;
    }
  })
  .catch(error => {
    console.error("Fetch error:", error);
    document.getElementById('viewLessonMain').innerHTML = `
      <div class="error">An error occurred while fetching lesson details.</div>
    `;
  });
  }

  // -- Partner Functions --
  var selectedPartner = '';
  function showViewPartner(element){
    showOverlay('viewPartnerOverlay', ['partnersOverlay']);

    // Get the lesson ID from the clicked element
    const partnerCard = element.closest('.partners-card');
    const partnerID = partnerCard.getAttribute('partner-id');

    selectedPartner = partnerID;

    console.log("Partner ID: " + partnerID);
    fetch('adminFunctions.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'getPartnerDetails',
        data: { partnerID: partnerID }
      })
    })
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      console.log("Response: ", response);
      return response.json(); // Parse JSON response
    })
    .then(data =>{
      const {partnerDetails} = data;
      console.log("Partner Details: ", partnerDetails);
      const htmlContent = `
        <div id="viewPartnerTitle">
          <img src="images/Partners_Icon.jpg" alt="">
          <div id="viewPartnerName">${partnerDetails.partnerName}</div>
        </div>

        <div id="viewPartnerDesc">
          <div id="viewPartnerDescText">${partnerDetails.partnerDesc}</div>
        </div>
        <div id="viewPartnerContactInfo">
          <div id="viewPartnerContact">
            <div id="viewPartnerContactTitle">Contact</div>
            <div id="viewPartnerContactText">${partnerDetails.contact}</div>
          </div>

          <div id="viewPartnerEmail">
            <div id="viewPartnerEmailTitle">Email</div>
            <div id="viewPartnerEmailText">${partnerDetails.email}</div>
          </div>
        </div>
      `;
      document.getElementById('viewPartnerInfo').innerHTML = htmlContent;
    })

  }

  let originalPartnerName = '';
  let originalPartnerDesc = '';
  let originalPartnerContact = '';
  let originalPartnerEmail = '';

  function editPartner() {
      const nameEl = document.getElementById('viewPartnerName');
      const descEl = document.getElementById('viewPartnerDescText');
      const contactEl = document.getElementById('viewPartnerContactText');
      const emailEl = document.getElementById('viewPartnerEmailText');
      const editBtn = document.getElementById('editBtn');
      const deleteBtn = document.getElementById('deleteBtn');
      const leaveBtn = document.getElementById('leaveBtn');

      // Store original values
      originalPartnerName = nameEl.innerText;
      originalPartnerDesc = descEl.innerText;
      originalPartnerContact = contactEl.innerText;
      originalPartnerEmail = emailEl.innerText;


      // Replace with editable fields
      nameEl.outerHTML = `<input id="editPartnerName" type="text" value="${originalPartnerName}" class="editable-input">`;
      descEl.outerHTML = `<textarea id="editPartnerDesc" class="editable-textarea">${originalPartnerDesc}</textarea>`;
      contactEl.outerHTML = `<input id="editPartnerContact" type="text" value="${originalPartnerContact}" class="editable-input">`;
      emailEl.outerHTML = `<input id="editPartnerEmail" type="text" value="${originalPartnerEmail}" class="editable-input">`;

      // Reveal Save and swap Edit to Cancel
      document.getElementById('editPartnerBtn').outerHTML = `
        <div id="editPartnerBtn" style="display: flex; gap: 10px;">
        <button class="cd-edit-btn" onClick="savePartnerEdit()">Save</button>
        <button class="cd-edit-btn" onClick="cancelPartnerEdit()">Cancel</button>
        </div>
      `;

        
  }

  function cancelPartnerEdit() {
      // Revert back to original content
      document.getElementById('editPartnerName').outerHTML = `<div id="viewPartnerName">${originalPartnerName}</div>`;
      document.getElementById('editPartnerDesc').outerHTML = `<div id="viewPartnerDescText">${originalPartnerDesc}</div>`;
      document.getElementById('editPartnerContact').outerHTML = `<div id="viewPartnerContactText">${originalPartnerContact}</div>`;
      document.getElementById('editPartnerEmail').outerHTML = `<div id="viewPartnerEmailText">${originalPartnerEmail}</div>`;

      document.getElementById('editPartnerBtn').outerHTML = `<div id="editPartnerBtn"><button class="cd-edit-btn" onClick="editPartner()">Edit Details</button></div>`;

  }

  function savePartnerEdit() {
      const partnerName = document.getElementById('editPartnerName').value;
      const partnerDesc = document.getElementById('editPartnerDesc').value;
      const partnerContact = document.getElementById('editPartnerContact').value;
      const partnerEmail = document.getElementById('editPartnerEmail').value;

      if (partnerName.trim() === '' || partnerDesc.trim() === '' || partnerContact.trim() === '' || partnerEmail.trim() === '') {
          alert('Please fill in all the boxes.');
          return;
      }

      fetch('adminFunctions.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' }
          ,
          body: JSON.stringify({
              action: 'updatePartner',
              partnerName: partnerName,
              partnerDesc: partnerDesc,
              partnerContact: partnerContact,
              partnerEmail: partnerEmail,
              partnerID: selectedPartner
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
              notifyAndRedirect('Partner updated sucessfully!', 'reload');
          } else {
              notifyAndRedirect('Partner update failed. An error has occured', 'error');
          }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred: ' + error.message);
      });
  }

  // Deletion
  function deletePartner() {
      const confirmed = confirm("Are you sure you want to delete this partner?");
      if (confirmed) {
          // Add classroom deletion process here
          fetch('adminFunctions.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' }
              ,
              body: JSON.stringify({
                  action: 'deletePartner',
                  partnerID: selectedPartner
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
                  notifyAndRedirect('Partner deleted succesfully!', 'reload');
              } else {
                  notifyAndRedirect('Partner deletion failed. An error has occured.', 'error');
              }
          })
          .catch(error => {
            console.error('Error:', error);
            alert('An error occurred: ' + error.message);
          });

      }
      else return;
  }

  // -- Class Functions --

  // Editing
  let originalTitle = '';
  let originalDesc = '';

  function editClass() {
      const titleEl = document.getElementById('cdClassName');
      const descEl = document.getElementById('cd-description-text');
      const editBtn = document.getElementById('editBtn');
      const deleteBtn = document.getElementById('deleteBtn');
      const leaveBtn = document.getElementById('leaveBtn');

      // Store original values
      originalTitle = titleEl.innerText;
      originalDesc = descEl.innerText;

      // Replace with editable fields
      titleEl.outerHTML = `<input id="editTitle" type="text" value="${originalTitle}" class="editable-input">`;
      descEl.outerHTML = `<textarea id="editDesc" class="editable-textarea">${originalDesc}</textarea>`;

      // Reveal Save and swap Edit to Cancel
      document.getElementById('editBtn').outerHTML = `
        <div id="editBtn" style="display: flex; gap: 10px;">
        <button class="cd-edit-btn" onClick="saveEdit()">Save</button>
        <button class="cd-edit-btn" onClick="cancelEdit()">Cancel</button>
        </div>
      `;
  }

    function closePartnerModal() {
      // Cancel edit mode if it's active
      const editInput = document.getElementById('editPartnerName');
      if (editInput) {
          cancelPartnerEdit();
      }

      // Then hide the modal
      document.getElementById('viewPartnerOverlay').style.display = 'none';
  }

  function cancelEdit() {
      // Revert back to original content
      document.getElementById('editTitle').outerHTML = `<div id="cdClassName">${originalTitle}</div>`;
      document.getElementById('editDesc').outerHTML = `<div id="cd-description-text">${originalDesc}</div>`;

      document.getElementById('editBtn').outerHTML = `<div id="editBtn"><button class="cd-edit-btn" onClick="editClass()">Edit Details</button></div>`;
      document.getElementById('saveBtn').style.display = 'none';

  }

  function saveEdit() {
      const classTitle = document.getElementById('editTitle').value;
      const classDesc = document.getElementById('editDesc').value;

      if (classTitle.trim() === '' || classDesc.trim() === '') {
          alert('Title and description cannot be empty.');
          return;
      }

      fetch('adminFunctions.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' }
          ,
          body: JSON.stringify({
              action: 'updateClass',
              className: classTitle,
              classDesc: classDesc,
              classID: selectedClassroomID
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
              notifyAndRedirect('Changes updated sucessfully!', 'reload');
          } else {
              notifyAndRedirect('Updated changes failed. An error has occured.', 'error');
          }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred: ' + error.message);
      });
  }

  // Deletion
  function deleteClass() {
      const confirmed = confirm("Are you sure you want to delete this classroom?");
      if (confirmed) {
          // Add classroom deletion process here
          fetch('adminFunctions.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' }
              ,
              body: JSON.stringify({
                  action: 'deleteClass',
                  classID: selectedClassroomID
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
                  notifyAndRedirect('Classroom deleted succesfully!', 'reload');
              } else {
                  notifyAndRedirect('Classroom deletion failed. An error has occured.', 'error');
              }
          })
          .catch(error => {
            console.error('Error:', error);
            alert('An error occurred: ' + error.message);
          });

      }
      else return;
  }

// Load default on page load
window.onload = () => loadModules('All', document.querySelector('.tab.active'));

</script>
</body>
</html>