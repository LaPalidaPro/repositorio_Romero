<?php

namespace App\Form;

use App\Entity\Cancion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CancionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('idArtista')
            ->add('idAlbum')
            ->add('titulo')
            ->add('duracion')
            ->add('generoMusical')
            ->add('fechaLanzamiento', null, [
                'widget' => 'single_text',
            ])
            ->add('numeroReproducciones')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cancion::class,
        ]);
    }
}
