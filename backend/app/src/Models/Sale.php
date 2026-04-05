<?php

namespace App\Models;

class Sale implements \JsonSerializable
{
    public ?int $id = null;
    public int $user_id = 0;
    public float $total_amount = 0;
    public ?string $created_at = null;

    public array $items = [];

    public function jsonSerialize(): array
    {
        $data = [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'total_amount' => $this->total_amount,
            'created_at' => $this->created_at
        ];

        if (!empty($this->items)) $data['items'] = $this->items;

        return $data;
    }
}
