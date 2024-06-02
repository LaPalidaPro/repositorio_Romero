<?php

namespace App\Form;

use App\Entity\Album;
use App\Entity\Artista;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class AlbumType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre', TextType::class, [
                'label' => 'Nombre del Álbum',
                'attr' => ['class' => 'form-control']
            ])
            ->add('fechaLanzamiento', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Fecha de Lanzamiento',
                'attr' => ['class' => 'form-control']
            ])
            ->add('generosMusicales', ChoiceType::class, [
                'label' => 'Géneros Musicales',
                'choices'  => [
                    'Rock' => 'rock',
                    'Pop' => 'pop',
                    'Jazz' => 'jazz',
                    'Blues' => 'blues',
                    'Clásica' => 'clasica',
                    'Reggaeton' => 'reggaeton',
                    'Hip Hop' => 'hip_hop',
                    'Electrónica' => 'electronica',
                    'Otro' => 'otro'
                ],
                'multiple' => true,
                'expanded' => true,
                'attr' => ['class' => 'form-check']
            ])
            ->add('fotoPortada', FileType::class, [
                'label' => 'Foto de Portada',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'custom-file-input'],
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'Por favor, sube un archivo de imagen válido (JPEG, PNG, GIF)',
                    ])
                ],
            ])
            ->add('artista', EntityType::class, [
                'class' => Artista::class,
                'choice_label' => 'nombre',
                'label' => 'Artista',
                'attr' => ['class' => 'form-control']
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
