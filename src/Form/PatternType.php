<?php

namespace App\Form;

use App\Entity\Pattern;
use App\Form\ApplicationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PatternType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, $this->getConfiguration("Titre", "Titre du motif"))
            ->add('theme', ChoiceType::class,[
                'choices'=>[
                    'Graphique'=>"Graphique",
                    'Animaux'=>'Animaux',
                    'Nature'=>'Nature',
                    'Occasions'=>"Occasions",
                    "Géographie"=>"Géographie",
                    "Abstrait"=>"Abstrait",
                    "Textures"=>"Textures",
                    "Projets divers"=>"Projets divers"
                ]
            ])
            ->add('dominantColor',ChoiceType::class,[
                'choices'=> [
                    'Rouge'=>'Rouge',
                    'Bleu'=>'Bleu',
                    'Vert'=>'Vert',
                    'Rose'=>'Rose',
                    'Violet'=>'Violet',
                    'Jaune'=>'Jaune',
                    'Orange'=>'Orange',
                    'Blanc'=>'Blanc',
                    'Noir'=>'Noir',
                    'Brun'=>'Brun',
                    'Ecru'=>"Ecru"
                ]
            ] )
            ->add('description', TextType::class, $this->getConfiguration("Description","Ecrivez ici une petite description de votre motif"))
            ->add('cover', FileType::class, [
                "data_class" =>  null,
                "label"=> "Image de la recette(jpg, jpeg, png)",
                
            ])
            ->add('license',ChoiceType::class, [
                'choices'=>[
                    'COMMERCIALE'=>"COMMERCIALE",
                    'PRIVÉE'=>"PRIVÉE",
                    'PROTÉGÉE'=>"PROTÉGÉE"
                ]
            ])
            ->add('submit', SubmitType::class,['label' => 'Enregistrer'],
            [
                'attr' => ['class' => 'btn'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Pattern::class,
            // 'validation_groups' => 'front'
        ]);
    }
}
