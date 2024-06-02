<?php

namespace App\Service;


use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;

readonly class CategoryService {
    public function __construct(private readonly EntityManagerInterface $entityManager) {
    }

    public function buildTree(): array{
        $listCategoriesLevel1 = $this->entityManager->getRepository(Category::class)->findBy(array('enable' => true, 'level' => 1), array('priority' => 'ASC'));
        $tree = array();

        foreach ($listCategoriesLevel1 as $categoryLevel1) {
            $tree[$categoryLevel1->getName()] = array();
            $children = $categoryLevel1->getCategories(true);

            if( count($children) > 0){
                $this->buildChildren($tree[$categoryLevel1->getName()], $children);
            }
        }

        return $tree;
    }

    public function buildChildren(&$tree, $children): void{
        foreach ($children as $child) {
            $childData[$child->getName()][] = array();

            if(count($children) > 0){
                $this->buildChildren($childData[$child->getName()], $child->getCategories(true));
            }
            $tree = $childData;
        }
    }
}