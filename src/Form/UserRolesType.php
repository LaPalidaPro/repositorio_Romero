<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use App\Entity\User;

class UserRolesType extends AbstractType
{
    private $roleHierarchy;

    public function __construct(RoleHierarchyInterface $roleHierarchy)
    {
        $this->roleHierarchy = $roleHierarchy;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $allRoles = ['ROLE_USER', 'ROLE_ADMIN'];

        $builder
            ->add('roles', ChoiceType::class, [
                'choices' => array_combine($allRoles, $allRoles),
                'expanded' => true, // Use checkboxes
                'multiple' => true, // Allow multiple selections
                'label' => 'Roles'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => true,
            'csrf_fiel_name' => '_token',
        ]);
    }
}
