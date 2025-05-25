<?php
    include_once 'database.php';
    include_once 'authFunctions.php';
    ob_start();
    session_start();

    // Helper function for consistent JSON responses
    function sendJsonResponse($success, $message = '', $data = []) {
        echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
        exit;
    }

    // Handler for Ajax Requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Decode the JSON payload
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? null;

        if (!$action) {
            sendJsonResponse(false, 'No action specified');
        }


             if ($action === 'joinClassroom') {
              $data = $input['data'];
    
              // Extract variables from the data
              $creator = $data['creator'] ?? null;
              $id = $data['classid'] ?? null;
    
              $enrolledID = generateID('CI',8);
              $roleID = $_SESSION['roleID'];
    
              // Validate the data
              if (!$id) {
                  echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
                  exit; // Stop further execution
              }
    
                // Insert enrollment
                $sql = "INSERT INTO enrolledstudent (enrolledID, studentID, classroomID) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
    
              if (!$stmt) {
                  echo json_encode(['success' => false, 'message' => 'SQL prepare error: ' . $conn->error]);
                  exit; // Stop further execution
              }
    
              $stmt->bind_param('sss', $enrolledID, $roleID, $id);
    
              if ($stmt->execute()) {
                    logAction($conn, $_SESSION['userID'], 'Joined classroom with ID: ' . $id);
                  echo json_encode(['success' => true]);
              } else {
                  echo json_encode(['success' => false, 'message' => 'SQL execution error: ' . $stmt->error]);
              }
              exit; // Stop further execution
          }


        if ($action === 'getClassroomDetails') {
            $data = $input['data'] ?? [];
            $classroomID = $data['classID'] ?? null;
            $studentID = $_SESSION['roleID'] ?? 'TEST_STUDENT_ID';

            if (!$classroomID) {
                sendJsonResponse(false, 'Missing classroom ID');
            }

            try {
                // Fetch classroom details with primary instructor
                $sql = "SELECT c.classroomID, c.className, c.classDesc, c.classCode, c.dateCreated,
                               u.firstName, u.lastName, u.username as creatorName, u.email as creatorEmail
                        FROM classroom c 
                        JOIN instructor i ON c.instID = i.instID 
                        JOIN users u ON i.userID = u.userID 
                        WHERE c.classroomID = ?";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception('Failed to prepare classroom query: ' . $conn->error);
                }

                $stmt->bind_param('s', $classroomID);
                $stmt->execute();
                $result = $stmt->get_result();
                $classroomDetails = $result->fetch_assoc();

                if (!$classroomDetails) {
                    sendJsonResponse(false, 'Classroom not found');
                }

                // Fetch all instructors (including additional instructors)
                $sql = "SELECT DISTINCT u.firstName, u.lastName, u.username, u.email
                        FROM classinstructor ci 
                        JOIN instructor i ON ci.instID = i.instID 
                        JOIN users u ON i.userID = u.userID 
                        WHERE ci.classroomID = ?
                        UNION
                        SELECT u.firstName, u.lastName, u.username, u.email
                        FROM classroom c
                        JOIN instructor i ON c.instID = i.instID
                        JOIN users u ON i.userID = u.userID
                        WHERE c.classroomID = ?";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception('Failed to prepare instructors query: ' . $conn->error);
                }

                $stmt->bind_param('ss', $classroomID, $classroomID);
                $stmt->execute();
                $result = $stmt->get_result();
                $instructors = [];
                while ($row = $result->fetch_assoc()) {
                    $instructors[] = $row;
                }

                // ✅ Fixed: Fetch ALL students INCLUDING the current one
                $sql = "SELECT u.firstName, u.lastName, u.username
                        FROM enrolledstudent es 
                        JOIN student s ON es.studentID = s.studentID 
                        JOIN users u ON s.userID = u.userID 
                        WHERE es.classroomID = ?
                        ORDER BY u.firstName, u.lastName";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception('Failed to prepare students query: ' . $conn->error);
                }

                $stmt->bind_param('s', $classroomID);
                $stmt->execute();
                $result = $stmt->get_result();
                $students = [];
                while ($row = $result->fetch_assoc()) {
                    $students[] = $row;
                }

                // Fetch modules available in this classroom
                $sql = "SELECT DISTINCT lm.langID, lm.moduleName, lm.moduleDesc, lm.dateCreated
                        FROM classmodule cm 
                        JOIN classinstructor ci ON cm.classInstID = ci.classInstID 
                        JOIN languagemodule lm ON cm.langID = lm.langID
                        WHERE ci.classroomID = ?
                        ORDER BY lm.dateCreated DESC";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception('Failed to prepare modules query: ' . $conn->error);
                }

                $stmt->bind_param('s', $classroomID);
                $stmt->execute();
                $result = $stmt->get_result();
                $modules = [];
                while ($row = $result->fetch_assoc()) {
                    $row['progress'] = 0; // Default progress
                    $modules[] = $row;
                }

                sendJsonResponse(true, 'Data retrieved successfully', [
                    'classroomDetails' => $classroomDetails,
                    'instructors' => $instructors,
                    'students' => $students,
                    'modules' => $modules
                ]);

            } catch (Exception $e) {
                error_log("Database error in getClassroomDetails: " . $e->getMessage());
                sendJsonResponse(false, 'Database error occurred');
            }
        }

        if ($action === 'getModuleDetails') {
            $data = $input['data'] ?? [];
            $moduleID = $data['moduleID'] ?? null;
            // Use a default student ID for testing when session isn't available
            $studentID = $_SESSION['roleID'] ?? 'TEST_STUDENT_ID';

            if (!$moduleID) {
                sendJsonResponse(false, 'Module ID is required');
            }

            try {
                // Fetch module details without access validation
                $sql = "SELECT langID, moduleName, moduleDesc, dateCreated
                        FROM languagemodule 
                        WHERE langID = ?";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception('Failed to prepare module query: ' . $conn->error);
                }
                
                $stmt->bind_param('s', $moduleID);
                $stmt->execute();
                $result = $stmt->get_result();
                $moduleData = $result->fetch_assoc();

                if (!$moduleData) {
                    sendJsonResponse(false, 'Module not found');
                }

                // Fetch lessons for the module
                $sql = "SELECT lessID, lessonName, lessonDesc, dateCreated 
                        FROM lesson 
                        WHERE langID = ? 
                        ORDER BY lessonName ASC";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception('Failed to prepare lessons query: ' . $conn->error);
                }
                
                $stmt->bind_param('s', $moduleID);
                $stmt->execute();
                $result = $stmt->get_result();

                $lessons = [];
                while ($row = $result->fetch_assoc()) {
                    $lessons[] = $row;
                }

                // Set default progress to 0 (removed progress checking)
                $progress = 0;

                sendJsonResponse(true, 'Module details retrieved successfully', [
                    'moduleName' => $moduleData['moduleName'],
                    'moduleDesc' => $moduleData['moduleDesc'],
                    'dateCreated' => $moduleData['dateCreated'],
                    'className' => 'Available Module', // Default since we removed classroom validation
                    'progress' => $progress,
                    'lessons' => $lessons
                ]);

            } catch (Exception $e) {
                error_log("Error in getModuleDetails: " . $e->getMessage());
                sendJsonResponse(false, 'An error occurred while retrieving module details');
            }
        }

    // In studentFunctions.php, modify the getLessonDetails section
    if ($action === 'getLessonDetails') {
        $data = $input['data'] ?? [];
        $lessonID = $data['lessonID'] ?? null;

        if (!$lessonID) {
            sendJsonResponse(false, 'Lesson ID is required');
        }

        try {
            // Fetch lesson details
            $sql = "SELECT l.lessID, l.lessonName, l.lessonDesc, l.dateCreated, 
                        lm.moduleName, lm.langID
                    FROM lesson l
                    JOIN languagemodule lm ON l.langID = lm.langID
                    WHERE l.lessID = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception('Failed to prepare lesson query: ' . $conn->error);
            }
            
            $stmt->bind_param('s', $lessonID);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $lesson = $result->fetch_assoc();
            
            if (!$lesson) {
                sendJsonResponse(false, 'Lesson not found');
            }

            // Fetch vocabulary for the lesson
            $sql = "SELECT wordID, word, meaning FROM vocabulary WHERE lessID = ? ORDER BY word ASC";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception('Failed to prepare vocabulary query: ' . $conn->error);
            }
            
            $stmt->bind_param('s', $lessonID);
            $stmt->execute();
            $result = $stmt->get_result();

            $vocabulary = [];
            while ($row = $result->fetch_assoc()) {
                $vocabulary[] = $row;
            }

            sendJsonResponse(true, 'Lesson details retrieved successfully', [
                'success' => true,
                'lesson' => [
                    'lessonName' => $lesson['lessonName'],
                    'lessonDesc' => $lesson['lessonDesc'],
                    'dateCreated' => $lesson['dateCreated'],
                    'moduleName' => $lesson['moduleName']
                ],
                'vocabulary' => $vocabulary
            ]);

        } catch (Exception $e) {
            error_log("Error in getLessonDetails: " . $e->getMessage());
            sendJsonResponse(false, 'An error occurred while retrieving lesson details: ' . $e->getMessage());
        }
    }

    if ($action === 'leaveClass') {
            $classID = trim($input['classID']);
            $accountRole = $_SESSION['accountRole'];
            $userID = $_SESSION['userID'];

            $sql = "DELETE es FROM enrolledstudent es
                    JOIN student s ON es.studentID = s.studentID
                    JOIN users u ON s.userID = u.userID
                    WHERE es.classroomID = ? AND u.userID = ?";
                    
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("ss", $classID, $userID);
                $stmt->execute();
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => $conn->error]);
            }
            exit;
            
        }

    // Handle GET requests for basic data retrieval
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = $_GET['action'] ?? null;
        
    }
}
?>