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
                  echo json_encode(['success' => true]);
              } else {
                  echo json_encode(['success' => false, 'message' => 'SQL execution error: ' . $stmt->error]);
              }
              exit; // Stop further execution
          }
      }
?>