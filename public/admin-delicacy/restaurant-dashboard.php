<?php
/**
 * DELICACY - Visualizacao administrativa da dashboard de restaurante
 */

require_once __DIR__ . '/../../config/config.php';
require_once SRC_PATH . '/controllers/AdminDelicacyController.php';

$controller = new AdminDelicacyController();
$controller->showRestaurantDashboard((int)($_GET['id'] ?? 0));
