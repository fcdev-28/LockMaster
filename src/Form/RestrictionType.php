<?php

namespace App\Form;

use App\Entity\AuthorizationRule;
use App\Entity\User;
use App\Entity\UserGroup;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
// Añadimos los listeners para la validación personalizada
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;

class RestrictionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('active', HiddenType::class, [
                'attr' => [
                    'value' => 1,
                    'class' => 'form-group'
                ]
            ])
            ->add('startTimestamp', TimeType::class, [
                'label' => 'Start hour',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'attr' => [
                    'class' => 'form-group'
                ]
            ])
            ->add('endTimestamp', TimeType::class, [
                'label' => 'End hour',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'attr' => [
                    'class' => 'form-group'
                ]
            ])
            ->add('mon', CheckboxType::class, [
                'label' => 'Monday',
                'required' => false
            ])
            ->add('tue', CheckboxType::class, [
                'label' => 'Tuesday',
                'required' => false
            ])
            ->add('wed', CheckboxType::class, [
                'label' => 'Wednesday',
                'required' => false
            ])
            ->add('thu', CheckboxType::class, [
                'label' => 'Thursday',
                'required' => false
            ])
            ->add('fri', CheckboxType::class, [
                'label' => 'Friday',
                'required' => false
            ])
            ->add('sat', CheckboxType::class, [
                'label' => 'Saturday',
                'required' => false
            ])
            ->add('sun', CheckboxType::class, [
                'label' => 'Sunday',
                'required' => false
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
            ->add('accessPoint', HiddenType::class, [
                'disabled' => true,
                'attr' => [
                    'class' => 'form-group'
                ]
            ])
        ;

        // Creamos la validación personalizada del formulario para que siempre haya al menos un día seleccionado
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            $daysSelected = $data->isMon() || $data->isTue() || $data->isWed() ||
                $data->isThu() || $data->isFri() || $data->isSat() ||
                $data->isSun();

            if (!$daysSelected) {
                $form->addError(new FormError('At least one day must be selected'));
            }
        });

        // Creamos otra valdación personalizada para que sólo se pueda elegir un usuario o un grupo. Nunca ambos o ninguno de ellos
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
            'data_class' => AuthorizationRule::class,
        ]);
    }
}