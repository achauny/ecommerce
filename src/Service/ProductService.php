<?php

namespace App\Service;


use App\Entity\Product;
use App\Model\ProductModel;

readonly class ProductService {
    public function __construct(private ProductModel $productModel) {
    }

    public function addView(Product $product): void
    {
        $product->setNumberView($product->getNumberView() + 1);
        $this->productModel->save($product);
    }
}