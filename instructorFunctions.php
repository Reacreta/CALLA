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


          if ($action === 'getModuleDetails') {
            $data = $input['data'];
            $moduleID = $data['moduleID'] ?? null;
        
            if (!$moduleID) {
                echo json_encode(['success' => false, 'message' => 'Module ID is required.']);
                exit;
            }
        
            // Fetch module details
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
        
            if (!$row) {
                echo json_encode(['success' => false, 'message' => 'Module not found.']);
                exit;
            }
        
            $moduleName = $row['moduleName'];
            $moduleDesc = $row['moduleDesc'];
            $className = $row['className'];
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
                'lessons' => $lessons
            ]);
            exit;
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
    }

?>