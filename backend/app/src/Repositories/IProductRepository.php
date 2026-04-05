<?php

namespace App\Repositories;

use App\Models\Product;

interface IProductRepository
{
    public function getAll(?string $category, ?string $search, int $offset, int $limit): array;
    public function countAll(?string $category, ?string $search): int;
    public function getById(int $id): ?Product;
    public function getByCode(string $code): ?Product;
    public function create(Product $product): Product;
    public function update(Product $product): void;
    public function delete(int $id): void;
    public function getCategories(): array;
    public function getTopFive(): array;
    public function getDashboardSummary(): array;
    public function getTodaysSales(): array;
    public function completeSale(array $items, int $userId): int;
}
