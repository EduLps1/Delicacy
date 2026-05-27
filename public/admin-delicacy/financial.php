<?php
/**
 * DELICACY - Admin Delicacy Financial Router
 */

require_once __DIR__ . '/../../config/config.php';
require_once SRC_PATH . '/controllers/AdminDelicacyController.php';

$controller = new AdminDelicacyController();
$controller->showFinancial();
