<?php
// Fix session by logging out and forcing re-login
session_start();
session_destroy();
header('Location: login.php');
exit;
?>
