<?php

namespace App\Tests;

use App\Entity\Product;
use App\Entity\User;
use App\Model\ProductModel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ProductTest extends KernelTestCase
{
    private ProductModel $productModel;
    private EntityManagerInterface $entityManager;


    protected function setUp() : void{
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        $this->productModel = new ProductModel($this->entityManager);
    }


    public function testAddProduct(): void
    {
        $faker = \Faker\Factory::create();
        $name = $faker->text();

        $product = new Product();
        $product->setName($name);
        $product->setPrice($faker->numberBetween(0,100));
        $product->setStock($faker->numberBetween(0,100));
        $product->setCreatedBy(current($this->entityManager->getRepository(User::class)->findAll()));

        $this->productModel->save($product);
        $addedProduct = $this->entityManager->getRepository(Product::class)->findOneBy(array('name' => $name));

        $this->assertNotNull($addedProduct);
    }
}
