<?php
include __DIR__ . '/../includes/config.php';
session_start();
session_destroy();
header('Location: ' . BASE_URL . '/controllers/login.php');
exit();
?>