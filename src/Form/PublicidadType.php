<?php

// src/Form/PublicidadType.php

namespace App\Form;

use App\Entity\Publicidad;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PublicidadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('imagen', FileType::class, [
                'label' => 'Imagen (GIF, JPG, PNG)',
                'required' => true,
                'mapped' => false,
                'attr' => [
                    'class' => 'dropzone',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Publicidad::class,
        ]);
    }
}
