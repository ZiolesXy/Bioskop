<?php
session_start();
include 'function.php';

// Destroy the session
session_unset();
session_destroy();

// Redirect to the login page
redirect('auth/login.php');
?>
