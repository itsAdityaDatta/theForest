<?php
session_start();
$_SESSION['user_id'] = NULL;
session_destroy();
header("location:index.php");
?>