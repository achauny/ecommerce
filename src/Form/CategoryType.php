<?php

namespace App\Form;

use App\Entity\Category;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de la catégorie',
                'required' => true,
            ])
            ->add('priority', NumberType::class, [
                'label' => 'Priorité',
                'required' => true,
            ])
            ->add('level', NumberType::class, [
                'label' => 'Niveau',
                'required' => true,
            ])
            ->add('parentCategory',EntityType::class, [
                'class' => Category::class,
                'label' => 'Catégorie parente',
                'required' => true,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->where('c.enable = true')
                        ->orderBy('c.priority', 'ASC');
                },
                'group_by' => function($category) {
                    return $category->getParentCategory() ? $category->getParentCategory()->getName() : 'Sans parent';
                },
                'choice_label' => function($category) {
                    return $category->getName();
                },
                'empty_data' => null,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
        ]);
    }
}
