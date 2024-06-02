<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartProduct;
use App\Entity\Product;
use App\Model\ObjectModel;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CartController extends AbstractController
{
    public function __construct(private readonly CartService $cartService) {

    }

    #[Route('/panier', name: 'app_cart')]
    public function index(): Response
    {
        $cart = $this->getUser()->getCart();

        return $this->render('cart/index.html.twig', [
            'cart'  => $cart,
            'total' => $this->cartService->calculateTotalPriceOfCart(cart: $cart),
        ]);
    }

    #[Route('/panier/produit/{id}/ajouter/', name: 'app_cart_product_order')]
    public function orderProduct(RequestStack $request, Product $product): Response
    {
        $cart = $this->getUser()->getCart() ?? new Cart();
        $this->cartService->orderProduct($request, $cart, $product);

        // Pour éviter de passer un paramètre vide
        $params = (isset($_GET['idCategory'])) ? array("idCategory" => $_GET['idCategory']) : array();
        return $this->redirectToRoute('app_products', $params);
    }

    #[Route('/panier/{id}/produit/supprimer', name: 'app_cart_product_delete')]
    public function deleteProduct(RequestStack $request, CartProduct $cartProduct): Response
    {
        $this->cartService->deleteProduct($request, $cartProduct);

        return $this->redirectToRoute('app_cart');
    }
}
