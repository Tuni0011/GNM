<?php

namespace App\Form;

use App\Entity\Shop;
use App\Entity\Products;
use App\Entity\SubCategories;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Image;

class ProductsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add ('shop',  EntityType::class,[
            'class' => Shop::class,
            'choice_label' => 'name',
        ])
            ->add('name')
            ->add('description')
            ->add('price', MoneyType::class,[
                'currency' => 'DTN',
                'divisor'=> 1000,
            ])
            ->add('stock')
          
            ->add('subcategories', EntityType::class, [
                'class' => SubCategories::class,
                'choice_label' => 'name',
            ])
            ->add('images', FileType::class, [
                'label'=> false,
                'multiple' =>true,
                'mapped' => false,
                'required' => false,
                'constraints' =>[
                    new All(
                         new Image
                            ([
                            'maxWidth' => 1280,
                            'maxWidthMessage' => 'Width maximal is 1280',
                           
                            ]) 
                        )
                
                ]


            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Products::class,
        ]);
    }
}
