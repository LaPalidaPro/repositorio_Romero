<?php

namespace App\Form;

use App\Entity\Artista;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArtistaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre')
            ->add('anoDebut', null, [
                'widget' => 'single_text',
            ])
            ->add('paisOrigen')
            ->add('biografia')
            ->add('imgArtista')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Artista::class,
        ]);
    }
}
