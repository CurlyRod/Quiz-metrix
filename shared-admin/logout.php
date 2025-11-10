<?php
session_start();
$_SESSION = [];
session_unset();
session_destroy();

$logoutRedirectUri = 'http://localhost/quiz-metrix/landing-page/';
header("Location: $logoutRedirectUri");
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
exit();
