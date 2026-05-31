<?php
// logout.php
require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();
session_unset();
session_destroy();

header("Location: login.php?msg=logged_out");
exit();
?>