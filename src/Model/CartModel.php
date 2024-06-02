<?php

namespace App\Model;

use App\Entity\Cart;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

readonly class CartModel
{
    public function __construct(private EntityManagerInterface $entityManager){
    }

    public function add(Cart $cart): void {
        $this->save($cart);
    }

    public function save(Cart $cart): void{
        try{
            if($cart->getId() === null){
                $this->entityManager->persist($cart);
            }
            $this->entityManager->flush();
        } catch (Exception $e){
            throw new \RuntimeException($e->getMessage());
        }
    }

    public function remove(Cart $cart): void{
        try{
            $this->entityManager->remove($cart);
            $this->entityManager->flush();
        } catch (Exception $e){
            throw new \RuntimeException($e->getMessage());
        }
    }
}