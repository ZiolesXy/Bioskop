<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
} else {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: user/dashboard.php");
    }
}
?>