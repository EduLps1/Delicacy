<?php
/**
 * DELICACY - Admin Contratante Dashboard Router
 */

require_once __DIR__ . '/../../config/config.php';
require_once SRC_PATH . '/controllers/AdminContratanteController.php';

$controller = new AdminContratanteController();
$controller->showDashboard();
