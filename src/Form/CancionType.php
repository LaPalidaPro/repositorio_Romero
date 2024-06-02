<?php 

namespace App\Form;

use App\Entity\Cancion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class CancionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titulo', TextType::class, [
                'label' => 'Título de la Canción',
                'attr' => ['class' => 'form-control']
            ])
            ->add('duracion', IntegerType::class, [
                'label' => 'Duración (segundos)',
                'attr' => ['class' => 'form-control']
            ])
            ->add('archivo', FileType::class, [
                'label' => 'Archivo de la Canción',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'custom-file-input'],
                'constraints' => [
                    new File([
                        'maxSize' => '50M',
                        'mimeTypes' => [
                            'audio/mpeg',
                            'audio/mp3',
                            'audio/wav',
                        ],
                        'mimeTypesMessage' => 'Por favor, sube un archivo de audio válido (MP3, WAV)',
                    ])
                ],
            ])
            ->add('generoMusical', TextType::class, [
                'label' => 'Género Musical',
                'attr' => ['class' => 'form-control']
            ])
            ->add('fechaLanzamiento', null, [
                'widget' => 'single_text',
                'label' => 'Fecha de Lanzamiento',
                'attr' => ['class' => 'form-control']
            ])
            ->add('numeroReproducciones', IntegerType::class, [
                'label' => 'Número de Reproducciones',
                'attr' => ['class' => 'form-control']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cancion::class,
        ]);
    }
}
