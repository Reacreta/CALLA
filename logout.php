<?php
ob_start();
session_start();
session_unset();  // Unset all session variables
session_destroy();  // Destroy the session

// Redirect to login page (change path as needed)
header("Location: index.php");
exit();
?>