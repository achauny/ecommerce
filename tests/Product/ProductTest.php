<?php

namespace Product;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\User;
use App\Model\ObjectModel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ProductTest extends KernelTestCase
{
    private ObjectModel $objectModel;
    private EntityManagerInterface $entityManager;


    protected function setUp() : void{
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        $this->objectModel = new ObjectModel($this->entityManager);
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
        $product->setDescription($faker->text(100));
        $product->setCategory(current($this->entityManager->getRepository(Category::class)->findAll()));

        $this->objectModel->save($product);
        $addedProduct = $this->entityManager->getRepository(Product::class)->findOneBy(array('name' => $name));

        $this->assertNotNull($addedProduct);
    }
}
