<?php

namespace App\Form;

use App\Entity\Mark;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class MarkType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('mark', ChoiceType::class, [
                'choices' => [
                    '1 / 5' => 1,
                    '2 / 5' => 2,
                    '3 / 5' => 3,
                    '4 / 5' => 4,
                    '5 / 5' => 5,
                ],
                'attr' => [
                    'class' => 'form-select'
                ],
                'label' => 'Noter la recette de 1 à 5',
                'label_attr' => [
                    'class' => 'form-label mt-4 texte'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-warning mt-4'
                ],
                'label' => 'Noter la recette'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Mark::class,
        ]);
    }
}
