<?php

namespace App\Form;

use App\Entity\AuthorizationRule;
use App\Entity\User;
use App\Entity\UserGroup;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TemporaryAccessType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('active', HiddenType::class, [
                'attr' => [
                    'value' => 1
                ]
            ])
            ->add('temp', HiddenType::class, [
                'attr' => [
                    'value' => 1
                ]
            ])
            ->add('mon', HiddenType::class, [
                'attr' => [
                    'value' => 0
                ]
            ])
            ->add('tue', HiddenType::class, [
                'attr' => [
                    'value' => 0
                ]
            ])
            ->add('wed', HiddenType::class, [
                'attr' => [
                    'value' => 0
                ]
            ])
            ->add('thu', HiddenType::class, [
                'attr' => [
                    'value' => 0
                ]
            ])
            ->add('fri', HiddenType::class, [
                'attr' => [
                    'value' => 0
                ]
            ])
            ->add('sat', HiddenType::class, [
                'attr' => [
                    'value' => 0
                ]
            ])
            ->add('sun', HiddenType::class, [
                'attr' => [
                    'value' => 0
                ]
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'expanded' => true,
                'required' => false,
                'attr' => [
                    'class' => 'form-group'
                ]
            ])
            ->add('userGroup', EntityType::class, [
                'class' => UserGroup::class,
                'expanded' => true,
                'required' => false,
                'attr' => [
                    'class' => 'form-group'
                ]
            ])
            ->add('seconds', NumberType::class, [
                'label' => 'Seconds',
                'mapped' => false,
                'required' => true,
                'attr' => [
                    'class' => 'form-group',
                    'min' => 0,
                    'value' => 0
                ]
            ])
            ->add('minutes', NumberType::class, [
                'label' => 'Minutes',
                'mapped' => false,
                'required' => true,
                'attr' => [
                    'class' => 'form-group',
                    'min' => 0,
                    'value' => 0
                ]
            ])
            ->add('hours', NumberType::class, [
                'label' => 'Hours',
                'mapped' => false,
                'required' => true,
                'attr' => [
                    'class' => 'form-group',
                    'min' => 0,
                    'value' => 0
                ]
            ])
            ->add('days', NumberType::class, [
                'label' => 'Days',
                'mapped' => false,
                'required' => true,
                'attr' => [
                    'class' => 'form-group',(int)
                    'min' => 0,
                    'value' => 0
                ]
            ])
        ;

        // Validación personalizada para que no se guarden restricciones temporales con las misma fecha de inicio y de fin
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();

            $sec = (int) $form->get('seconds')->getData();
            $min = (int) $form->get('minutes')->getData();
            $hours = (int) $form->get('hours')->getData();
            $days = (int) $form->get('days')->getData();

            if ($sec + $min + $hours + $days === 0) {
                $form->addError(new FormError('At least one of the fields (seconds, minutes, hours, days) must be filled in'));
            }
        });

        // Creamos una valdación personalizada para que sólo se pueda elegir un usuario o un grupo. Nunca ambos o ninguno de ellos
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            $userSelected = $data->getUser() !== null;
            $groupSelected = $data->getUserGroup() !== null;

            if (!($userSelected xor $groupSelected)) {
                $form->addError(new FormError('You must select either a user or a group, but not both or none'));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AuthorizationRule::class
        ]);
    }
}
