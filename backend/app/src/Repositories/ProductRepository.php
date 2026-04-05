<?php

namespace App\Repositories;

use App\Models\Product;
use App\Framework\Repository;

class ProductRepository extends Repository implements IProductRepository
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getAll(?string $category = null, ?string $search = null, int $offset = 0, int $limit = 50): array
    {
        $sql = 'SELECT id, name, description, price, quantity, product_code, image_url, category FROM products WHERE 1=1';
        $params = [];

        if ($category) {
            $sql .= ' AND category = :category';
            $params[':category'] = $category;
        }

        if ($search) {
            $sql .= ' AND (name LIKE :search OR description LIKE :search2)';
            $params[':search'] = "%$search%";
            $params[':search2'] = "%$search%";
        }

        $sql .= ' ORDER BY name ASC LIMIT :limit OFFSET :offset';

        $stmt = $this->getConnection()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_CLASS, Product::class);
    }

    public function countAll(?string $category = null, ?string $search = null): int
    {
        $sql = 'SELECT COUNT(*) FROM products WHERE 1=1';
        $params = [];

        if ($category) {
            $sql .= ' AND category = :category';
            $params[':category'] = $category;
        }

        if ($search) {
            $sql .= ' AND (name LIKE :search OR description LIKE :search2)';
            $params[':search'] = "%$search%";
            $params[':search2'] = "%$search%";
        }

        $stmt = $this->getConnection()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function getById(int $id): ?Product
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT id, name, description, price, quantity, product_code, image_url, category FROM products WHERE id = :id'
        );
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $product = $stmt->fetchObject(Product::class);
        return $product === false ? null : $product;
    }

    public function getByCode(string $code): ?Product
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT id, name, description, price, quantity, product_code, image_url, category FROM products WHERE product_code = :code'
        );
        $stmt->bindParam(':code', $code);
        $stmt->execute();
        $product = $stmt->fetchObject(Product::class);
        return $product === false ? null : $product;
    }

    public function create(Product $product): Product
    {
        $existing = $this->getByCode($product->product_code);
        if ($existing) {
            throw new \Exception('Product code already exists');
        }

        $stmt = $this->getConnection()->prepare(
            'INSERT INTO products (name, description, price, quantity, product_code, image_url, category)
             VALUES (:name, :description, :price, :quantity, :code, :image_url, :category)'
        );

        $stmt->bindParam(':name', $product->name);
        $stmt->bindParam(':description', $product->description);
        $stmt->bindParam(':price', $product->price);
        $stmt->bindParam(':quantity', $product->quantity);
        $stmt->bindParam(':code', $product->product_code);
        $stmt->bindParam(':image_url', $product->image_url);
        $stmt->bindParam(':category', $product->category);
        $stmt->execute();

        $product->id = (int) $this->getConnection()->lastInsertId();
        return $product;
    }

    public function update(Product $product): void
    {
        $stmt = $this->getConnection()->prepare(
            'UPDATE products SET name = :name, description = :description, price = :price, 
             quantity = :quantity, image_url = :image_url, category = :category WHERE id = :id'
        );

        $stmt->bindParam(':name', $product->name);
        $stmt->bindParam(':description', $product->description);
        $stmt->bindParam(':price', $product->price);
        $stmt->bindParam(':quantity', $product->quantity);
        $stmt->bindParam(':image_url', $product->image_url);
        $stmt->bindParam(':category', $product->category);
        $stmt->bindParam(':id', $product->id);
        $stmt->execute();
    }

    public function delete(int $id): void
    {
        $stmt = $this->getConnection()->prepare('DELETE FROM products WHERE id = :id');
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }

    public function getCategories(): array
    {
        $stmt = $this->getConnection()->query(
            'SELECT DISTINCT category FROM products WHERE category IS NOT NULL ORDER BY category'
        );
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function getTopFive(): array
    {
        $sql = "SELECT p.id, p.name, SUM(si.quantity) AS quantity
                FROM sale_items si
                JOIN products p ON p.id = si.product_id
                GROUP BY p.id, p.name
                ORDER BY quantity DESC
                LIMIT 5";

        $stmt = $this->getConnection()->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getDashboardSummary(): array
    {
        $sql = "SELECT
            COALESCE(SUM(CASE WHEN DATE(created_at) = CURDATE() THEN total_amount ELSE 0 END), 0) AS todayTotal,
            COALESCE(SUM(CASE WHEN YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1) THEN total_amount ELSE 0 END), 0) AS weekTotal,
            COALESCE(SUM(CASE WHEN YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE()) THEN total_amount ELSE 0 END), 0) AS monthTotal,
            COALESCE(SUM(CASE WHEN YEAR(created_at) = YEAR(CURDATE()) THEN total_amount ELSE 0 END), 0) AS yearTotal
        FROM sales";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getTodaysSales(): array
    {
        $sql = "SELECT s.id AS saleId, p.name AS product, si.price, si.quantity,
                (si.price * si.quantity) AS amount
                FROM sales s
                JOIN sale_items si ON si.sale_id = s.id
                JOIN products p ON p.id = si.product_id
                WHERE DATE(s.created_at) = CURDATE()
                ORDER BY s.id DESC";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function completeSale(array $items, int $userId): int
    {
        $this->getConnection()->beginTransaction();

        try {
            $totalAmount = 0;
            foreach ($items as $item) {
                $totalAmount += ($item['price'] * $item['quantity']);
            }

            $stmtSale = $this->getConnection()->prepare(
                'INSERT INTO sales (user_id, total_amount) VALUES (:userId, :totalAmount)'
            );
            $stmtSale->bindParam(':userId', $userId);
            $stmtSale->bindParam(':totalAmount', $totalAmount);
            $stmtSale->execute();

            $saleId = (int) $this->getConnection()->lastInsertId();

            $stmtItem = $this->getConnection()->prepare(
                'INSERT INTO sale_items (sale_id, product_id, price, quantity) VALUES (:saleId, :productId, :price, :qty)'
            );

            $stmtStock = $this->getConnection()->prepare(
                'UPDATE products SET quantity = quantity - :quantity WHERE id = :productId AND quantity >= :quantity2'
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

                $stmtItem->bindParam(':saleId', $saleId);
                $stmtItem->bindParam(':productId', $productId);
                $stmtItem->bindParam(':price', $price);
                $stmtItem->bindParam(':qty', $qty);
                $stmtItem->execute();

                $stmtStock->bindParam(':quantity', $qty);
                $stmtStock->bindParam(':productId', $productId);
                $stmtStock->bindParam(':quantity2', $qty);
                $stmtStock->execute();
            }

            $this->getConnection()->commit();
            return $saleId;

        } catch (\Exception $e) {
            if ($this->getConnection()->inTransaction()) {
                $this->getConnection()->rollBack();
            }
            throw $e;
        }
    }
}
