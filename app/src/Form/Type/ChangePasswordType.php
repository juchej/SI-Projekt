<?php

/**
 * Change Password type.
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

/**
 * Class ChangePasswordType.
 */
class ChangePasswordType extends AbstractType
{
    /**
     * Builds the form.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array<string, mixed> $options Form options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (!$options['is_admin']) {
            $builder->add('currentPassword', PasswordType::class, [
                'label' => 'label.currentPassword',
                'mapped' => false,
                'attr' => ['autocomplete' => 'current-password'],
                'constraints' => [
                    new UserPassword(['message' => 'message.invalidCurrentPassword']),
                ],
            ]);
        }
        $builder->add('plainPassword', RepeatedType::class, [
            'type' => PasswordType::class,
            'mapped' => false,
            'first_options' => ['label' => 'label.newPassword'],
            'second_options' => ['label' => 'label.repeatPassword'],
            'invalid_message' => 'message.passwordMismatch',
            'constraints' => [
                new NotBlank(['message' => 'message.passwordBlank']),
                new Length([
                    'min' => 6,
                    'minMessage' => 'message.passwordTooShort',
                ]),
            ],
        ]);
    }

    /**
     * Configures the options for this form type.
     *
     * @param OptionsResolver $resolver the options resolver instance
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'is_admin' => false,
        ]);
    }
}
