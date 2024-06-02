<?php

namespace App\Model;

use Doctrine\ORM\EntityManagerInterface;
use Exception;

readonly class ObjectModel
{
    public function __construct(private EntityManagerInterface $entityManager){
    }

    public function add(object $object): void {
        $this->save($object);
    }

    public function save(object $object): void{
        try{
            if($object->getId() === null){
                $this->entityManager->persist($object);
            }
            $this->entityManager->flush();
        } catch (Exception $e){
            throw new \RuntimeException($e->getMessage());
        }
    }

    public function remove(object $object): void{
        try{
            $this->entityManager->remove($object);
            $this->entityManager->flush();
        } catch (Exception $e){
            throw new \RuntimeException($e->getMessage());
        }
    }
}