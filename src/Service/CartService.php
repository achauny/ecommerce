<?php

namespace App\Service;


use App\Entity\Cart;
use App\Entity\CartProduct;
use App\Entity\Product;
use App\Model\ObjectModel;
use Symfony\Component\HttpFoundation\RequestStack;

readonly class CartService {
    public function __construct(private ObjectModel $objectModel) {
    }

    public function orderProduct(RequestStack $request, Cart $cart, Product $product): void
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

    public function calculateTotalPriceOfCart(Cart $cart): float
    {
        $total = 0;

        foreach ($cart->getCartProducts() as $cartProduct) {
            $total += $cartProduct->getProduct()?->getPrice() * $cartProduct->getQuantity();
        }

        return round($total, 2);
    }

    public function deleteProduct(RequestStack $request, CartProduct $cartProduct): void
    {
        $product = $cartProduct->getProduct();
        $product?->setStock($product?->getStock() + $cartProduct->getQuantity());
        $request->getSession()->getFlashBag()->add('success', 'Produit supprimé du panier avec succès.');

        $this->objectModel->remove($cartProduct);
    }
}