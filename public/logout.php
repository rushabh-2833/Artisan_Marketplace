<?php
session_start();

// Optionally, save session cart items to the database if needed here

// Clear session data
session_unset();
session_destroy();

// Redirect to login page
header("Location: login.php");
exit;
?>
