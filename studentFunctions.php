<?php
    include_once 'database.php';
    include_once 'authFunctions.php';
    ob_start();
    session_start();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Decode the JSON payload
        $input = json_decode(file_get_contents('php://input'), true);
    
        if (isset($input['action']) && $input['action'] === 'joinClassroom') {
            $data = $input['data'];
    
            // Extract variables from the data
            $classroomID = $data['classid'] ?? null;
            $studentID = $_SESSION['studentID'] ?? null;
            $userID = $_SESSION['userID'] ?? null;

            // Validate the data
            if (!$classroomID || !$studentID || !$userID) {
                echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
                exit;
            }

            // Check if classroom exists
            $checkClassSql = "SELECT * FROM classroom WHERE classroomID = ?";
            $checkClassStmt = $conn->prepare($checkClassSql);
            $checkClassStmt->bind_param('s', $classroomID);
            $checkClassStmt->execute();
            $classResult = $checkClassStmt->get_result();

            if ($classResult->num_rows === 0) {
                echo json_encode(['success' => false, 'message' => 'Classroom does not exist.']);
                exit;
            }

            // Check if student is already enrolled
            $checkEnrollSql = "SELECT * FROM enrolledstudent WHERE studentID = ? AND classroomID = ?";
            $checkEnrollStmt = $conn->prepare($checkEnrollSql);
            $checkEnrollStmt->bind_param('ss', $studentID, $classroomID);
            $checkEnrollStmt->execute();
            $enrollResult = $checkEnrollStmt->get_result();

            if ($enrollResult->num_rows > 0) {
                echo json_encode(['success' => false, 'message' => 'You are already enrolled in this classroom.']);
                exit;
            }

            // Generate a new enrolledID
            $enrolledID = generateID('ES', 8);

            // SQL statement to insert into enrolledstudent table
            $sql = "INSERT INTO enrolledstudent (enrolledID, studentID, classroomID) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                echo json_encode(['success' => false, 'message' => 'SQL prepare error: ' . $conn->error]);
                exit;
            }

            $stmt->bind_param('sss', $enrolledID, $studentID, $classroomID);

            if ($stmt->execute()) {
                logAction($conn, $userID, 'Joined classroom with ID: ' . $classroomID);
                echo json_encode(['success' => true, 'message' => 'Successfully joined classroom!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'SQL execution error: ' . $stmt->error]);
            }
            exit;
        }
    }

    // Return error if no valid action specified
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
?>