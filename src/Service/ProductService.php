<?php

namespace App\Service;


use App\Entity\Cart;
use App\Entity\CartProduct;
use App\Entity\Product;
use App\Model\ObjectModel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

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