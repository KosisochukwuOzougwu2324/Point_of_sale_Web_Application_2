<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\IProductRepository;
use App\Repositories\ProductRepository;

class ProductService implements IProductService
{
    private IProductRepository $productRepository;

    public function __construct()
    {
        $this->productRepository = new ProductRepository();
    }

    public function getAll(?string $category = null, ?string $search = null, int $offset = 0, int $limit = 50): array
    {
        return $this->productRepository->getAll($category, $search, $offset, $limit);
    }

    public function countAll(?string $category = null, ?string $search = null): int
    {
        return $this->productRepository->countAll($category, $search);
    }

    public function getById(int $id): ?Product
    {
        return $this->productRepository->getById($id);
    }

    public function getByCode(string $code): ?Product
    {
        return $this->productRepository->getByCode($code);
    }

    public function create(Product $product): Product
    {
        return $this->productRepository->create($product);
    }

    public function update(Product $product): void
    {
        $this->productRepository->update($product);
    }

    public function delete(int $id): void
    {
        $this->productRepository->delete($id);
    }

    public function getCategories(): array
    {
        return $this->productRepository->getCategories();
    }

    public function getTopFive(): array
    {
        return $this->productRepository->getTopFive();
    }

    public function getDashboardSummary(): array
    {
        return $this->productRepository->getDashboardSummary();
    }

    public function getTodaysSales(): array
    {
        return $this->productRepository->getTodaysSales();
    }

    public function completeSale(array $items, int $userId): int
    {
        return $this->productRepository->completeSale($items, $userId);
    }
}
