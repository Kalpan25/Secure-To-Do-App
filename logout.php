<?php
require_once 'config.php';
require_once 'security.php';

session_unset();
session_destroy();

header('Location: index.php');
exit();
?>
