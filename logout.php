<?php
ob_start();
session_start();

require_once 'authFunctions.php';

destroySession();
header("Location: index.php");
exit();

?>