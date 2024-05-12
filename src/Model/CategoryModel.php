<?php

namespace App\Model;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;

readonly class CategoryModel
{
    public function __construct(private EntityManagerInterface $entityManager){
    }

    public function add(Category $category): void {
        $this->save($category);
    }

    public function save(Category $category): void{
        try{
            if($category->getId() === null){
                $this->entityManager->persist($category);
            }
            $this->entityManager->flush();
        } catch (Exception $e){
            throw new \RuntimeException($e->getMessage());
        }
    }

    public function remove(Category $category): void{
        try{
            $this->entityManager->remove($category);
            $this->entityManager->flush();
        } catch (Exception $e){
            throw new \RuntimeException($e->getMessage());
        }
    }
}