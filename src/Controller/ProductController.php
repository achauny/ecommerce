<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartProduct;
use App\Entity\Product;
use App\Form\ProductType;
use App\Model\ProductModel;
use App\Service\CategoryService;
use App\Service\ProductService;
use App\Utils\FormUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProductController extends AbstractController
{
    public function __construct(private readonly ProductModel $productModel, private readonly ProductService $productService, private readonly  CategoryService $categoryService, private readonly EntityManagerInterface $entityManager, private readonly UrlGeneratorInterface $urlGenerator){
    }

    #[Route('/produits', name: 'app_products')]
    public function index(): Response
    {
        $treeCategories = $this->categoryService->buildTree();

        return $this->render('product/index.html.twig', array(
            "listProducts" => (isset($_GET['idCategory']) && $_GET['idCategory'] !== "")
                ? $this->entityManager->getRepository(Product::class)->findBy(
                    ['category' => array_merge([$_GET['idCategory']], $this->categoryService->getCategoryChildrenIds($treeCategories, $_GET['idCategory']))],
                    ['id' => 'ASC']
                )
                : $this->entityManager->getRepository(Product::class)->findBy(array(), ['id' => 'ASC']),
            "treeCategories" => $treeCategories,
            "idCategory" => $_GET['idCategory'] ?? ""
        ));
    }

    #[Route('/produits/ajouter', name: 'app_products_add')]
    public function add(Request $request): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);

        $redirect = FormUtils::validateForm($form, [
            "request"         => $request,
            "modelClass"      => $this->productModel,
            "flashbagSuccess" => "Produit ajouté avec succès.",
            "redirectSuccess" => $this->urlGenerator->generate("app_products"),
        ]);

        return (!is_null($redirect)) ? $redirect : $this->render('product/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/produits/{id}/modifier/', name: 'app_products_edit', options: ['methodApply' => 'verifyCreatedBy', 'redirectRoute' => 'app_products'])]
    #[IsGranted('edit', 'product')] // On met le type de vérification et le type d'objet
    public function edit(Request $request, Product $product): Response
    {
        $form = $this->createForm(ProductType::class, $product);

        $redirect = FormUtils::validateForm($form, [
            "request" => $request,
            "modelClass" => $this->productModel,
            "flashbagSuccess" => "Produit modifié avec succès.",
            "redirectSuccess" => $this->urlGenerator->generate("app_products"),
        ]);

        return (!is_null($redirect)) ? $redirect : $this->render('product/form.html.twig', [
            'form' => $form->createView(),
            'product' => $product
        ]);
    }

    #[Route('/produits/{id}/fiche/', name: 'app_products_view')]
    public function view(Product $product): Response
    {
        $this->productService->addView($product);

        return $this->render('product/view.html.twig', [
            'product' => $product
        ]);
    }

    #[Route('/produits/{id}/commander/', name: 'app_products_order')]
    public function order(RequestStack $request, Product $product): Response
    {
        $cart = $this->entityManager->getRepository(Cart::class)->findOneBy(['createdBy' => $this->getUser()]) ?? new Cart();
        $this->productService->order($request, $cart, $product);

        // Pour éviter de passer un paramètre vide
        $params = (isset($_GET['idCategory'])) ? array("idCategory" => $_GET['idCategory']) : array();
        return $this->redirectToRoute('app_products', $params);
    }
}
