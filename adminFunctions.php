<?php
    ob_start();
    session_start();
  require_once 'database.php';
  require_once 'authFunctions.php';

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Decode the JSON payload
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? null;
  
    if ($action === 'getActivityLogs'){
      $data = $input['data'] ?? null;
      $userID = $data['selectedUser'] ?? null;

      $sql = "SELECT * FROM activity a 
              join users u on a.userID = u.userID 
              WHERE a.userID = ?
              ORDER BY a.dateTimeCreated DESC;";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("s", $userID);
      $stmt->execute();
      $result = $stmt->get_result();
      $logs = [];
      while ($row = $result->fetch_assoc()) {
          $logs[] = $row;
      }

      echo json_encode([
        'success' => true,
        'logs' => $logs
      ]);

    }

    if($action === 'getClassroomDetails'){
      $data = $input['data'];
      $classroomID = $data['classID'] ?? null;

      // Validate the data
      if (!$classroomID) {
          echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
          exit; // Stop further execution
      }

      // SQL statement to fetch classroom details
      $sql = "SELECT * FROM classroom c 
              join instructor i on c.instID = i.instID 
              join users u on i.userID = u.userID 
              WHERE c.classroomID=?;";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param('s', $classroomID);
      $stmt->execute();
      $result = $stmt->get_result();
      $row = $result->fetch_assoc();

      $classroomDetails = $row;

      // SQL statment to fetch Instructor details
      $sql = "SELECT * FROM classinstructor ci 
              JOIN instructor i on ci.instID = i.instID 
              JOIN users u ON i.userID = u.userID 
              JOIN classroom c ON c.classroomID = ci.classroomID
              WHERE c.classroomID = ?;";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param('s', $classroomID);
      $stmt->execute();
      $result = $stmt->get_result();
      $instructors = [];

      while($row = $result->fetch_assoc()){
          $instructors[] = $row;
      }
      // SQL statment to fetch Student details
      $sql = "SELECT * FROM enrolledstudent es 
              JOIN student s ON es.studentID = s.studentID 
              JOIN users u on s.userID = u.userID 
              JOIN classroom c on es.classroomID = c.classroomID
              WHERE c.classroomID = ?;";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param('s', $classroomID);
      $stmt->execute();
      $result = $stmt->get_result();
      $students = [];
      while($row = $result->fetch_assoc()){
          $students[] = $row;
      }
      // SQL statment to fetch Module details
      $sql = "SELECT * FROM classmodule cm 
              join classinstructor ci on cm.classInstID = ci.classInstID 
              join classroom c on ci.classroomID = c.classroomID 
              join languagemodule lm on cm.langID = lm.langID
              join instructor i on ci.instID = i.instID
              join users u on u.userID = i.userID
              WHERE c.classroomID = ?;";

      $stmt = $conn->prepare($sql);
      $stmt->bind_param('s', $classroomID);
      $stmt->execute();
      $result = $stmt->get_result();
      $modules = [];
      while($row = $result->fetch_assoc()){
          $modules[] = $row;
      }

      echo json_encode([
          'success' => true,
          'classroomDetails' => $classroomDetails,
          'instructors' => $instructors,
          'students' => $students,
          'modules' => $modules
      ]);
      exit;

    }

    // -- Admin Functions --

    if ($action === 'updateClass') {
            $className = trim($input['className']);
            $classDesc = trim($input['classDesc']);
            $classID = trim($input['classID']);

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

    if ($action === 'deleteClass') {
            $classID = trim($input['classID']);

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

    if ($action === 'deleteUser') {
            $userID = trim($input['userID']);

            $sql = "DELETE FROM users WHERE userID = ?";
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $stmt->bind_param("s", $userID);
                $stmt->execute();
                echo json_encode(['success' => true]);

            } else {
                echo json_encode(['success' => false, 'error' => $conn->error]);
            }
            exit;
        }

    if ($action === 'deactivateUser') {
            $userID = trim($input['userID']);

            $sql = "UPDATE users SET active = 0 WHERE userID = ?";
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $stmt->bind_param("s", $userID);
                $stmt->execute();
                echo json_encode(['success' => true]);

            } else {
                echo json_encode(['success' => false, 'error' => $conn->error]);
            }
            exit;
        }

    if ($action === 'activateUser') {
            $userID = trim($input['userID']);

            $sql = "UPDATE users SET active = 1 WHERE userID = ?";
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $stmt->bind_param("s", $userID);
                $stmt->execute();
                echo json_encode(['success' => true]);

            } else {
                echo json_encode(['success' => false, 'error' => $conn->error]);
            }
            exit;
        }

    if ($action === 'updatePartner') {
            $partnerName = trim($input['partnerName']);
            $partnerDesc = trim($input['partnerDesc']);
            $partnerContact = trim($input['partnerContact']);
            $partnerEmail = trim($input['partnerEmail']);
            $partnerID = trim($input['partnerID']);

            $sql = "UPDATE partner SET partnerName = ?, partnerDesc = ?, email = ?, contact = ? WHERE partnerID = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("sssss", $partnerName, $partnerDesc, $partnerEmail, $partnerContact, $partnerID);
                $stmt->execute();
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => $conn->error]);
            }
            exit;
        }

    if ($action === 'deletePartner') {
            $partnerID = trim($input['partnerID']);

            $sql = "DELETE FROM partner WHERE partnerID = ?";
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $stmt->bind_param("s", $partnerID);
                $stmt->execute();
                echo json_encode(['success' => true]);

            } else {
                echo json_encode(['success' => false, 'error' => $conn->error]);
            }
            exit;
        }

    // -- End of Functions

    if ($action === 'getModuleDetails') {
      $data = $input['data'];
      $moduleID = $data['moduleID'] ?? null;
  
      if (!$moduleID) {
          echo json_encode(['success' => false, 'message' => 'Module ID is required.']);
          exit;
      }
  
      // Check if naa siya sa classmodules
      $sql = "SELECT 
                  lm.langID, 
                  lm.moduleName, 
                  lm.moduleDesc,
                  u.username AS creator,
                  c.className
              FROM classmodule cm 
              JOIN classinstructor ci ON cm.classInstID = ci.classInstID
              JOIN instructor i ON i.instID = ci.instID
              JOIN users u ON u.userID = i.userID
              JOIN classroom c ON ci.classroomID = c.classroomID
              JOIN languagemodule lm ON lm.langID = cm.langID
              WHERE lm.langID = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param('s', $moduleID);
      $stmt->execute();
      $result = $stmt->get_result();
      $row = $result->fetch_assoc();

      $moduleType = 'classroom';
      if (!$row) { // check if naa siya sa partnermodules
            $sql = "SELECT
                        lm.langID, 
                        lm.moduleName, 
                        lm.moduleDesc,
                        p.partnerName AS creator,
                        p.partnerID
                    FROM partnermodule pm 
                    JOIN partner p ON p.partnerID = pm.partnerID 
                    JOIN languagemodule lm ON lm.langID = pm.langID
                    WHERE lm.langID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('s', $moduleID);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            $moduleType = 'partner';
      }
    

      $moduleName = $row['moduleName'];
      $moduleDesc = $row['moduleDesc'];
      $className = $row['className'] ?? $row['creator'];
      $creator = $row['creator'];
  
      // Fetch lessons for the module
      $sql = "SELECT lessID, lessonName, lessonDesc FROM lesson WHERE langID = ? ORDER BY `lesson`.`lessonName` ASC";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param('s', $moduleID);
      $stmt->execute();
      $result = $stmt->get_result();
  
      $lessons = [];
      while ($row = $result->fetch_assoc()) {
          $lessons[] = $row;
      }
  
      // Return module details and lessons as JSON
      echo json_encode([
            'success' => true,
            'moduleName' => $moduleName,
            'moduleDesc' => $moduleDesc,
            'className' => $className,
            'creator' => $creator,
            'lessons' => $lessons,
            'moduleType' => $moduleType
      ]);
      exit;
    }

  if($action === 'deleteModule'){
    $data = $input['data'];
    $moduleID = $data['moduleID'] ?? null;
    $moduleType = $data['moduleType'] ?? null;
    if (!$moduleID) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
        exit; // Stop further execution
    }
    if($moduleType === 'partner'){
        $sql = "DELETE FROM partnermodule WHERE langID = ?;";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $moduleID);
        if($stmt->execute()){
            echo json_encode(['success' => true]);
        }else{
            echo json_encode(['success' => false, 'message' => 'SQL execution error: ' . $stmt->error]);
        }
    }
    if($moduleType === 'classroom'){
        $sql = "DELETE FROM classmodule WHERE langID = ?;";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $moduleID);
        if($stmt->execute()){
            echo json_encode(['success' => true]);
        }else{
            echo json_encode(['success' => false, 'message' => 'SQL execution error: ' . $stmt->error]);
        }
    }
    }

  if ($action === 'getLessonDetails') {
      $data = $input['data'];
      $lessonID = $data['lessonID'] ?? null;
  
      // Validate the lesson ID
      if (!$lessonID) {
          echo json_encode(['success' => false, 'message' => 'Lesson ID is required.']);
          exit;
      }
  
      // Fetch lesson details
      $sql = "SELECT lessonName, lessonDesc FROM lesson WHERE lessID = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param('s', $lessonID);
      $stmt->execute();
      $result = $stmt->get_result();
      
      $lesson = $result->fetch_assoc();
  
      // Fetch vocabulary for the lesson
      $sql = "SELECT word, meaning FROM vocabulary WHERE lessID = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param('s', $lessonID);
      $stmt->execute();
      $result = $stmt->get_result();

      $vocabulary = [];
      while ($row = $result->fetch_assoc()) {
          $vocabulary[] = $row;
      }
  
      // Return lesson details and vocabulary as JSON
      echo json_encode([
          'success' => true,
          'data' => [
              'lessonName' => $lesson['lessonName'],
              'lessonDesc' => $lesson['lessonDesc'],
              'vocabulary' => $vocabulary
          ]
      ]);
      exit;
  }
  if($action === 'getPartnerDetails'){
      $data = $input['data'];
      $partnerID = $data['partnerID'] ?? null;

      // Validate the data
      if (!$partnerID) {
          echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
          exit; // Stop further execution
      }

      // SQL statement to fetch partner details
      $sql = "SELECT * FROM partner  
              WHERE partnerID=?;";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param('s', $partnerID);
      $stmt->execute();
      $result = $stmt->get_result();
      $row = $result->fetch_assoc();

      $partnerDetails = $row;
      echo json_encode([
          'success' => true,
          'partnerDetails' => $partnerDetails
      ]);
    }
  } // End of AJAX Request Handling


  // File Handling
  if (isset($_FILES['files']) && isset($_POST['upload'])) {
    $response = "";
    $partnerID = $_POST['partnerIDField'];
    $adminID = $_SESSION['roleID'];
    $fileArray = $_FILES['files'];

    // check if Files are uploaded and valid then loop through each file
    if (checkFiles($fileArray)) {
        for($i = 0; $i < count($fileArray['name']); $i++) {
            $file = [
                'name' => $fileArray['name'][$i],
                'tmp_name' => $fileArray['tmp_name'][$i],
                'type' => $fileArray['type'][$i],
                'error' => $fileArray['error'][$i],
                'size' => $fileArray['size'][$i],
            ];

            $fileContent = file_get_contents($file['tmp_name']);

            // Loop, parse and insert modules
            $modulePattern = '/(.*?),\s*(.*?)\s*{(.*)}/s';
            if (preg_match($modulePattern, $fileContent, $moduleMatches)) {
            $moduleName = trim($moduleMatches[1]);
            $moduleDesc = trim($moduleMatches[2]);
            $moduleContent = trim($moduleMatches[3]);

            debug_console("Module Name: " . $moduleName);
            debug_console("Module Desc: " . $moduleDesc);

            $moduleID = generateID("M", 9);
            $pmID = generateID("PM", 8);

            // Insert into Language Modules
            $sql = "INSERT INTO languagemodule (langID, moduleName, moduleDesc, dateCreated)
                    VALUES (?, ?, ?, CURRENT_DATE)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sss', $moduleID, $moduleName, $moduleDesc);
            $stmt->execute();

            // Insert ClassModule
            $sql = "INSERT INTO partnermodule (pmID,partnerID,adminID,langID) VALUES (?,?,?,?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssss', $pmID,$partnerID,$adminID,$moduleID);
            $stmt->execute();

            // loop, parse and insert lessons
            preg_match_all('/{(.*?),\s*(.*?)\s*{(.*?)}}/s', $moduleContent, $lessonMatches, PREG_SET_ORDER);
            error_log("Lesson Matches: " . print_r($lessonMatches, true)); // Debugging line
            foreach ($lessonMatches as $lesson) {
                $lessonName = trim($lesson[1]);
                $lessonDesc = trim($lesson[2]);
                $lessonContent = trim($lesson[3]);
                $lessonID = generateID("L", 9);

                debug_console("Lesson Name: " . $lessonName);
                debug_console("Lesson Desc: " . $lessonDesc);

                $sql = "INSERT INTO lesson (lessID, langID, lessonName, lessonDesc, dateCreated)
                        VALUES (?, ?, ?, ?, CURRENT_DATE)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ssss', $lessonID, $moduleID, $lessonName, $lessonDesc);
                $stmt->execute();
                // loop, parse and insert word-meaning pairs
                preg_match_all('/{\s*(.*?),\s*(.*?)}/s', $lessonContent, $wordMeaningPairs, PREG_SET_ORDER);
                foreach ($wordMeaningPairs as $pair) {
                    $word = trim($pair[1]);
                    $meaning = trim($pair[2]);
                    debug_console("Word: " . $word);
                    debug_console("Meaning: " . $meaning);
                    $wordID = generateID("W", 9);

                    $sql = "INSERT INTO vocabulary (wordID, lessID, word, meaning) VALUES (?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('ssss', $wordID, $lessonID, $word, $meaning);
                    $stmt->execute();
                }
                }
                logAction($conn, $_SESSION['userID'], 'Uploaded module: ' . $moduleName);
            }
            }
        }
      header('Location: Admin.php');
      exit();
    }
?>
