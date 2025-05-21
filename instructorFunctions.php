<?php
    include_once 'database.php';
    include_once 'authFunctions.php';
    ob_start();
    session_start();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Decode the JSON payload
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? null;
    
          if ($action === 'joinClassroom') {
              $data = $input['data'];
    
              // Extract variables from the data
              $creator = $data['creator'] ?? null;
              $id = $data['classid'] ?? null;
    
              $classInstID = generateID('CI',8);
              $roleID = $_SESSION['roleID'];
    
              // Validate the data
              if (!$id) {
                  echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
                  exit; // Stop further execution
              }
    
              // SQL statement to insert the data (open for editing)
              $sql = "INSERT INTO classinstructor (classInstID,instID,classroomID) VALUES (?, ?, ?)";
              $stmt = $conn->prepare($sql);
    
              if (!$stmt) {
                  echo json_encode(['success' => false, 'message' => 'SQL prepare error: ' . $conn->error]);
                  exit; // Stop further execution
              }
    
              $stmt->bind_param('sss', $classInstID, $roleID, $id);
    
              if ($stmt->execute()) {
                    logAction($conn, $_SESSION['userID'], 'Joined classroom with ID: ' . $id);
                  echo json_encode(['success' => true]);
              } else {
                  echo json_encode(['success' => false, 'message' => 'SQL execution error: ' . $stmt->error]);
              }
              exit; // Stop further execution
          }

          if($action === 'getModuleDetails'){
            $data = $input['data'];
            $moduleID = $data['moduleID'] ?? null;

            $sql = "SELECT 
                        lm.langID, 
                        lm.moduleName, 
                        lm.moduleDesc,
                        u.username,
                        c.className
                    FROM classmodule cm 
                    JOIN classinstructor ci ON cm.classInstID = ci.classInstID
                    JOIN instructor i ON i.instID = ci.instID
                    JOIN users u ON u.userID = i.userID
                    JOIN classroom c ON ci.classroomID = c.classroomID
                    JOIN languagemodule lm ON lm.langID = cm.langID
                    WHERE lm.langID = ?;
            ";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('s', $moduleID);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            $moduleName = $row['moduleName'];
            $moduleDesc = $row['moduleDesc'];
            $className = $row['className'];
            $creator = $row['username'];
            
            echo '
            <div id="viewModuleInfo">
              <div id="viewModuleTitle">
                <img src="images/Module_Icon.jpg" alt="Module Icon" class="module-icon">
                <div id="viewModuleInfoText">
                    <div id="viewModuleTitle">'.$moduleName.'</div>
                    <div id="viewModuleClass">'.$creator.'</div>
                </div>
              </div>

              <div id="viewModuleDesc">
                <div id="viewModuleDescText">'.$moduleDesc.'</div>
              </div>

              <div id="lessonList">
                <div class="list-wrapper">
                    <div class="dynamic-list">
            ';

            $sql = "SELECT * from lesson WHERE langID = ? ORDER BY `lesson`.`lessonName` ASC;
            ";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('s', $moduleID);
            $stmt->execute();
            $result = $stmt->get_result();

            while($row = $result->fetch_assoc()){
                $lessonID = $row['lessID'];
                $lessonName = $row['lessonName'];
                echo '
                <div class="module-card" 
                  lesson-id = "'.$lessonID.'">
                    <img src="images/Module_Icon.jpg" alt="Module Icon" class="module-icon">
                    <div class="module-info">
                        <div class="module-title">'.$lessonName.'</div>
                        <div class="module-creator">In '.$moduleName.'</div>
                    </div>
                    <button class="view-lesson" onclick="showSubOverlay(\'showLessonOverlay\', \'moduleOverlay\')">
                      <img src="images/Search_Icon.jpg" alt="View Lesson" class="search-image-icon">
                    </Button>
                </div> 
                ';
            }

            echo'
                    </div>
                </div>
              </div>
            </div>
            ';
            
        }

        if($action === 'deleteModule'){
            $data = $input['data'];
            $moduleID = $data['moduleID'] ?? null;

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

?>