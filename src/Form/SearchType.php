<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('search', TextType::class, [
            'label' => false,
            'attr' => [
                'class' => 'form-control',
                'id'=>'inputsearch',
                'placeholder' => 'Rechercher un motif'
            ]
        ])
        ->add('rechercher', SubmitType::class, [
            'attr' => [
                'class' => 'btn btn-warning',
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
