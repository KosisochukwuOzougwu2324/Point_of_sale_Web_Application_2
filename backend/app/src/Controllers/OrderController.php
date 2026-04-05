<?php

namespace App\Controllers;

use App\Framework\Controller;
use App\Middleware\AuthMiddleware;
use App\Models\Order;
use App\Services\IOrderService;
use App\Services\OrderService;

class OrderController extends Controller
{
    private IOrderService $orderService;

    public function __construct()
    {
        $this->orderService = new OrderService();
    }

   
    public function getAll()
    {
        AuthMiddleware::requireRole('Admin');

        $status = $_GET['status'] ?? null;
        $offset = (int)($_GET['offset'] ?? 0);
        $limit = (int)($_GET['limit'] ?? 50);

        $orders = $this->orderService->getAll($status, $offset, $limit);
        return $this->sendSuccessResponse(['orders' => $orders]);
    }

    public function getById($vars = [])
    {
        $authUser = AuthMiddleware::requireAuth();

        $id = (int)($vars['id'] ?? 0);
        $order = $this->orderService->getById($id);

        if (!$order) {
            return $this->sendErrorResponse('Order not found', 404);
        }

        if ($authUser->role === 'Customer' && $order->customer_id !== $authUser->id) {
            return $this->sendErrorResponse('Access denied', 403);
        }

        return $this->sendSuccessResponse(['order' => $order]);
    }

    public function getMyOrders()
    {
        $authUser = AuthMiddleware::requireAuth();

        $orders = $this->orderService->getByCustomerId($authUser->id);
        return $this->sendSuccessResponse(['orders' => $orders]);
    }

    public function create()
    {
        $authUser = AuthMiddleware::requireAuth();

        $data = $this->getRequestBody();

        if (!$data || empty($data['items']) || !is_array($data['items'])) {
            return $this->sendErrorResponse('No items provided', 400);
        }

        if (empty($data['delivery_method'])) {
            return $this->sendErrorResponse('Delivery method is required', 400);
        }

        if (empty($data['payment_method'])) {
            return $this->sendErrorResponse('Payment method is required', 400);
        }


        if ($data['delivery_method'] === 'Delivery') {
            if (empty($data['delivery_address'])) {
                return $this->sendErrorResponse('Delivery address is required for delivery orders', 400);
            }
            if (empty($data['delivery_phone'])) {
                return $this->sendErrorResponse('Phone number is required for delivery orders', 400);
            }
        }

        try {
            $order = new Order();
            $order->customer_id = $authUser->id;
            $order->delivery_method = $data['delivery_method'];
            $order->delivery_address = $data['delivery_address'] ?? null;
            $order->delivery_phone = $data['delivery_phone'] ?? null;
            $order->payment_method = $data['payment_method'];
            $order->stripe_payment_id = $data['stripe_payment_id'] ?? null;

            if ($data['payment_method'] === 'Online' && !empty($data['stripe_payment_id'])) {
                $order->payment_status = 'Paid';
            } else {
                $order->payment_status = 'Pending';
            }

            $order->order_status = 'Pending';

            $createdOrder = $this->orderService->create($order, $data['items']);

            return $this->sendSuccessResponse([
                'message' => 'Order placed successfully',
                'order' => $createdOrder
            ], 201);

        } catch (\Exception $e) {
            return $this->sendErrorResponse($e->getMessage(), 400);
        }
    }

    public function updateStatus($vars = [])
    {
        AuthMiddleware::requireAnyRole(['Admin', 'Editor']);

        $id = (int)($vars['id'] ?? 0);
        $data = $this->getRequestBody();

        if (!$data || empty($data['order_status'])) {
            return $this->sendErrorResponse('Order status is required', 400);
        }

        $order = $this->orderService->getById($id);
        if (!$order) {
            return $this->sendErrorResponse('Order not found', 404);
        }

        $this->orderService->updateStatus($id, $data['order_status']);

        return $this->sendSuccessResponse(['message' => 'Order status updated to ' . $data['order_status']]);
    }

    public function assignDriver($vars = [])
    {
        AuthMiddleware::requireRole('Admin');

        $id = (int)($vars['id'] ?? 0);
        $data = $this->getRequestBody();

        if (!$data || empty($data['driver_id'])) {
            return $this->sendErrorResponse('Driver ID is required', 400);
        }

        $order = $this->orderService->getById($id);
        if (!$order) {
            return $this->sendErrorResponse('Order not found', 404);
        }

        $this->orderService->assignDriver($id, (int)$data['driver_id']);

        return $this->sendSuccessResponse(['message' => 'Driver assigned successfully']);
    }
}
