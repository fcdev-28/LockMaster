<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Dropzone\Form\DropzoneType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'required' => true,
                'attr' => ['class' => 'form-group']
            ])
            ->add('lastName', TextType::class, [
                'required' => true,
                'attr' => ['class' => 'form-group']
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'attr' => ['class' => 'form-group']
            ])
            ->add('birthDate', DateType::class, [
                'required' => true,
                'input' => 'datetime_immutable',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-group']
            ])
            ->add('username', TextType::class, [
                'required' => true,
                'attr' => ['class' => 'form-group']
            ])
            ->add('phoneNumber', TextType::class, [
                'required' => true,
                'attr' => [
                    'class' => 'form-group',
                    'placeholder' => 'XXX XX XX XX'
                ]
            ])
            ->add('uploadedPhoto', DropzoneType::class, [
                'label' => 'Upload avatar',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'dropzone',
                    'data-controller' => 'dropzone',
                    'data-dropzone-max-files-value' => 1,
                    'data-dropzone-accepted-files-value' => 'image/*',
                    'placeholder' => 'Drag and drop a file or click to browse'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
