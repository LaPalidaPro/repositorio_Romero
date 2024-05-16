<?php

namespace App\Form;

use App\Entity\Album;
use App\Entity\Artista;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AlbumType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fechaLanzamiento', null, [
                'widget' => 'single_text',
            ])
            ->add('numPistas')
            ->add('duracionTotal')
            ->add('generoMusical')
            ->add('fotoPortada')
            ->add('nombre')
            ->add('artista', EntityType::class, [
                'class' => Artista::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Album::class,
        ]);
    }
}
