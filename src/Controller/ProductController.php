<?php

namespace App\Controller;

use App\Entity\Product;
use App\Model\ProductModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    public function __construct(private readonly ProductModel $productModel){
    }

    #[Route('/produits', name: 'app_products')]
    public function index(): Response
    {
        return $this->render('product/index.html.twig');
    }

    #[Route('/produits/ajouter', name: 'app_products_add')]
    public function add(): Response
    {
        $product = new Product();
        $product->setName("Test");
        $product->setPrice(30.0);
        $product->setStock(10);

        $this->productModel->save($product);

        return $this->redirectToRoute('app_products');
    }
}
