<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class ProfileUpdateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('email',EmailType::class,[
            'attr' => ['class' => 'form-control'],
        ])
        ->add('lastname',TextType::class,[
            'attr' => ['class' => 'form-control'],
        ])
        ->add('firstname',TextType::class,[
            'attr' => ['class' => 'form-control'],
        ])
        ->add('phone',TextType::class,[
            'attr' => ['class' => 'form-control'],
        ])
        ->add('zipcode',TextType::class,[
            'attr' => ['class' => 'form-control'],
        ])
        ->add('address',TextType::class ,[
            'attr' => ['class' => 'form-control'],
        ])
        ->add('city',TextType::class,[
            'attr' => ['class' => 'form-control'],
        ]) ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
        ]);
    }
}
