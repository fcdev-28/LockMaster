<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class EditPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (!$options['admin']) {
            $builder
                ->add('oldPassword', PasswordType::class, [
                    'mapped' => false,
                    'required' => true,
                    'label' => 'Current password',
                    'toggle' => true,
                    'constraints' => [
                        new NotBlank(),
                        new UserPassword([
                            'message' => 'Incorrect current password'
                        ])
                    ],
                    'attr' => [
                        'class' => 'form-group'
                    ]
                ])
            ;
        }

        $builder
            ->add('newPassword', RepeatedType::class, [
                'label' => 'New password',
                'required' => true,
                'type' => PasswordType::class,
                'mapped' => false,
                'first_options' => [
                    'label' => 'New password',
                    'toggle' => true,
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Please enter a password',
                        ]),
                        new Length([
                            'min' => 8,
                            'minMessage' => 'Your password must be at least {{ limit }} characters long',
                            'max' => 4096,
                        ]),
                        new Regex([
                            'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z0-9]).+$/',
                            'message' => 'Your password must include at least one uppercase letter, one lowercase letter, one number, and one special character',
                        ]),
                    ],
                    'attr' => [
                        'class' => 'form-group'
                    ]
                ],
                'second_options' => [
                    'label' => 'Confirm new password',
                    'required' => true,
                    'attr' => [
                        'class' => 'form-group'
                    ]
                ],
                'attr' => [
                    'class' => 'form-row'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'admin' => false
        ]);
    }
}
