<?php
/**
 * DELICACY - Admin Delicacy Users Router
 */

require_once __DIR__ . '/../../config/config.php';
require_once SRC_PATH . '/controllers/AdminDelicacyController.php';

$controller = new AdminDelicacyController();
$controller->listUsers();