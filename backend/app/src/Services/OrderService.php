<?php

namespace App\Services;

use App\Models\Order;
use App\Repositories\IOrderRepository;
use App\Repositories\OrderRepository;

class OrderService implements IOrderService
{
    private IOrderRepository $orderRepository;

    public function __construct()
    {
        $this->orderRepository = new OrderRepository();
    }

    public function getAll(?string $status = null, int $offset = 0, int $limit = 50): array
    {
        return $this->orderRepository->getAll($status, $offset, $limit);
    }

    public function getById(int $id): ?Order
    {
        return $this->orderRepository->getById($id);
    }

    public function getByCustomerId(int $customerId): array
    {
        return $this->orderRepository->getByCustomerId($customerId);
    }

    public function create(Order $order, array $items): Order
    {
        return $this->orderRepository->create($order, $items);
    }

    public function updateStatus(int $id, string $status): void
    {
        $this->orderRepository->updateStatus($id, $status);
    }

    public function assignDriver(int $orderId, int $driverId): void
    {
        $this->orderRepository->assignDriver($orderId, $driverId);
    }

    public function updatePaymentStatus(int $id, string $status, ?string $stripePaymentId = null): void
    {
        $this->orderRepository->updatePaymentStatus($id, $status, $stripePaymentId);
    }
}
