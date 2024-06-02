<?php

namespace App\Service;


use App\Entity\Product;
use App\Model\ObjectModel;

readonly class ProductService
{
    public function __construct(private ObjectModel $objectModel)
    {
    }

    public function addView(Product $product): void
    {
        $product->setNumberView($product->getNumberView() + 1);
        $this->objectModel->save($product);
    }
}