<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom produit',
                'required' => true,
            ])
            ->add('price',NumberType::class, [
                'label' => 'Prix',
                'required' => true,
            ])
            ->add('stock',NumberType::class, [
                'label' => 'Quantité',
                'required' => true,
            ])
            ->add('category',EntityType::class, [
                'class' => Category::class,
                'label' => 'Catégorie',
                'required' => true,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.priority', 'ASC');
                },
                'group_by' => function($category) {
                    return $category->getParentCategory() ? $category->getParentCategory()->getName() : 'Sans parent';
                },
                'choice_label' => function($category) {
                    return $category->getName();
                }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
