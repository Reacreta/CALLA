<?php
  require_once 'database.php';
  require_once 'authFunctions.php';

  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);

  ob_start();
  session_start();

  sessionCheck('Student');
  
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
      position: absolute;
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
      position: absolute;
      border: 2px solid white;
      border-radius: 6px 6px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
      top: 10%;
      left: 20%;
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
    gap: 20px; /* Optional for spacing between sections */
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
    background: #8b0000;
    color: white;
  }

  /* Create Module Overlay */
  .create-module-overlay{
    display: none;
    position: absolute;
    border: 2px solid white;
    border-radius: 6px 6px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    top: 10%;
    left: 20%;
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
      position: absolute;
      border: 2px solid white;
      border-radius: 6px 6px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
      top: 10%;
      left: 20%;
      height: fit-content;
      width: 50%;
      background: rgba(241, 241, 241, 0.85);
      backdrop-filter: blur(5px);
      z-index: 20;
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
      position: absolute;
      border: 2px solid white;
      border-radius: 6px 6px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
      top: 10%;
      left: 20%;
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

    .show, #viewModuleOverlay.show, #viewLessonOverlay.show, #joinOverlay.show, 
    #viewClassroomDetailsOverlay.show, #create-module-overlay.show, #wordSearchGameOverlay.show {
      display: block;
    }
    
     /* Word Search Game Styles */
        #wordSearchGameOverlay {
          display: none;
          padding: 20px;
        }
        #wordSearchGame {
            display: flex;
            gap: 30px;
            padding: 20px;
            background: linear-gradient(135deg, #f9f9f9, #f0f0f0);
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            margin: 20px 0;
            min-height: 500px;
            position: relative;
        }

        .game-header {
            display: flex;
            gap: 35px;
            align-items: center;
            margin-top: 20px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e0e0e0;
        }

        .game-title {
            color: #7b0000;
            font-size: 1.8rem;
            margin: 0;
            font-weight: 600;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }

        .game-difficulty {
            display: flex;
            margin-top: 10px;
            align-items: center;
            gap: 12px;
        }

        .game-difficulty label {
            font-weight: 600;
            color: #555;
            font-size: 1rem;
        }

        #gameDifficulty {
            padding: 8px 12px;
            border-radius: 8px;
            border: 2px solid #ddd;
            background: linear-gradient(135deg, #fff, #f8f8f8);
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        #gameDifficulty:hover {
            border-color: #7b0000;
            box-shadow: 0 0 8px rgba(123, 0, 0, 0.2);
        }

        .grid {
            display: grid;
            grid-gap: 4px;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #fff, #f5f5f5);
            padding: 15px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
        }

        .cell {
            width: 40px;
            height: 40px;
            border: 2px solid #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            user-select: none;
            background: linear-gradient(135deg, #ffffff, #f8f8f8);
            font-weight: 600;
            font-size: 1.2rem;
            color: #333;
            border-radius: 8px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .cell::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.6), transparent);
            transition: left 0.5s;
        }

        .cell:hover::before {
            left: 100%;
        }

        .cell:hover {
            background: linear-gradient(135deg, #f0f8ff, #e6f3ff);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-color: #7b0000;
        }

        .cell.selected {
            background: linear-gradient(135deg, #a0d8ff, #87ceeb);
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 6px 20px rgba(160, 216, 255, 0.4);
            border-color: #0066cc;
            animation: pulse 0.6s ease-in-out;
        }

        @keyframes pulse {
            0%, 100% { transform: translateY(-2px) scale(1.05); }
            50% { transform: translateY(-2px) scale(1.1); }
        }

        .cell.found {
            background: linear-gradient(135deg, #c8e6c9, #a5d6a7);
            color: #1b5e20;
            border-color: #4caf50;
            animation: celebrate 0.8s ease-out;
        }

        @keyframes celebrate {
            0% { transform: scale(1); }
            50% { transform: scale(1.2) rotate(5deg); }
            100% { transform: scale(1); }
        }

        .word-list {
            flex: 1;
            background: linear-gradient(135deg, #ffffff, #f8f8f8);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            max-height: 500px;
            overflow-y: auto;
        }

        .word-list::-webkit-scrollbar {
            width: 8px;
        }

        .word-list::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .word-list::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #7b0000, #a00000);
            border-radius: 4px;
        }

        .word-list h3 {
            color: #7b0000;
            margin-top: 0;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e0e0e0;
            font-size: 1.3rem;
            font-weight: 600;
        }

        .word-item {
            padding: 12px 18px;
            margin: 10px 0;
            cursor: pointer;
            background: linear-gradient(135deg, #f8f8f8, #f0f0f0);
            border-radius: 10px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            position: relative;
            border: 2px solid transparent;
            font-weight: 500;
        }

        .word-item:hover {
            background: linear-gradient(135deg, #e8f4fd, #ddeeff);
            transform: translateX(5px);
            border-color: #7b0000;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .word-item.found {
            background: linear-gradient(135deg, #e8f5e9, #d4edda);
            color: #155724;
            border-color: #28a745;
            transform: translateX(0);
        }

        .word-item.found::before {
            content: "✓ ";
            position: absolute;
            left: 8px;
            top: 50%;
            transform: translateY(-50%);
            color: #28a745;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .word-item.found {
            padding-left: 35px;
        }

        .hint {
            font-style: italic;
            color: #666;
            margin-top: 8px;
            font-size: 0.9em;
            padding: 8px 12px;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 6px;
            border-left: 3px solid #7b0000;
            display: none;
            transition: all 0.3s ease;
        }


        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .word-item.found .hint {
            display: block;
            color: #155724;
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            border-left-color: #28a745;
        }

        #playGameBtn {
            background: linear-gradient(135deg, #7b0000, #a00000);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(123, 0, 0, 0.3);
        }

        #playGameBtn:hover {
            background: linear-gradient(135deg, #5a0000, #800000);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(123, 0, 0, 0.4);
        }

        #playGameBtn:active {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(123, 0, 0, 0.3);
        }

        .close-game-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 1.8rem;
            cursor: pointer;
            color: #666;
            transition: all 0.3s ease;
            width: 35px;
            height: 35px;
            padding-left: 0.5px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.8);
            backdrop-filter: blur(10px);
        }
        
        .close-game-btn:hover {
            color: #7b0000;
            transform: scale(1.1) rotate(90deg);
            background: rgba(255,255,255,1);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        /*Responsiveness */
        @media (max-width: 768px) {
            #wordSearchGame {
                flex-direction: column;
                gap: 20px;
                padding: 15px;
            }
            
            .game-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .cell {
                width: 30px;
                height: 30px;
                font-size: 1rem;
            }
            
            .grid {
                justify-self: center;
            }

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

        /* Loading Animation */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 40px;
            height: 40px;
            margin: -20px 0 0 -20px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #7b0000;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /*Completion notification styles */
        #gameCompletionNotification {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0);
            background: linear-gradient(135deg, #4CAF50, #45a049, #66BB6A);
            color: white;
            padding: 30px 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            z-index: 10000;
            font-family: Arial, sans-serif;
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            border: 3px solid #fff;
            backdrop-filter: blur(10px);
        }

        @keyframes popIn {
            0% {
                transform: translate(-50%, -50%) scale(0);
                opacity: 0;
            }
            80% {
                transform: translate(-50%, -50%) scale(1.1);
            }
            100% {
                transform: translate(-50%, -50%) scale(1);
                opacity: 1;
            }
        }
        
        @keyframes fadeOut {
            0% {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1);
            }
            100% {
                opacity: 0;
                transform: translate(-50%, -50%) scale(0.8);
            }
        }
  </style>
</head>

<body>
  <div class="header">
    <div class="title"><img id="logo" src="images/logo.png"><div id="role"><span>STUDENT</span></div></div>
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
        <button class="nav-btn" onclick="showOverlay('classroomOverlay')"><img src="images/Class_Icon.jpg" class="User-icon" alt="Classroom Icon"> <div>Classrooms</div></button>
        <button class="nav-btn" onclick="showOverlay('moduleOverlay')"><img src="images/Module_Icon.jpg" class="User-icon" alt="Module Icon"><div>Modules</div></button>
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
            <button class="tab" onclick="setClassFilter('enrolled')">Enrolled</button>
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
            // get table of all classrooms and their creator
            $sql = "SELECT * 
                    FROM classroom 
                    JOIN instructor ON classroom.instID = instructor.instID 
                    JOIN users ON instructor.userID = users.userID;";
            $result = $conn->query($sql);

            while ($row = $result->fetch_assoc()) {
                $studentID = $_SESSION['roleID']; // Assuming this is the student's ID
                $classroomID = htmlspecialchars($row['classroomID']);
                $className = htmlspecialchars($row['className']);
                $classDesc = htmlspecialchars($row['classDesc']);
                $classCode = htmlspecialchars($row['classCode']);
                $creatorName = htmlspecialchars($row['username']);
                
                // Check if already enrolled
                $sql = "SELECT * FROM enrolledstudent es 
                        WHERE es.studentID = ? AND es.classroomID = ?;";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ss', $studentID, $classroomID);
                $stmt->execute();
                $res = $stmt->get_result();

                // Not enrolled - show as joinable
                if($res->num_rows == 0) { 
            ?>
                    <div class="classroom-card" class-type="joinable"
                        classroom-id="<?php echo $classroomID?>"
                        classroom-name="<?php echo $className?>"
                        classroom-desc="<?php echo $classDesc?>"
                        classroom-code="<?php echo $classCode?>"
                        classroom-creator="<?php echo $creatorName?>">

                        <img src="images/Class_Icon.jpg" alt="Class Icon" class="classroom-icon">
                        <div class="classroom-info">
                            <div class="classroom-title"><?php echo $className; ?></div>
                            <div class="classroom-creator"><?php echo $creatorName; ?></div>
                        </div>
                        <Button id="joinClass" onclick="showJoinOverlay(this)">Join</Button>
                    </div>
            <?php 
                } else {
                    // Already enrolled - show as enrolled
            ?>
                    <div class="classroom-card" class-type="enrolled"
                        classroom-id="<?php echo $classroomID?>">
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
              <button class="cd-btn cd-btn-leave" onclick="leaveClass()">Leave</button>
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
        <div id="moduleOverlay" class="module-overlay" overlay-type="module">
        <button class="close-btn" onclick="hideOverlay('moduleOverlay')">×</button>
        <h2 style="color: #7b0000; margin-bottom: 20px;">Available Modules</h2>

        <div class="tabs">
            <div id="tabHeader">Enrolled</div>
            <div class="right-buttons">
                <div class="search-container">
                    <input type="text" placeholder="Search modules..." class="search-input">
                    <label class="SearchButton" onclick="toggleSearch(this)">Search</label>
                </div>
            </div>
        </div>

        <div class="list-wrapper">
            <div class="dynamic-list">
                <?php
                // Query to get modules available to the student through their enrolled classrooms
                $sql = "
                SELECT DISTINCT
                    lm.langID, 
                    lm.moduleName,
                    lm.moduleDesc,
                    c.className,
                    u.firstName,
                    u.lastName,
                    lm.dateCreated
                FROM enrolledstudent es
                JOIN classroom c ON es.classroomID = c.classroomID
                JOIN classinstructor ci ON c.classroomID = ci.classroomID
                JOIN classmodule cm ON ci.classInstID = cm.classInstID
                JOIN languagemodule lm ON cm.langID = lm.langID
                JOIN instructor i ON ci.instID = i.instID
                JOIN users u ON i.userID = u.userID
                WHERE es.studentID = ?
                ORDER BY lm.dateCreated DESC, lm.moduleName ASC;
                ";

                $stmt = $conn->prepare($sql); 
                $stmt->bind_param('s', $_SESSION['roleID']);
                $stmt->execute();
                
                debug_console("StudentID: ".$_SESSION['roleID']);

                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $instructorName = htmlspecialchars($row['firstName'] . ' ' . $row['lastName']);
                ?>
                        <div class="module-card" module-id="<?php echo htmlspecialchars($row['langID'])?>">
                            <img src="images/Module_Icon.jpg" alt="Module Icon" class="module-icon">
                            <div class="module-info">
                                <div class="module-title"><?= htmlspecialchars($row['moduleName']) ?></div>
                                <div class="module-creator">In <?= htmlspecialchars($row['className']) ?></div>
                            </div>
                            <button id="viewModule" onclick="showViewModule(this)">
                                <img src="images/Search_Icon.jpg" alt="View Module" class="search-image-icon">
                            </button>
                        </div>    
                <?php
                    }
                } else {
                ?>
                    <div class="no-modules-message">  
                        <h3>No Modules Available</h3>
                        <p>You haven't enrolled in any classrooms with modules yet.</p>
                        <p>Join a classroom to access learning modules!</p>
                    </div>
                <?php
                }
                ?>
            </div>
        </div>
    </div><!-- End Module Overlay-->



      <!-- View Module Overlay -->
      <div id="viewModuleOverlay" class="view-module-overlay" overlay-type = "view-module-overlay">
        <div id="viewModuleCon">
          <div id="viewModuleHeader">
            <button class="close-btn" onclick="hideSubOverlay('viewModuleOverlay','moduleOverlay')">×</button>
            <h2 style="color: #7b0000; margin-bottom: 20px;">View Module</h2>
          </div>
          <div id="viewModuleMain">
                  <!-- I Edit ni siya sa adtong scipt sa java script i love jollibee -->
          </div>
          <div id="viewModuleSC" class="view-module-SC">
            <button type="button" class="create-mod-btn" onclick="hideSubOverlay('viewModuleOverlay','moduleOverlay')">Close</button>
          </div>
        </div>
      </div><!-- End View Module Overlay -->

      <!-- View Lesson Overlay -->
      <div id="viewLessonOverlay" class="view-lesson-overlay" overlay-type="view-lesson-overlay">
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

      <!-- Word Search Game Overlay -->
      <div id="wordSearchGameOverlay" class="ViewLessonOverlay">
        <div class="game-header">
            <h2 class="game-title">🔍 Word Search Challenge</h2>
            <div class="game-difficulty">
                <label for="gameDifficulty">Difficulty:</label>
                <select id="gameDifficulty" onchange="generateWordSearch()">
                    <option value="10"selected>Easy (10×10)</option>
                    <option value="15">Medium (15×15)</option>
                    <option value="20">Hard (20×20)</option>
                </select>
            </div>
        </div>
        <span class="close-game-btn" onclick="hideOverlay('wordSearchGameOverlay')">×</span>
        <div id="wordSearchGame"></div>
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

  function showOverlay(targetId, backgroundIds = null) {
    const overlays = [
    'classroomOverlay', 'moduleOverlay', 'joinOverlay','viewModuleOverlay', 
    'viewLessonOverlay', 'viewClassroomDetailsOverlay', 'wordSearchGameOverlay'];
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
function showClassDetails(element) {
  console.log("Show Classroom Details");
  showOverlay('viewClassroomDetailsOverlay', 'classroomOverlay');

  const classCard = element.closest('.classroom-card');
  const classID = classCard.getAttribute('classroom-id');
  selectedClassroomID = classID;
  if (!classID) {
    console.error("Error: Classroom ID not found.");
    return;
  }

  // Fetch classroom details
  console.log('Sending Fetch Request to studentFunctions.php');
  fetch('studentFunctions.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      action: 'getClassroomDetails',
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

      // Generate HTML content - Updated to match PHP response structure
      const headerContent = `
        <div id="cdClassName">${classroomDetails.className}</div>
        <div id="cdCreator">${classroomDetails.creatorName}</div>
      `;
      const descriptionContent = `
        <h2 class="cd-section-title">Description:</h2>
        <div id="cd-description-text">${classroomDetails.classDesc}</div>
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
      
      // Updated instructor content to match PHP response fields
      const instructorContent = instructors.length > 0 ? `
        <h2 class="cd-section-title">Instructors</h2>
        <div id="cd-instructor-list">
          ${instructors.map(instructor => `
            <div class="cd-student-card">
              <img src="images/Human_Icon.jpg" alt="Instructor" class="cd-list-icon">
              <div class="cd-student-info">
                <h3>${instructor.firstName} ${instructor.lastName}</h3>
                <p>@${instructor.username}</p>
              </div>
            </div>
          `).join('')}
        </div>
      ` : `<p>No instructors available.</p>`;

      // Updated student content to match PHP response fields
      const studentContent = students.length > 0 ? `
        <h2 class="cd-section-title">Students</h2>
        <div id="cd-students-list">
          ${students.map(student => `
            <div class="cd-student-card">
              <img src="images/Human_Icon.jpg" alt="Student" class="cd-list-icon">
              <div class="cd-student-info">
                <h3>${student.firstName} ${student.lastName}</h3>
                <p>@${student.username}</p>
              </div>
            </div>
          `).join('')}
        </div>
      ` : `<p>No students available.</p>`;

      // Updated module content to match PHP response fields and include progress
      const moduleContent = modules.length > 0 ? `
        <h2 class="cd-section-title">Modules</h2>
        <div id="cd-module-list">
          ${modules.map(module => `
            <div class="cd-module-card">
              <img src="images/Module_Icon.jpg" alt="Module Icon" class="cd-list-icon">
              <div class="cd-module-info">
                <h3>${module.moduleName}</h3>
                <p>${module.moduleDesc || 'No description available'}</p>
                <small>Created: ${module.dateCreated}</small>
              </div>
            </div>
          `).join('')}
        </div>
      ` : `<p>No modules available.</p>`;

      // Update DOM elements
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


  // Leave Class
  function leaveClass() {
      let message = "Are you sure you want to leave this classroom?";
      const confirmed = confirm(message);

      if (confirmed) {
          // Add classroom deletion process here
          fetch('studentFunctions.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' }
              ,
              body: JSON.stringify({
                  action: 'leaveClass',
                  classID: selectedClassroomID,
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
                  notifyAndRedirect('You have left the classroom.', 'reload');
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

    fetch('studentFunctions.php', {
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
          alert('Failed to join classroom: ' + result.message);
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
    showOverlay('viewModuleOverlay', ['moduleOverlay']);

    const moduleCard = element.closest('.module-card');
    const moduleID = moduleCard.getAttribute('module-id');

    selectedModuleID = moduleID;
    console.log("Module ID: " + moduleID);

    if (!moduleID) {
      console.error("Error: Module ID not found.");
      return;
    }

    console.log('Sending Fetch Request to student.php');
    fetch('studentFunctions.php', {
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

  
   // Show View Lesson
  function showViewLesson(element) {
    console.log("View Lesson");
    showOverlay('viewLessonOverlay', ['viewModuleOverlay','moduleOverlay']);

    const lessonCard = element.closest('.module-card');
    const lessonID = lessonCard.getAttribute('lesson-id');

    console.log("Lesson ID: " + lessonID);

    if (!lessonID) {
        console.error("Error: Lesson ID not found.");
        return;
    }

    fetch('studentFunctions.php', {
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
        return response.json();
    })
    .then(result => {
        if (!result.success) {
            throw new Error(result.message || 'Failed to load lesson details');
        }

        const lesson = result.lesson;
        const vocabulary = result.vocabulary || [];

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
                <button id="playGameBtn" onclick="startWordSearchGame()" style="margin: 10px 0; padding: 8px 15px; background: #7b0000; color: white; border: none; border-radius: 4px; cursor: pointer;">
                    Play Word Search Game
                </button>
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
                                ${vocabulary.map(word => `
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

        document.getElementById('viewLessonMain').innerHTML = htmlContent;
        
        // Store vocabulary for the game
        currentLessonWords = vocabulary.map(item => item.word.toUpperCase());
        currentLessonMeanings = {};
        vocabulary.forEach(item => {
            currentLessonMeanings[item.word.toUpperCase()] = item.meaning;
        });
    })
    .catch(error => {
        console.error("Fetch error:", error);
        document.getElementById('viewLessonMain').innerHTML = `
            <div class="error" style="color: red; padding: 20px;">
                Error loading lesson: ${error.message}
            </div>
        `;
    });
}

 // Enhanced global variables with better organization
        const GameState = {
            currentLessonWords: [],
            currentLessonMeanings: {},
            isSelecting: false,
            selectedCells: [],
            foundWords: new Set(),
            gameStats: {
                startTime: null,
                wordsFound: 0,
                totalWords: 0
            }
        };

        // Enhanced game initialization with error handling and performance improvements
        function startWordSearchGame() {
            console.log('Starting Word Search Game...');
            
            try {
                const vocabularyTable = document.querySelector('.dynamic-table tbody');
                
                if (!vocabularyTable) {
                    throw new Error('Vocabulary table not found');
                }
                
                const rows = vocabularyTable.querySelectorAll('tr');
                
                if (rows.length === 0) {
                    throw new Error('No vocabulary rows found');
                }
                
                // Reset game state
                GameState.currentLessonWords = [];
                GameState.currentLessonMeanings = {};
                GameState.foundWords.clear();
                
                // Extract words with validation
                rows.forEach((row, index) => {
                    if (row.cells && row.cells.length >= 2) {
                        const word = row.cells[0].textContent.trim().toUpperCase();
                        const meaning = row.cells[1].textContent.trim();
                        
                        if (word && meaning && word.length >= 3) { // Minimum word length validation
                            GameState.currentLessonWords.push(word);
                            GameState.currentLessonMeanings[word] = meaning;
                        }
                    }
                });
                
                if (GameState.currentLessonWords.length === 0) {
                    throw new Error('No valid vocabulary words found');
                }
                
                console.log(`📚 Loaded ${GameState.currentLessonWords.length} words`);
                
                // Initialize game stats
                GameState.gameStats = {
                    startTime: Date.now(),
                    wordsFound: 0,
                    totalWords: GameState.currentLessonWords.length
                };
                
                showOverlay('wordSearchGameOverlay');
                generateWordSearch();
                
            } catch (error) {
                console.error('❌ Game initialization failed:', error);
                alert(`Game Error: ${error.message}`);
            }
        }

        // Enhanced word search generation with better algorithm and performance
        function generateWordSearch() {
            console.log('🔄 Generating word search grid...');
            
            const difficultyElement = document.getElementById('gameDifficulty');
            const gameContainer = document.getElementById('wordSearchGame');
            
            if (!difficultyElement || !gameContainer) {
                console.error('❌ Required elements not found');
                return;
            }
            
            const difficulty = parseInt(difficultyElement.value);
            const gameWords = [...GameState.currentLessonWords]; // Create copy
            
            // Add loading state
            gameContainer.classList.add('loading');
            
            // Use setTimeout to prevent UI blocking
            setTimeout(() => {
                try {
                    const hints = gameWords.map(word => GameState.currentLessonMeanings[word]);
                    
                    const gameHTML = `
                        <div class="grid" id="wordGrid" data-size="${difficulty}"></div>
                        <div class="word-list" id="wordList">
                            <h3>🎯 Words to Find (${gameWords.length}):</h3>
                            ${gameWords.map((word, index) => `
                                <div class="word-item" data-word="${word}" onclick="toggleHint('${word}')">
                                    <strong>${word}</strong>
                                    <span class="hint" id="hint-${word}">
                                        💡 ${hints[index]}
                                    </span>
                                </div>
                            `).join('')}
                        </div>
                    `;
                    
                    gameContainer.innerHTML = gameHTML;
                    
                    const grid = document.getElementById('wordGrid');
                    grid.style.gridTemplateColumns = `repeat(${difficulty}, 1fr)`;
                    
                    // Create cells with improved performance
                    const fragment = document.createDocumentFragment();
                    for (let i = 0; i < difficulty * difficulty; i++) {
                        const cell = document.createElement('div');
                        cell.className = 'cell';
                        cell.dataset.index = i;
                        fragment.appendChild(cell);
                    }
                    grid.appendChild(fragment);
                    
                    // Place words with improved algorithm
                    placeWordsInGrid(gameWords, difficulty);
                    fillEmptyCells(difficulty);
                    setupWordSelection(difficulty);
                    
                    gameContainer.classList.remove('loading');
                    console.log('✅ Word search generated successfully');
                    
                } catch (error) {
                    console.error('❌ Grid generation failed:', error);
                    gameContainer.innerHTML = '<p>Error generating game. Please try again.</p>';
                    gameContainer.classList.remove('loading');
                }
            }, 100);
        }

        // Enhanced word placement with better collision detection
        function placeWordsInGrid(words, size) {
            const gridCells = document.querySelectorAll('#wordGrid .cell');
            const directions = [
                { dr: 0, dc: 1, name: 'horizontal' },
                { dr: 1, dc: 0, name: 'vertical' },
                { dr: 1, dc: 1, name: 'diagonal-down' },
                { dr: -1, dc: 1, name: 'diagonal-up' }
            ];
            
            // Sort words by length (longer first for better placement)
            const sortedWords = [...words].sort((a, b) => b.length - a.length);
            
            sortedWords.forEach((word) => {
                let placed = false;
                let attempts = 0;
                const maxAttempts = size * size; // Increased attempts based on grid size
                
                while (!placed && attempts < maxAttempts) {
                    const direction = directions[Math.floor(Math.random() * directions.length)];
                    const row = Math.floor(Math.random() * size);
                    const col = Math.floor(Math.random() * size);
                    
                    if (canPlaceWord(gridCells, word, row, col, direction, size)) {
                        placeWord(gridCells, word, row, col, direction, size);
                        placed = true;
                        console.log(`✅ Placed "${word}" (${direction.name})`);
                    }
                    
                    attempts++;
                }
                
                if (!placed) {
                    console.warn(`⚠️ Could not place word: ${word}`);
                }
            });
        }

        // Optimized collision detection
        function canPlaceWord(cells, word, row, col, direction, size) {
            const length = word.length;
            
            // Check bounds more efficiently
            const endRow = row + direction.dr * (length - 1);
            const endCol = col + direction.dc * (length - 1);
            
            if (endRow < 0 || endRow >= size || endCol < 0 || endCol >= size) {
                return false;
            }
            
            // Check for conflicts with early termination
            for (let i = 0; i < length; i++) {
                const r = row + direction.dr * i;
                const c = col + direction.dc * i;
                const index = r * size + c;
                const cellContent = cells[index].textContent;
                
                if (cellContent !== '' && cellContent !== word[i]) {
                    return false;
                }
            }
            
            return true;
        }

        // Enhanced word placement with data attributes
        function placeWord(cells, word, row, col, direction, size) {
            const positions = [];
            
            for (let i = 0; i < word.length; i++) {
                const r = row + direction.dr * i;
                const c = col + direction.dc * i;
                const index = r * size + c;
                
                cells[index].textContent = word[i];
                cells[index].classList.add('word-cell');
                cells[index].dataset.word = word;
                cells[index].dataset.position = i;
                
                positions.push(index);
            }
            
            // Store word positions for validation
            cells[row * size + col].dataset.wordPositions = JSON.stringify(positions);
        }

        // Enhanced random letter generation with better distribution
        function fillEmptyCells(size) {
            const gridCells = document.querySelectorAll('#wordGrid .cell');
            const letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            
            gridCells.forEach(cell => {
                if (cell.textContent === '') {
                    // Weight certain letters more heavily for better gameplay
                    const commonLetters = 'AEIOURSTLNMDHCGPYFWBVKJXQZ';
                    const randomIndex = Math.floor(Math.random() * commonLetters.length);
                    cell.textContent = commonLetters[randomIndex];
                }
            });
        }

        // Enhanced selection system with touch support and better feedback
        function setupWordSelection(size) {
            const cells = document.querySelectorAll('#wordGrid .cell');
            let isSelecting = false;
            let currentSelection = [];
            let startCell = null;
            
            // Add both mouse and touch event listeners
            cells.forEach(cell => {
                // Mouse events
                cell.addEventListener('mousedown', handleSelectionStart);
                cell.addEventListener('mouseover', handleSelectionMove);
                cell.addEventListener('mouseup', handleSelectionEnd);
            });
            
            // Prevent context menu on right click
            document.getElementById('wordGrid').addEventListener('contextmenu', e => e.preventDefault());
            
            function handleSelectionStart(e) {
                if (e.button !== 0) return; // Only left click
                
                e.preventDefault();
                isSelecting = true;
                startCell = e.target;
                
                clearSelection();
                const index = parseInt(e.target.dataset.index);
                currentSelection = [index];
                e.target.classList.add('selected');
            }
            
            function handleSelectionMove(e) {
                if (!isSelecting || !startCell) return;
                
                const index = parseInt(e.target.dataset.index);
                if (isValidSelection(currentSelection[currentSelection.length - 1], index, size)) {
                    if (!currentSelection.includes(index)) {
                        currentSelection.push(index);
                        e.target.classList.add('selected');
                    }
                }
            }
            
            function handleSelectionEnd(e) {
                if (!isSelecting) return;
                
                isSelecting = false;
                validateSelection();
                startCell = null;
            }
            
            // Touch event handlers
            function handleTouchStart(e) {
                e.preventDefault();
                const touch = e.touches[0];
                const element = document.elementFromPoint(touch.clientX, touch.clientY);
                if (element && element.classList.contains('cell')) {
                    handleSelectionStart({ target: element, button: 0, preventDefault: () => {} });
                }
            }
            
            function handleTouchMove(e) {
                e.preventDefault();
                const touch = e.touches[0];
                const element = document.elementFromPoint(touch.clientX, touch.clientY);
                if (element && element.classList.contains('cell')) {
                    handleSelectionMove({ target: element });
                }
            }
            
            function handleTouchEnd(e) {
                e.preventDefault();
                handleSelectionEnd({});
            }
            
            function isValidSelection(lastIndex, newIndex, gridSize) {
                if (lastIndex === undefined) return true;
                
                const lastRow = Math.floor(lastIndex / gridSize);
                const lastCol = lastIndex % gridSize;
                const newRow = Math.floor(newIndex / gridSize);
                const newCol = newIndex % gridSize;
                
                const rowDiff = Math.abs(newRow - lastRow);
                const colDiff = Math.abs(newCol - lastCol);
                
                // Allow adjacent cells (including diagonals)
                return (rowDiff <= 1 && colDiff <= 1) && !(rowDiff === 0 && colDiff === 0);
            }
            
            function validateSelection() {
                if (currentSelection.length < 2) {
                    clearSelection();
                    return;
                }
                
                const selectedWord = currentSelection.map(index => 
                    cells[index].textContent
                ).join('');
                
                const reversedWord = selectedWord.split('').reverse().join('');
                
                // Check both directions
                const wordToCheck = GameState.currentLessonWords.find(word => 
                    word === selectedWord || word === reversedWord
                );
                
                if (wordToCheck && !GameState.foundWords.has(wordToCheck)) {
                    handleWordFound(wordToCheck, currentSelection);
                } else {
                    // Add shake animation for invalid selection
                    currentSelection.forEach(index => {
                        cells[index].style.animation = 'shake 0.3s ease-in-out';
                        setTimeout(() => {
                            cells[index].style.animation = '';
                        }, 300);
                    });
                }
                
                clearSelection();
            }
            
           function handleWordFound(word, cellIndices) {
            GameState.foundWords.add(word);
            GameState.gameStats.wordsFound++;
            
            // Mark cells as found
            cellIndices.forEach(index => {
                cells[index].classList.add('found');
                cells[index].classList.remove('selected');
            });
            
            // Mark word item as found - THIS WAS MISSING
            const wordItems = document.querySelectorAll(`[data-word="${word}"]`);
            wordItems.forEach(item => {
                item.classList.add('found');
                const hintElement = item.querySelector('.hint');
                if (hintElement) {
                    hintElement.style.display = 'block';
                }
            });
            
            console.log(`🎉 Found word: ${word} (${GameState.gameStats.wordsFound}/${GameState.gameStats.totalWords})`);
            checkGameCompletion();
        }
            
            function clearSelection() {
                cells.forEach(cell => {
                    if (!cell.classList.contains('found')) {
                        cell.classList.remove('selected');
                    }
                });
                currentSelection = [];
            }
        }

          function checkGameCompletion() {
          console.log('Checking game completion');
          const totalWords = GameState.gameStats.totalWords;
          const foundWords = GameState.gameStats.wordsFound;
          
          console.log(`Current progress: ${foundWords}/${totalWords}`);
          
          if (foundWords >= totalWords) {
              setTimeout(() => {
                  console.log('All words found! Showing completion notification');
                  showCompletionNotification();
              }, 100);
          }
      }

      // Function to show completion notification
      function showCompletionNotification() {
          // Create notification element
          const notification = document.createElement('div');
          notification.id = 'gameCompletionNotification';
          notification.innerHTML = `
              <div class="notification-content">
                  🎉 Congratulations! 🎉<br>
                  You found all the words!
                  <small>Returning to lessons in 3 seconds...</small>
              </div>
          `;
          
          // Add styles
          notification.style.cssText = `
              position: fixed;
              top: 50%;
              left: 50%;
              transform: translate(-50%, -50%) scale(0);
              background: linear-gradient(135deg, #4CAF50, #45a049);
              color: white;
              padding: 20px 30px;
              border-radius: 15px;
              box-shadow: 0 10px 25px rgba(0,0,0,0.3);
              z-index: 10000;
              font-family: Arial, sans-serif;
              font-size: 18px;
              font-weight: bold;
              text-align: center;
              border: 3px solid #fff;
              animation: popIn 0.5s ease-out forwards, fadeOut 0.5s ease-in 2.5s forwards;
          `;
          
          // Add animation keyframes to document if not already added
          if (!document.getElementById('notification-styles')) {
              const style = document.createElement('style');
              style.id = 'notification-styles';
              style.textContent = `
                  @keyframes popIn {
                      0% {
                          transform: translate(-50%, -50%) scale(0);
                          opacity: 0;
                      }
                      80% {
                          transform: translate(-50%, -50%) scale(1.1);
                      }
                      100% {
                          transform: translate(-50%, -50%) scale(1);
                          opacity: 1;
                      }
                  }
                  
                  @keyframes fadeOut {
                      0% {
                          opacity: 1;
                          transform: translate(-50%, -50%) scale(1);
                      }
                      100% {
                          opacity: 0;
                          transform: translate(-50%, -50%) scale(0.8);
                      }
                  }
              `;
              document.head.appendChild(style);
          }
          
          // Add to document
          document.body.appendChild(notification);
          
          // Remove notification after animation completes
          setTimeout(() => {
          if (notification.parentNode) {
              notification.parentNode.removeChild(notification);
          }
          GameState.foundWords.clear();
          GameState.gameStats.wordsFound = 0;
          showOverlay('viewLessonOverlay', ['viewModuleOverlay','moduleOverlay']); // Directly specify return target
          
        }, 3000);
      }
      

      function toggleHint(word) {
          const hintElement = document.getElementById(`hint-${word}`);
          if (hintElement) {
              hintElement.style.display = hintElement.style.display === 'none' ? 'inline' : 'none';
          }
      }
</script>

</body>
</html>

<?php
  mysqli_close($conn);
?>
