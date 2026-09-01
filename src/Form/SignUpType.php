<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\Regex;

class SignUpType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'required' => true,
                'attr' => [
                    'class' => 'form-group'
                ]
            ])
            ->add('lastName', TextType::class, [
                'required' => true,
                'attr' => [
                    'class' => 'form-group'
                ]
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'attr' => [
                    'class' => 'form-group'
                ]
            ])
            ->add('birthDate', DateType::class, [
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'required' => true,
                'attr' => [
                    'class' => 'form-group'

                ],
                'constraints' => [
                    new LessThan([
                        'value' => 'today',
                        'message' => 'Please enter a valid date'
                    ])
                ]
            ])
            ->add('username', TextType::class, [
                'required' => true,
                'attr' => [
                    'class' => 'form-group'
                ]
            ])
            ->add('password', RepeatedType::class, [
                'required' => true,
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'Password',
                    'trim' => true,
                    'toggle' => true,
                    'row_attr' => [
                        'class' => 'form-group'
                    ],
                    'attr' => [
                        'class' => 'form-group'
                    ]
                ],
                'second_options' => [
                    'label' => 'Confirm password',
                    'required' => true,
                    'row_attr' => [
                        'class' => 'form-group'
                    ],
                    'attr' => [
                        'class' => 'form-group'
                    ]
                ]
            ])
            ->add('phoneNumber', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Regex('/^\d{3} \d{2} \d{2} \d{2}$/')
                ],
                'attr' => [
                    'class' => 'form-group',
                    'placeholder' => 'XXX XX XX XX'
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
