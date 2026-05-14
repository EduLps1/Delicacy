<?php
/**
 * DELICACY - Admin Delicacy Restaurants Router
 */

require_once __DIR__ . '/../../config/config.php';
require_once SRC_PATH . '/controllers/AdminDelicacyController.php';

$controller = new AdminDelicacyController();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'update_status' && isset($_POST['restaurant_id'])) {
        $controller->updateRestaurantStatus($_POST['restaurant_id']);
    }
} else {
    // GET - Lista restaurantes
    $controller->listRestaurants();
}