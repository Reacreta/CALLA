<?php
  require_once 'database.php';
  require_once 'authFunctions.php';

  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);

  ob_start();
  session_start();

  sessionCheck('Instructor');

  // Insert into Classroom
  if(isset($_POST['createClassroom'])){
    

    $creatorID = $_SESSION['roleID'];

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

    logAction($conn, $_SESSION['userID'], "Created Classroom: ".$classID);
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

    #tabHeader{
      display: flex;
      font-size: 20px;
      font-weight: bold;
      color: #7b0000;
      align-items: center;
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

    /* CREATE CLASS */

    .create-overlay { 
      display: none;
      position: fixed;
      float: left;
      left: 50%;
      top: 50%;
      transform: translate(-50%, -50%);

      background: rgba(241, 241, 241, 0.85);
      backdrop-filter: blur(5px);
      border: 2px solid white;
      border-radius: 6px 6px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
      
      height: fit-content;
      width: fit-content;
      

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


 /* Classroom Details Overlay */
  #viewClassroomDetailsOverlay {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 60%;
    height: 90%;
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
    border-radius: 6px;
    background: #f8f8f8;
    margin-bottom: 10px;
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
    background: #8b0000;
    color: white;
  }

  .cd-btn-leave {
    background: #8b0000;
    color: white;
  }

  .cd-btn-partner {
    background-color: #28a745; /* Green */
    color: white;
  }

  .cd-btn-partner:hover {
    background-color: #218838;
  }

  /* Create Module Overlay */
  .create-module-overlay{
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
  
  .create-mod-btn{
    background: #e6e6e6;
    border: none;
    color: #7b0000;
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

    /* View Module */
    #viewModuleOverlay{
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
      z-index: 15;
      padding: 20px;
      overflow-y: auto;
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

    /* View Lessons */

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
      max-height: 300px;     /* You can adjust this height */
      overflow-y: auto;
      border: 1px solid #ccc;
      padding: 10px;
      border-radius: 6px;
      background-color: #f9f9f9;
      scroll-behavior: smooth;scrollbar-width: thin;
      scrollbar-color: #a00 #f0f0f0;
    }

    #viewLessonWordsTitle {
      font-size: 1.2rem;
      font-weight: bold;
      margin-bottom: 10px;
      color: #444;
    }

    .list-wrapper {
      overflow-x: auto; 
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

    .show, #viewModuleOverlay.show, #viewLessonOverlay.show, #createOverlay.show, #joinOverlay.show, #viewClassroomDetailsOverlay.show, #create-module-overlay.show {
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
      }

      #cdDesc{
        height: fit-content;
      }
    }
    
  </style>
</head>

<body>
  <div class="header">
    <div class="title"><img id="logo" src="images/logo.png"><div id="role"><span>INSTRUCTOR</span></div></div>
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
        <button class="nav-btn" onclick="showOverlay('classroomOverlay')"><img src="images/Class_Icon.jpg" class="User-icon" alt="Classroom Icon"><div>Classrooms</div></button>
        <button class="nav-btn" onclick="showOverlay('moduleOverlay')"><img src="images/Module_Icon.jpg" class="User-icon" alt="Module Icon"><div>Modules</div></button>
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
            <button class="SearchButton" onclick="showSubOverlay('createOverlay')">Create Classroom</button>
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
                  <div class="classroom-card" class-type = "owned"
                  classroom-id = "<?php echo $classroomID?>">
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
        <button class="close-btn" onclick="hideSubOverlay('createOverlay')">×</button>
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
            <button class = "creates" onclick="hideSubOverlay('createOverlay','classroomOverlay')">Cancel</button>
          </div>
          </form>

        </div>

      </div> <!-- End Classroom Creation-->


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
            <div id="cd-actions" class="cd-actions-right">
              <button class="cd-btn cd-btn-delete" onclick="deleteClass()">Delete</button>
              <button class="cd-btn cd-btn-close" onclick="hideSubOverlay('viewClassroomDetailsOverlay','classroomOverlay')">Close</button>
            </div>
          </div>

        </div><!-- End Content-wrapper  -->

      </div> <!-- End viewClassroomOverlay  -->

      <!-- Join Classroom -->
      <div id="joinOverlay" class="join-overlay">
      <button class="close-btn" onclick="hideSubOverlay('joinOverlay','classroomOverlay')">×</button>
        <div class = join-con>

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
            <input type="text" id="classCode" name="classCode"placeholder="Code" required>
          </div>

          <div class = join-SC>
            <button type="button" onclick= "joinClassroom();">Join</button>
            <button type="button" onclick="hideSubOverlay('joinOverlay','classroomOverlay')">Cancel</button>
          </div>

        </div>
      </div> <!-- End Join Classroom Overlay -->

      <!-- Modules Overlay -->
      <div id="moduleOverlay" class="module-overlay" overlay-type ="module">
        <button class="close-btn" onclick="hideOverlay('moduleOverlay')">×</button>
        <h2 style="color: #7b0000; margin-bottom: 20px;">Modules</h2>

        <div class="tabs">
          <div id="tabHeader">Owned</div>
          <div class="right-buttons">
            <button onclick="showOverlay('createModuleOverlay','moduleOverlay')" class="SearchButton">New Module</button>
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
                    c.className
                FROM classmodule cm 
                JOIN classinstructor ci ON cm.classInstID = ci.classInstID
                JOIN instructor i ON i.instID = ci.instID
                JOIN users u ON u.userID = i.userID
                JOIN classroom c ON ci.classroomID = c.classroomID
                JOIN languagemodule lm ON lm.langID = cm.langID
                WHERE i.instID = ?;
                ";

                $stmt = $conn->prepare($sql); 
                $stmt->bind_param('s', $_SESSION['roleID']);
                $stmt->execute();
                 
                debug_console("InstructorID: ".$_SESSION['roleID']);

                $result = $stmt->get_result();
                while($row = $result->fetch_assoc()){
              ?>
                  <div class="module-card" 
                  module-id = "<?php echo htmlspecialchars($row['langID'])?>">
                    <img src="images/Module_Icon.jpg" alt="Module Icon" class="module-icon">
                    <div class="module-info">
                    <div class="module-title"><?= htmlspecialchars($row['moduleName']) ?></div>
                    <div class="module-creator">In <?= htmlspecialchars($row['className']) ?></div>
                    </div>
                    <Button id="viewModule" onclick="showViewModule(this)">
                      <img src="images/Search_Icon.jpg" alt="View Module" class="search-image-icon">
                    </Button>
                </div>    
              <?php
                }
              ?>
          </div>
        </div>

      </div><!-- End Module Overlay-->

      <!-- Module Creation -->
      <div id="createModuleOverlay" class="create-module-overlay">
        <div id="createModuleMain">
          <button class="close-btn" onclick="hideSubOverlay('createModuleOverlay','moduleOverlay')">×</button>
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
          <form action="instructorFunctions.php" method="post" enctype="multipart/form-data">
            <div class="create-module-con">
              <select name="classIDField" id="classroomIDField" placeholder="ClassroomID">
                <option value="" disabled selected>Select a Classroom</option>
                  <?php
                    $sql = "SELECT ci.classroomID, c.className FROM classinstructor ci 
                            JOIN classroom c ON ci.classroomID = c.classroomID 
                            WHERE ci.instID = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('s', $_SESSION['roleID']);
                    $stmt->execute();
                    $results = $stmt->get_result();
                    while($rows = $results->fetch_assoc()){
                      $classroomID = $rows['classroomID'];
                      $classroomName = $rows['className'];
                      echo "<option value='$classroomID'>$classroomName</option>";
                    }
                  ?>

              </select>
              <input id="files" type="file" name="files[]" multiple>
            </div>

            <div class = create-module-SC>
              <button type="submit" class="create-mod-btn" name="upload">Upload</button>
              <button type="button" class="create-mod-btn" onclick="hideSubOverlay('createModuleOverlay','moduleOverlay')">Cancel</button>
            </div>
          </form>
        </div>
      </div><!-- End Module Creation -->


      <!-- View Module Overlay -->
      <div id="viewModuleOverlay" class="view-module-overlay">
        <div id="viewModuleCon">
          <div id="viewModuleHeader">
            <button class="close-btn" onclick="hideSubOverlay('viewModuleOverlay','moduleOverlay')">×</button>
            <h2 style="color: #7b0000; margin-bottom: 20px;">View Module</h2>
          </div>
          <div id="viewModuleMain">
                  <!-- I Edit ni siya sa adtong scipt sa java script i love jollibee -->
          </div>
          <div id="viewModuleSC" class="view-module-SC">
            <button type="button" class="create-mod-btn" onclick="deleteModule(this)">Delete</button>
            <button type="button" class="create-mod-btn" onclick="hideSubOverlay('viewModuleOverlay','moduleOverlay')">Close</button>
          </div>
        </div>
      </div><!-- End View Module Overlay -->

      <!-- View Lesson Overlay -->
      <div id="viewLessonOverlay" class="view-lesson-overlay">
        <div id="viewLessonCon">

          <div id="viewLessonHeader">
            <button class="close-btn" onclick="hideSubOverlay('viewLessonOverlay', 'viewModuleOverlay')">×</button>
            <h2 style="color: #7b0000; margin-bottom: 20px;">View Lesson</h2>
          </div>

          <div id="viewLessonMain">
            <div id="viewLessonInfo">
                  <!-- Inject SQL-->
            </div>

            <div id="viewLessonSC" class="view-lesson-SC">
                <button type="button" class="create-mod-btn" onclick="hideSubOverlay('viewModuleOverlay','moduleOverlay')">Close</button>
            </div>
          </div>

        </div>
      </div>

    </div><!-- End Main Content-->
  </div><!-- End dashboard-container-->



<script>
  document.addEventListener('click', function (e) {
    const profileContainer = document.querySelector('.profile-container');
    const dropdown = document.getElementById('logoutDropdown');
    if (!profileContainer.contains(e.target)) {
      dropdown.style.display = 'none';
    }
  });

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

    // Funcs
  function searchClassroom(query) {
    const activeTabElement = document.querySelector('#classroomOverlay .tab.active');
    const cards = document.querySelectorAll('.classroom-card');
    const searchValue = query.toLowerCase();

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

  function showOverlay(targetId, backgroundIds = null) {
    const overlays = [
    'classroomOverlay', 'moduleOverlay', 'createOverlay', 'joinOverlay', 'createModuleOverlay',
    'viewModuleOverlay', 'viewLessonOverlay', 'viewClassroomDetailsOverlay'];
    const bg = document.getElementById('backgroundContent');

    overlays.forEach(id => {
      const overlay = document.getElementById(id); // gets element with corresponding name from overlay array

      //console.log("Overlay ID: " + id); // Debug line para maipakita kung unsa na overlay ang ginaspecify

      const shouldShow = (id === targetId || (Array.isArray(backgroundIds) && backgroundIds.includes(id)) || (backgroundIds === id));

      //console.log("Should Show: " + shouldShow); // Debug line to demo ang boolean for showing per overlay 
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

  function showSubOverlay ($subOverlay){
    const subOverlay = document.getElementById($subOverlay);
    subOverlay.classList.add('show');
  }

  function hideSubOverlay(targetId,parent) {

    const target = document.getElementById(targetId);

    target.classList.remove('show');

    const anyOpen = document.querySelectorAll('.show').length > 0;

    if (!anyOpen) {
      document.getElementById(parent).classList.add('show');
    }

  }

  // Show Classroom Details
  var selectedClassroomID = '';
  var checkOwner = '';
  function showClassDetails(element) {
    console.log("Show Classroom Details");
    showSubOverlay('viewClassroomDetailsOverlay');

    const classCard = element.closest('.classroom-card');
    const classID = classCard.getAttribute('classroom-id');
    selectedClassroomID = classID;
    if (!classID) {
      console.error("Error: Classroom ID not found.");
      return;
    }

    // Fetch classroom details
    console.log('Sending Fetch Request to instructorFunctions.php');
    fetch('instructorFunctions.php', {
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

        checkOwner = data.checkOwner;
        console.log("Ownership:", checkOwner);

        // Change Buttons according to ownership

        if (checkOwner == 'true')
          document.getElementById('cd-actions').outerHTML = `
            <div id="cd-actions" class="cd-actions-right">
              <button class="cd-btn cd-btn-leave" onclick="leaveClass()">Leave</button>
              <button class="cd-btn cd-btn-close" onclick="hideSubOverlay('viewClassroomDetailsOverlay','classroomOverlay')">Close</button>
            </div>`;
        else
          document.getElementById('cd-actions').outerHTML = `
            <div id="cd-actions" class="cd-actions-right">
              <button class="cd-btn cd-btn-leave" onclick="leaveClass()">Leave</button>
              <button class="cd-btn cd-btn-close" onclick="hideSubOverlay('viewClassroomDetailsOverlay','classroomOverlay')">Close</button>
            </div>`; 

        // <button class="cd-btn cd-btn-partner" onclick="addPartnerModule()">Add Partner Module</button> - Still Not Implemented
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
          <h2 class="cd-section-title">Instructors</h2>
          <div id="cd-instructor-list">
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
          <h2 class="cd-section-title">Students</h2>
          <div id="cd-students-list">
            ${students.map(student => `
              <div class="cd-student-card">
                <img src="images/Human_Icon.jpg" alt="Student" class="cd-list-icon">
                <div class="cd-student-info">
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
              <div class="module-card" module-id="${module.langID}" onclick="showViewModule(this)">
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

  // -- Class Functions --
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
      document.getElementById('editBtn').outerHTML = `<div id="editBtn"><button class="cd-edit-btn" onClick="cancelEdit()" style="display:flex;">Cancel</button></div>`;
      document.getElementById('saveBtn').style.display = 'block';
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

      fetch('instructorFunctions.php', {
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
          fetch('instructorFunctions.php', {
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
                  notifyAndRedirect('Classroom deletion failed. An error has occured', 'error');
              }
          })
          .catch(error => {
            console.error('Error:', error);
            alert('An error occurred: ' + error.message);
          });

      }
      else return;
  }

  // Leave Class
  function leaveClass() {
      let message = checkOwner
      ? "Are you sure you want to leave this classroom? As the owner, this will delete the classroom and remove it for everyone."
      : "Are you sure you want to leave this classroom?";

      const confirmed = confirm(message);

      if (confirmed) {
          // Add classroom deletion process here
          fetch('instructorFunctions.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' }
              ,
              body: JSON.stringify({
                  action: 'leaveClass',
                  classID: selectedClassroomID,
                  checkOwner: checkOwner
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
                    if (checkOwner == 'true') notifyAndRedirect('Classroom deleted successfully!', 'reload');
                    else notifyAndRedirect('You have left the classroom.', 'reload');
              } else {
                  notifyAndRedirect('Leaving the classroom failed. An error has occured.', 'error');
              }
          })
          .catch(error => {
            console.error('Error:', error);
            alert('An error occurred: ' + error.message);
          });

      }
      else return;
  }


  var name = "";
  var desc = "";
  var code = "";
  var creator = "";
  var classid = "";
  
  // Show Join Overlay
  function showJoinOverlay(element) {
  
    // Find the parent classroom-card element
    const classCard = element.closest('.classroom-card');
    if (!classCard) {
      console.error("Error: Classroom card not found.");
      return;
    }

    

    // Get data attributes directly
    name = classCard.getAttribute('classroom-name');
    desc = classCard.getAttribute('classroom-desc');
    code = classCard.getAttribute('classroom-code');
    creator = classCard.getAttribute('classroom-creator');
    classid = classCard.getAttribute('classroom-id');

    
    // Update overlay fields
    document.getElementById('joinName').textContent = name;
    document.getElementById('joinDesc').textContent = desc;
    document.getElementById('joinCreator').textContent = creator;

    // Show the overlay
    showSubOverlay('joinOverlay');
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
          notifyAndRedirect('Classroom joined successfully!', 'reload');
        } else {
          notifyAndRedirect('Failed in joining the classroom. An error has occured.', 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred: ' + error.message);
      });
  }

 // Show View Module
  var selectedModuleID = "";

  function showViewModule(element) {
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
    fetch('instructorFunctions.php', {
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

      const { moduleName, moduleDesc, className, lessons: lessonArray } = data;

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
                    <button class="view-lesson" onclick="showViewLesson(this)">
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

    if (!moduleID) {
      console.error("Error: Module ID not found.");
      return;
    }

    fetch('instructorFunctions.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'deleteModule',
        data: { moduleID: moduleID }
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
         notifyAndRedirect('Module deleted succesfully!', 'reload')
      } else {
        notifyAndRedirect('Module delettion failed. An error has occured.', '');
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
  fetch('instructorFunctions.php', {
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
</script>

</body>
</html>

<?php
  mysqli_close($conn);
?>
