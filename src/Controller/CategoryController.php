<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Model\CategoryModel;
use App\Service\CategoryService;
use App\Utils\FormUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CategoryController extends AbstractController
{
    public function __construct(private readonly CategoryModel $categoryModel, private readonly CategoryService $categoryService, private readonly EntityManagerInterface $entityManager, private readonly UrlGeneratorInterface $urlGenerator){
    }

    #[Route('/categories', name: 'app_categories')]
    public function index(): Response
    {
        return $this->render('category/index.html.twig', array(
            "listCategories" => $this->entityManager->getRepository(Category::class)->findAll(),
        ));
    }

    #[Route('/categories/ajouter', name: 'app_categories_add')]
    public function add(Request $request): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);

        $redirect = FormUtils::validateForm($form, [
            "request" => $request,
            "modelClass" => $this->categoryModel,
            "flashbagSuccess" => "Catégorie ajoutée avec succès.",
            "redirectSuccess" => $this->urlGenerator->generate("app_categories"),
        ]);

        return (!is_null($redirect)) ? $redirect : $this->render('category/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/categories/{id}/modifier/', name: 'app_categories_edit', options: ['methodApply' => 'verifyCreatedBy', 'redirectRoute' => 'app_categories'])]
    #[IsGranted('edit', 'category')] // On met le type de vérification et le type d'objet
    public function edit(Request $request, Category $category): Response
    {
        $form = $this->createForm(CategoryType::class, $category);

        $redirect = FormUtils::validateForm($form, [
            "request" => $request,
            "modelClass" => $this->categoryModel,
            "flashbagSuccess" => "Catégorie modifiée avec succès.",
            "redirectSuccess" => $this->urlGenerator->generate("app_categories"),
        ]);

        return (!is_null($redirect)) ? $redirect : $this->render('category/form.html.twig', [
            'form' => $form->createView(),
            'category' => $category
        ]);
    }
}
