<?php
/**
 * DELICACY - Admin Contratante Edit Restaurant Router
 */

require_once __DIR__ . '/../../config/config.php';
require_once SRC_PATH . '/controllers/AdminContratanteController.php';

$controller = new AdminContratanteController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->updateRestaurant();
} else {
    $controller->showEditRestaurantForm();
}
