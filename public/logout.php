<?php
/**
 * DELICACY - Logout Router
 */

require_once __DIR__ . '/../config/config.php';
require_once SRC_PATH . '/controllers/AuthController.php';

requireAuth();

$controller = new AuthController();
$controller->logout();