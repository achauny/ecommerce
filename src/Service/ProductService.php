<?php

namespace App\Service;


use App\Entity\Product;
use App\Model\ProductModel;
use Symfony\Component\HttpFoundation\RequestStack;

readonly class ProductService
{
    public function __construct(private ProductModel $productModel)
    {
    }

    public function addView(Product $product): void
    {
        $product->setNumberView($product->getNumberView() + 1);
        $this->productModel->save($product);
    }

    public function order(RequestStack $request, Product $product): void
    {
        if ($product->getStock() > 0) {
            $product->setStock($product->getStock() - 1);
            $this->productModel->save($product);
            $request->getSession()->getFlashBag()->add('success', 'Produit commandé avec succès.');
        }
    }
}