<?php
function redirect($url) {
    header("Location: " . $url);
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return $_SESSION['role'] === 'admin';
}
?>