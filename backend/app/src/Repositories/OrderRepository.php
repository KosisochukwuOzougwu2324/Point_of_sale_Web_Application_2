<?php

namespace App\Repositories;

use App\Models\Order;
use App\Framework\Repository;

class OrderRepository extends Repository implements IOrderRepository
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getAll(?string $status = null, int $offset = 0, int $limit = 50): array
    {
        $sql = "SELECT o.*, u.username AS customer_name, d.username AS driver_name
                FROM orders o
                JOIN users u ON u.id = o.customer_id
                LEFT JOIN users d ON d.id = o.driver_id
                WHERE 1=1";
        $params = [];

        if ($status) {
            $sql .= ' AND o.order_status = :status';
            $params[':status'] = $status;
        }

        $sql .= ' ORDER BY o.created_at DESC LIMIT :limit OFFSET :offset';

        $stmt = $this->getConnection()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?Order
    {
        $stmt = $this->getConnection()->prepare(
            "SELECT o.*, u.username AS customer_name, d.username AS driver_name
             FROM orders o
             JOIN users u ON u.id = o.customer_id
             LEFT JOIN users d ON d.id = o.driver_id
             WHERE o.id = :id"
        );
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) return null;

        $order = new Order();
        foreach ($row as $key => $value) {
            if (property_exists($order, $key)) {
                $order->$key = $value;
            }
        }

        $itemStmt = $this->getConnection()->prepare(
            "SELECT oi.*, p.name AS product_name, p.image_url
             FROM order_items oi
             JOIN products p ON p.id = oi.product_id
             WHERE oi.order_id = :orderId"
        );
        $itemStmt->bindParam(':orderId', $id);
        $itemStmt->execute();
        $order->items = $itemStmt->fetchAll(\PDO::FETCH_ASSOC);

        return $order;
    }

    public function getByCustomerId(int $customerId): array
    {
        $stmt = $this->getConnection()->prepare(
            "SELECT o.*, u.username AS customer_name
             FROM orders o
             JOIN users u ON u.id = o.customer_id
             WHERE o.customer_id = :customerId
             ORDER BY o.created_at DESC"
        );
        $stmt->bindParam(':customerId', $customerId);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function create(Order $order, array $items): Order
    {
        $this->getConnection()->beginTransaction();

        try {
            $totalAmount = 0;
            foreach ($items as $item) {
                $totalAmount += ($item['price'] * $item['quantity']);
            }
            $order->total_amount = $totalAmount;

            $stmt = $this->getConnection()->prepare(
                "INSERT INTO orders (customer_id, total_amount, delivery_method, delivery_address, delivery_phone,
                 payment_method, payment_status, stripe_payment_id, order_status)
                 VALUES (:customer_id, :total_amount, :delivery_method, :delivery_address, :delivery_phone,
                 :payment_method, :payment_status, :stripe_payment_id, :order_status)"
            );

            $stmt->bindParam(':customer_id', $order->customer_id);
            $stmt->bindParam(':total_amount', $order->total_amount);
            $stmt->bindParam(':delivery_method', $order->delivery_method);
            $stmt->bindParam(':delivery_address', $order->delivery_address);
            $stmt->bindParam(':delivery_phone', $order->delivery_phone);
            $stmt->bindParam(':payment_method', $order->payment_method);
            $stmt->bindParam(':payment_status', $order->payment_status);
            $stmt->bindParam(':stripe_payment_id', $order->stripe_payment_id);
            $stmt->bindParam(':order_status', $order->order_status);
            $stmt->execute();

            $order->id = (int) $this->getConnection()->lastInsertId();

            $stmtItem = $this->getConnection()->prepare(
                'INSERT INTO order_items (order_id, product_id, price, quantity) VALUES (:orderId, :productId, :price, :qty)'
            );

            foreach ($items as $item) {
                $productId = (int) $item['id'];
                $price = (float) $item['price'];
                $qty = (int) $item['quantity'];

                $checkStmt = $this->getConnection()->prepare(
                    'SELECT quantity, name FROM products WHERE id = :productId FOR UPDATE'
                );
                $checkStmt->bindParam(':productId', $productId);
                $checkStmt->execute();
                $product = $checkStmt->fetch(\PDO::FETCH_ASSOC);

                if (!$product || $product['quantity'] < $qty) {
                    $this->getConnection()->rollBack();
                    throw new \Exception('Insufficient stock for ' . ($product['name'] ?? 'unknown product'));
                }

              
                $stmtItem->bindParam(':orderId', $order->id);
                $stmtItem->bindParam(':productId', $productId);
                $stmtItem->bindParam(':price', $price);
                $stmtItem->bindParam(':qty', $qty);
                $stmtItem->execute();

                $stockStmt = $this->getConnection()->prepare(
                    'UPDATE products SET quantity = quantity - :quantity WHERE id = :productId'
                );
                $stockStmt->bindParam(':quantity', $qty);
                $stockStmt->bindParam(':productId', $productId);
                $stockStmt->execute();
            }

            $this->getConnection()->commit();
            return $order;

        } catch (\Exception $e) {
            if ($this->getConnection()->inTransaction()) {
                $this->getConnection()->rollBack();
            }
            throw $e;
        }
    }

    public function updateStatus(int $id, string $status): void
    {
        $stmt = $this->getConnection()->prepare('UPDATE orders SET order_status = :status WHERE id = :id');
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }

    public function assignDriver(int $orderId, int $driverId): void
    {
        $stmt = $this->getConnection()->prepare('UPDATE orders SET driver_id = :driverId WHERE id = :id');
        $stmt->bindParam(':driverId', $driverId);
        $stmt->bindParam(':id', $orderId);
        $stmt->execute();
    }

    public function updatePaymentStatus(int $id, string $status, ?string $stripePaymentId = null): void
    {
        $stmt = $this->getConnection()->prepare(
            'UPDATE orders SET payment_status = :status, stripe_payment_id = :stripeId WHERE id = :id'
        );
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':stripeId', $stripePaymentId);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }
}
