<?php

namespace App\Models;

class Product implements \JsonSerializable
{
    public ?int $id = null;
    public string $name = '';
    public ?string $description = null;
    public float $price = 0;
    public int $quantity = 0;
    public string $product_code = '';
    public ?string $image_url = null;
    public ?string $category = null;

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'product_code' => $this->product_code,
            'image_url' => $this->image_url,
            'category' => $this->category
        ];
    }
}
