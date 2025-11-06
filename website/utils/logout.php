<?php
require_once __DIR__ . '/paths.php';
require_once UTILS_DIR . '/auth.php';

session_destroy();
header("Location: ../login/");
exit;
