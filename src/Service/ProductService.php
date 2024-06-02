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
    public function __construct(private ObjectModel $objectModel, private EntityManagerInterface $entityManager)
    {
    }

    public function addView(Product $product): void
    {
        $product->setNumberView($product->getNumberView() + 1);
        $this->objectModel->save($product);
    }

    public function order(RequestStack $request, Cart $cart, Product $product): void
    {
        if ($product->getStock() > 0) {
            $cartProduct = $cart->haveCartProduct($product);

            if(is_null($cartProduct)){
                $cartProduct = new CartProduct();
                $cartProduct->setProduct($product);
                $cartProduct->setQuantity(1);
            }

            $cart->addCartProduct($cartProduct);
            $product->setStock($product->getStock() - 1);

            $this->objectModel->save($cart);

            $request->getSession()->getFlashBag()->add('success', 'Produit ajouté au panier avec succès.');
        }
    }
}