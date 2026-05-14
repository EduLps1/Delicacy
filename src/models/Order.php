<?php
/**
 * DELICACY - Order Model
 *
 * Gerencia operações com pedidos no banco de dados.
 */

require_once __DIR__ . '/../../config/database.php';

class Order
{
    /**
     * Encontra pedido por ID
     */
    public function findById($id)
    {
        $query = "SELECT o.*, m.name as menu_name, m.restaurant_id,
                         r.name as restaurant_name
                  FROM orders o
                  LEFT JOIN menus m ON o.menu_id = m.id
                  LEFT JOIN restaurants r ON m.restaurant_id = r.id
                  WHERE o.id = ?";
        return Database::getInstance()->fetchOne($query, [$id], 'i');
    }

    /**
     * Lista pedidos por restaurante com filtros
     */
    public function findByRestaurantId($restaurantId, $filters = [], $limit = 50, $offset = 0)
    {
        $query = "SELECT o.*, m.name as menu_name
                  FROM orders o
                  INNER JOIN menus m ON o.menu_id = m.id
                  WHERE m.restaurant_id = ?";
        $params = [$restaurantId];
        $types = 'i';

        if (!empty($filters['status'])) {
            $query .= " AND o.status = ?";
            $params[] = $filters['status'];
            $types .= 's';
        }

        if (!empty($filters['date_from'])) {
            $query .= " AND o.created_at >= ?";
            $params[] = $filters['date_from'] . ' 00:00:00';
            $types .= 's';
        }

        if (!empty($filters['date_to'])) {
            $query .= " AND o.created_at <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
            $types .= 's';
        }

        $query .= " ORDER BY o.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';

        return Database::getInstance()->fetchAll($query, $params, $types);
    }

    /**
     * Conta pedidos por restaurante
     */
    public function countByRestaurantId($restaurantId, $filters = [])
    {
        $query = "SELECT COUNT(*) as total
                  FROM orders o
                  INNER JOIN menus m ON o.menu_id = m.id
                  WHERE m.restaurant_id = ?";
        $params = [$restaurantId];
        $types = 'i';

        if (!empty($filters['status'])) {
            $query .= " AND o.status = ?";
            $params[] = $filters['status'];
            $types .= 's';
        }

        if (!empty($filters['date_from'])) {
            $query .= " AND o.created_at >= ?";
            $params[] = $filters['date_from'] . ' 00:00:00';
            $types .= 's';
        }

        if (!empty($filters['date_to'])) {
            $query .= " AND o.created_at <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
            $types .= 's';
        }

        $result = Database::getInstance()->fetchOne($query, $params, $types);
        return (int)($result['total'] ?? 0);
    }

    /**
     * Cria novo pedido com itens
     */
    public function create($data, $items)
    {
        $db = Database::getInstance();

        try {
            $db->beginTransaction();

            // Calcula total
            $totalValue = 0;
            foreach ($items as $item) {
                $totalValue += (float)$item['price'] * (int)$item['quantity'];
            }

            // Insere pedido
            $query = "INSERT INTO orders (menu_id, customer_id, customer_name, customer_phone, 
                        delivery_address, total_value, status)
                      VALUES (?, ?, ?, ?, ?, ?, 'pending')";

            $stmt = $db->execute(
                $query,
                [
                    $data['menu_id'],
                    $data['customer_id'] ?? null,
                    $data['customer_name'] ?? null,
                    $data['customer_phone'] ?? null,
                    $data['delivery_address'] ?? null,
                    $totalValue
                ],
                'iisssd'
            );

            if ($stmt === false) {
                throw new Exception('Erro ao criar pedido');
            }

            $orderId = $db->lastInsertId();

            // Insere itens do pedido
            $itemQuery = "INSERT INTO order_items (order_id, menu_item_id, quantity, price_at_moment, notes)
                          VALUES (?, ?, ?, ?, ?)";

            foreach ($items as $item) {
                $db->execute(
                    $itemQuery,
                    [
                        $orderId,
                        $item['menu_item_id'],
                        (int)$item['quantity'],
                        (float)$item['price'],
                        $item['notes'] ?? null
                    ],
                    'iiids'
                );
            }

            $db->commit();
            return $orderId;

        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    /**
     * Atualiza status do pedido
     */
    public function updateStatus($orderId, $newStatus)
    {
        $validStatuses = [
            ORDER_STATUS_PENDING,
            ORDER_STATUS_CONFIRMED,
            ORDER_STATUS_PREPARING,
            ORDER_STATUS_READY,
            ORDER_STATUS_DELIVERED,
            ORDER_STATUS_CANCELLED
        ];

        if (!in_array($newStatus, $validStatuses)) {
            throw new Exception('Status inválido');
        }

        $stmt = Database::getInstance()->execute(
            "UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?",
            [$newStatus, $orderId],
            'si'
        );

        return $stmt !== false;
    }

    /**
     * Obtém itens de um pedido
     */
    public function getOrderItems($orderId)
    {
        $query = "SELECT oi.*, mi.name, mi.image_url
                  FROM order_items oi
                  LEFT JOIN menu_items mi ON oi.menu_item_id = mi.id
                  WHERE oi.order_id = ?";
        return Database::getInstance()->fetchAll($query, [$orderId], 'i');
    }

    /**
     * Estatísticas rápidas de pedidos para dashboard
     */
    public function getStats($restaurantId)
    {
        $stats = [];

        // Pedidos pendentes
        $result = Database::getInstance()->fetchOne(
            "SELECT COUNT(*) as total FROM orders o 
             INNER JOIN menus m ON o.menu_id = m.id 
             WHERE m.restaurant_id = ? AND o.status = 'pending'",
            [$restaurantId],
            'i'
        );
        $stats['pending'] = (int)($result['total'] ?? 0);

        // Pedidos hoje
        $result = Database::getInstance()->fetchOne(
            "SELECT COUNT(*) as total, COALESCE(SUM(o.total_value), 0) as revenue 
             FROM orders o 
             INNER JOIN menus m ON o.menu_id = m.id 
             WHERE m.restaurant_id = ? AND DATE(o.created_at) = CURDATE()",
            [$restaurantId],
            'i'
        );
        $stats['today_orders'] = (int)($result['total'] ?? 0);
        $stats['today_revenue'] = (float)($result['revenue'] ?? 0);

        // Total geral
        $result = Database::getInstance()->fetchOne(
            "SELECT COUNT(*) as total, COALESCE(SUM(o.total_value), 0) as revenue 
             FROM orders o 
             INNER JOIN menus m ON o.menu_id = m.id 
             WHERE m.restaurant_id = ?",
            [$restaurantId],
            'i'
        );
        $stats['total_orders'] = (int)($result['total'] ?? 0);
        $stats['total_revenue'] = (float)($result['revenue'] ?? 0);

        return $stats;
    }

    /**
     * Resumo financeiro e operacional por periodo.
     */
    public function getSummaryByRestaurantId($restaurantId, $days = 30)
    {
        $days = max(1, (int)$days);
        $startDate = date('Y-m-d 00:00:00', strtotime('-' . ($days - 1) . ' days'));

        $result = Database::getInstance()->fetchOne(
            "SELECT COUNT(*) as total_orders, COALESCE(SUM(o.total_value), 0) as revenue
             FROM orders o
             INNER JOIN menus m ON o.menu_id = m.id
             WHERE m.restaurant_id = ?
               AND o.created_at >= ?",
            [$restaurantId, $startDate],
            'is'
        );

        return [
            'total_orders' => (int)($result['total_orders'] ?? 0),
            'revenue' => (float)($result['revenue'] ?? 0)
        ];
    }

    /**
     * Serie diaria de faturamento e pedidos para graficos.
     */
    public function getDailySeriesByRestaurantId($restaurantId, $days = 30)
    {
        $days = max(1, min(90, (int)$days));
        $startDate = date('Y-m-d 00:00:00', strtotime('-' . ($days - 1) . ' days'));

        $rows = Database::getInstance()->fetchAll(
            "SELECT DATE(o.created_at) as order_date,
                    COUNT(*) as total_orders,
                    COALESCE(SUM(o.total_value), 0) as revenue
             FROM orders o
             INNER JOIN menus m ON o.menu_id = m.id
             WHERE m.restaurant_id = ?
               AND o.created_at >= ?
             GROUP BY DATE(o.created_at)",
            [$restaurantId, $startDate],
            'is'
        );

        $byDate = [];
        foreach ($rows as $row) {
            $byDate[$row['order_date']] = [
                'orders' => (int)($row['total_orders'] ?? 0),
                'revenue' => (float)($row['revenue'] ?? 0)
            ];
        }

        $series = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $series[] = [
                'day' => (int)date('j', strtotime($date)),
                'date' => $date,
                'orders' => $byDate[$date]['orders'] ?? 0,
                'revenue' => $byDate[$date]['revenue'] ?? 0.0
            ];
        }

        return $series;
    }
}
