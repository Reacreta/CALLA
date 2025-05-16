<?php
require_once 'database.php';
header('Content-Type: application/json');

if (!isset($_POST['userID'])) {
    echo json_encode(['error' => 'No user ID provided']);
    exit;
}

$userID = $conn->real_escape_string($_POST['userID']);

// Query to fetch logs from the database
$sql = "SELECT u.username, l.action, l.timestamp 
        FROM user_logs l
        JOIN users u ON l.userID = u.userID
        WHERE l.userID = ?
        ORDER BY l.timestamp DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $userID);
$stmt->execute();
$result = $stmt->get_result();

$logs = [];
while ($row = $result->fetch_assoc()) {
    $logs[] = [
        'username' => $row['username'],
        'action' => $row['action'],
        'timestamp' => $row['timestamp']
    ];
}

echo json_encode($logs);
$stmt->close();
?> 