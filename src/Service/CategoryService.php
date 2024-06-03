<?php

namespace App\Service;


use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;

readonly class CategoryService {
    public function __construct(private EntityManagerInterface $entityManager) {
    }

    public function buildTree(): array {
        $listCategoriesLevel1 = $this->entityManager->getRepository(Category::class)->findBy(
            ['enable' => true, 'level' => 1],
            ['priority' => 'ASC']
        );
        $tree = [];

        foreach ($listCategoriesLevel1 as $categoryLevel1) {
            $tree[$categoryLevel1->getId()] = ['name' => $categoryLevel1->getName(), 'children' => []];
            $children = $categoryLevel1->getCategories(true);

            if (count($children) > 0) {
                $this->buildChildren($tree[$categoryLevel1->getId()]['children'], $children);
            }
        }

        return $tree;
    }

    public function buildChildren(&$tree, $children): void {
        foreach ($children as $child) {
            $tree[$child->getId()] = ['name' => $child->getName(), 'children' => []];

            if (count($child->getCategories(true)) > 0) {
                $this->buildChildren($tree[$child->getId()]['children'], $child->getCategories(true));
            }
        }
    }

    public function getCategoryChildrenIds(array $tree, int $categoryId): ?array {
        $category = $this->findCategoryById($tree, $categoryId);
        if ($category !== null) {
            $childrenIds = [];
            $this->accumulateCategoryChildrenIds($category, $childrenIds);
            return $childrenIds;
        }
        return null;
    }

    private function accumulateCategoryChildrenIds(array $category, array &$ids): void {
        foreach ($category['children'] as $childId => $child) {
            $ids[] = $childId;
            if (!empty($child['children'])) {
                $this->accumulateCategoryChildrenIds($child, $ids);
            }
        }
    }

    private function findCategoryById(array $tree, int $id): ?array {
        foreach ($tree as $key => $value) {
            if ($key === $id) {
                return $value;
            }
            if (isset($value['children'])) {
                $result = $this->findCategoryById($value['children'], $id);
                if ($result !== null) {
                    return $result;
                }
            }
        }
        return null;
    }
}