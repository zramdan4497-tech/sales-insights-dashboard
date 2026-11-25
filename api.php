<?php
// JSON API for the dashboard.
// Usage: api.php?action=kpis|monthly|categories|top_customers|cities&from=YYYY-MM-DD&to=YYYY-MM-DD&city=...
require __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf8');

$action = $_GET['action'] ?? '';
$from   = $_GET['from']   ?? '2000-01-01';
$to     = $_GET['to']     ?? '2100-01-01';
$city   = $_GET['city']   ?? '';

// Shared filter: completed orders inside the selected date range (and city, if chosen).
$where  = "o.status = 'completed' AND o.order_date BETWEEN :from AND :to";
$params = [':from' => $from, ':to' => $to];
if ($city !== '') {
    $where .= ' AND c.city = :city';
    $params[':city'] = $city;
}

function run(string $sql, array $params): array {
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

switch ($action) {
    case 'kpis':
        $rows = run(
            "SELECT COUNT(DISTINCT o.order_id)               AS orders,
                    ROUND(SUM(oi.quantity * oi.unit_price))  AS revenue
             FROM orders o
             JOIN customers c    ON c.customer_id = o.customer_id
             JOIN order_items oi ON oi.order_id   = o.order_id
             WHERE $where",
            $params
        );
        $avg = run(
            "SELECT ROUND(AVG(t.order_total)) AS avg_order_value
             FROM (
                 SELECT o.order_id, SUM(oi.quantity * oi.unit_price) AS order_total
                 FROM orders o
                 JOIN customers c    ON c.customer_id = o.customer_id
                 JOIN order_items oi ON oi.order_id   = o.order_id
                 WHERE $where
                 GROUP BY o.order_id
             ) t",
            $params
        );
        $out = $rows[0] ?? ['orders' => 0, 'revenue' => 0];
        $out['avg_order_value'] = $avg[0]['avg_order_value'] ?? 0;
        echo json_encode($out);
        break;

    case 'monthly':
        echo json_encode(run(
            "SELECT DATE_FORMAT(o.order_date, '%Y-%m')      AS month,
                    ROUND(SUM(oi.quantity * oi.unit_price)) AS revenue
             FROM orders o
             JOIN customers c    ON c.customer_id = o.customer_id
             JOIN order_items oi ON oi.order_id   = o.order_id
             WHERE $where
             GROUP BY month
             ORDER BY month",
            $params
        ));
        break;

    case 'categories':
        echo json_encode(run(
            "SELECT p.category,
                    ROUND(SUM(oi.quantity * oi.unit_price)) AS revenue
             FROM order_items oi
             JOIN products  p ON p.product_id  = oi.product_id
             JOIN orders    o ON o.order_id    = oi.order_id
             JOIN customers c ON c.customer_id = o.customer_id
             WHERE $where
             GROUP BY p.category
             ORDER BY revenue DESC",
            $params
        ));
        break;

    case 'top_customers':
        echo json_encode(run(
            "SELECT c.full_name AS customer, c.city,
                    COUNT(DISTINCT o.order_id)              AS orders,
                    ROUND(SUM(oi.quantity * oi.unit_price)) AS revenue
             FROM orders o
             JOIN customers c    ON c.customer_id = o.customer_id
             JOIN order_items oi ON oi.order_id   = o.order_id
             WHERE $where
             GROUP BY c.customer_id, c.full_name, c.city
             ORDER BY revenue DESC
             LIMIT 10",
            $params
        ));
        break;

    case 'cities':
        echo json_encode(run('SELECT DISTINCT city FROM customers ORDER BY city', []));
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Unknown action']);
}
