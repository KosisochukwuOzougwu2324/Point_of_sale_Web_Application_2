<?php

namespace App\Controllers;

use App\Framework\Controller;
use App\Middleware\AuthMiddleware;
use App\Models\Product;
use App\Services\IProductService;
use App\Services\ProductService;

class ProductController extends Controller
{
    private IProductService $productService;

    public function __construct()
    {
        $this->productService = new ProductService();
    }

    public function getAll()
    {
        $category = $_GET['category'] ?? null;
        $search = $_GET['search'] ?? null;
        $offset = (int)($_GET['offset'] ?? 0);
        $limit = (int)($_GET['limit'] ?? 50);

        $products = $this->productService->getAll($category, $search, $offset, $limit);
        $total = $this->productService->countAll($category, $search);

        return $this->sendSuccessResponse([
            'products' => $products,
            'total' => $total,
            'offset' => $offset,
            'limit' => $limit
        ]);
    }

    public function getCategories()
    {
        $categories = $this->productService->getCategories();
        return $this->sendSuccessResponse(['categories' => $categories]);
    }


    public function getById($vars = [])
    {
        $id = (int)($vars['id'] ?? 0);
        $product = $this->productService->getById($id);

        if (!$product) {
            return $this->sendErrorResponse('Product not found', 404);
        }

        return $this->sendSuccessResponse(['product' => $product]);
    }

    
    public function getByCode($vars = [])
    {
        AuthMiddleware::requireAuth();

        $code = $vars['code'] ?? '';
        $product = $this->productService->getByCode($code);

        if (!$product) {
            return $this->sendErrorResponse("Product with code $code not found", 404);
        }

        return $this->sendSuccessResponse(['product' => $product]);
    }


    public function create()
    {
        AuthMiddleware::requireAnyRole(['Admin', 'Editor']);

        $data = $this->getRequestBody();

        if (!$data || empty($data['name']) || empty($data['product_code'])) {
            return $this->sendErrorResponse('Name and product code are required', 400);
        }

        try {
            $product = new Product();
            $product->name = trim($data['name']);
            $product->description = $data['description'] ?? '';
            $product->price = (float)($data['price'] ?? 0);
            $product->quantity = (int)($data['quantity'] ?? 0);
            $product->product_code = trim($data['product_code']);
            $product->image_url = $data['image_url'] ?? null;
            $product->category = $data['category'] ?? null;

            $createdProduct = $this->productService->create($product);

            return $this->sendSuccessResponse([
                'message' => 'Product successfully created',
                'product' => $createdProduct
            ], 201);

        } catch (\Exception $e) {
            return $this->sendErrorResponse($e->getMessage(), 400);
        }
    }

    public function update($vars = [])
    {
        AuthMiddleware::requireAnyRole(['Admin', 'Editor']);

        $id = (int)($vars['id'] ?? 0);
        $data = $this->getRequestBody();

        $existing = $this->productService->getById($id);
        if (!$existing) {
            return $this->sendErrorResponse('Product not found', 404);
        }

        $product = new Product();
        $product->id = $id;
        $product->name = trim($data['name'] ?? $existing->name);
        $product->description = $data['description'] ?? $existing->description;
        $product->price = (float)($data['price'] ?? $existing->price);
        $product->quantity = (int)($data['quantity'] ?? $existing->quantity);
        $product->product_code = $existing->product_code; // Code cannot change
        $product->image_url = $data['image_url'] ?? $existing->image_url;
        $product->category = $data['category'] ?? $existing->category;

        $this->productService->update($product);

        return $this->sendSuccessResponse([
            'message' => 'Product successfully updated',
            'product' => $product
        ]);
    }

    public function delete($vars = [])
    {
        AuthMiddleware::requireRole('Admin');

        $id = (int)($vars['id'] ?? 0);

        $existing = $this->productService->getById($id);
        if (!$existing) {
            return $this->sendErrorResponse('Product not found', 404);
        }

        $this->productService->delete($id);
        return $this->sendSuccessResponse(['message' => 'Product successfully deleted']);
    }

    public function completeSale()
    {
        $authUser = AuthMiddleware::requireAuth();

        $data = $this->getRequestBody();

        if (!$data || empty($data['items']) || !is_array($data['items'])) {
            return $this->sendErrorResponse('No items provided', 400);
        }

        try {
            $saleId = $this->productService->completeSale($data['items'], $authUser->id);
            return $this->sendSuccessResponse([
                'message' => 'Sale completed successfully',
                'sale_id' => $saleId
            ], 201);
        } catch (\Exception $e) {
            return $this->sendErrorResponse($e->getMessage(), 400);
        }
    }

    public function getTopFive()
    {
        AuthMiddleware::requireRole('Admin');

        $products = $this->productService->getTopFive();
        return $this->sendSuccessResponse(['products' => $products]);
    }

    
    public function getTodaysSales()
    {
        AuthMiddleware::requireAuth();

        $sales = $this->productService->getTodaysSales();
        return $this->sendSuccessResponse(['todaySales' => $sales]);
    }

    public function getDashboardSummary()
    {
        AuthMiddleware::requireRole('Admin');

        $summary = $this->productService->getDashboardSummary();
        return $this->sendSuccessResponse(['summary' => $summary]);
    }
}
