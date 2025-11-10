<?php
session_start();
$_SESSION = [];
session_unset();
session_destroy();

$logoutRedirectUri = 'http://localhost/quiz-metrix/landing-page/';
$msLogoutUrl = 'https://login.microsoftonline.com/common/oauth2/v2.0/logout?' .
               'post_logout_redirect_uri=' . urlencode($logoutRedirectUri);

header("Location: $msLogoutUrl");
exit();
