<?php

namespace App\Models;

class Order implements \JsonSerializable
{
    public ?int $id = null;
    public int $customer_id = 0;
    public ?int $driver_id = null;
    public float $total_amount = 0;
    public string $delivery_method = 'Pickup';
    public ?string $delivery_address = null;
    public ?string $delivery_phone = null;
    public string $payment_method = 'Pay at Pickup';
    public string $payment_status = 'Pending';
    public ?string $stripe_payment_id = null;
    public string $order_status = 'Pending';
    public ?string $created_at = null;
    public ?string $updated_at = null;

    public array $items = [];

  
    public ?string $customer_name = null;
    public ?string $driver_name = null;

    public function jsonSerialize(): array
    {
        $data = [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'driver_id' => $this->driver_id,
            'total_amount' => $this->total_amount,
            'delivery_method' => $this->delivery_method,
            'delivery_address' => $this->delivery_address,
            'delivery_phone' => $this->delivery_phone,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'stripe_payment_id' => $this->stripe_payment_id,
            'order_status' => $this->order_status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];

        if ($this->customer_name) $data['customer_name'] = $this->customer_name;
        if ($this->driver_name) $data['driver_name'] = $this->driver_name;
        if (!empty($this->items)) $data['items'] = $this->items;

        return $data;
    }
}
