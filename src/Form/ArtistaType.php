<?php

namespace App\Form;

use App\Entity\Artista;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;

class ArtistaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre', TextType::class, [
                'label' => 'Nombre Artista',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('anoDebut', DateType::class, [
                'label' => 'Año de Debut',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('paisOrigen', TextType::class, [
                'label' => 'País de Origen',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('biografia', TextareaType::class, [
                'label' => 'Biografía',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('imgArtista', FileType::class, [
                'label' => 'Imagen del Artista',
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
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
                'label' => 'Usuario',
                'attr' => ['class' => 'form-control']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Artista::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
        ]);
    }
}
