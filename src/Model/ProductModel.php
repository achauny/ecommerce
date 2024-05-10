<?php

namespace App\Model;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

readonly class ProductModel
{
    public function __construct(private EntityManagerInterface $entityManager){
    }

    public function add(Product $product): void {
        $this->save($product);
    }

    public function save(Product $product): void{
        try{
            $this->entityManager->persist($product);
            $this->entityManager->flush();
        } catch (Exception $e){
            throw new \RuntimeException($e->getMessage());
        }
    }

    public function remove(Product $product): void{
        try{
            $this->entityManager->remove($product);
            $this->entityManager->flush();
        } catch (Exception $e){
            throw new \RuntimeException($e->getMessage());
        }
    }
}