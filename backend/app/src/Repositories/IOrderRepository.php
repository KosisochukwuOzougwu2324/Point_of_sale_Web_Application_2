<?php

namespace App\Repositories;

use App\Models\Order;

interface IOrderRepository
{
    public function getAll(?string $status, int $offset, int $limit): array;
    public function getById(int $id): ?Order;
    public function getByCustomerId(int $customerId): array;
    public function create(Order $order, array $items): Order;
    public function updateStatus(int $id, string $status): void;
    public function assignDriver(int $orderId, int $driverId): void;
    public function updatePaymentStatus(int $id, string $status, ?string $stripePaymentId): void;
}
