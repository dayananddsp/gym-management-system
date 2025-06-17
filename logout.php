<?php
session_start();
session_unset();
session_destroy();

// Remove cookies
setcookie("client_id", "", time() - 3600, "/");
setcookie("role", "", time() - 3600, "/");

header("Location: login.php");
exit;
