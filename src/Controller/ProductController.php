<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Model\ProductModel;
use App\Utils\FormUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProductController extends AbstractController
{
    public function __construct(private readonly ProductModel $productModel, private readonly EntityManagerInterface $entityManager, private readonly UrlGeneratorInterface $urlGenerator){
    }

    #[Route('/produits', name: 'app_products')]
    public function index(): Response
    {
        return $this->render('product/index.html.twig', array(
            "listProducts" => $this->entityManager->getRepository(Product::class)->findAll(),
        ));
    }

    #[Route('/produits/ajouter', name: 'app_products_add')]
    public function add(Request $request): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);

        $redirect = FormUtils::validateForm($form, [
            "request" => $request,
            "modelClass" => $this->productModel,
            "flashbagSuccess" => "Produit ajouté avec succès.",
            "redirectSuccess" => $this->urlGenerator->generate("app_products"),
        ]);

        return (!is_null($redirect)) ? $redirect : $this->render('product/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
