<?php
require_once '../../middleware/Class/AdminAuth.php';

use Middleware\Class\AdminAuth;

$adminAuth = new AdminAuth();

if (!$adminAuth->isLoggedIn()) {
    header('Location: ../../landing-page/index.php');
    exit();
}
?>