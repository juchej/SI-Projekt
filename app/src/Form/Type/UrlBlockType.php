<?php

/**
 * Url Block Type.
 */

namespace App\Form\Type;

use App\Entity\Url;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class UrlBlockType.
 */
class UrlBlockType extends AbstractType
{
    /**
     * Builds the form for blocking a URL.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array<string, mixed> $options Form options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('permanent', CheckboxType::class, [
                'label' => 'label.permanentBlock',
                'required' => false,
                'mapped' => false,
            ])
            ->add('blockedUntil', DateTimeType::class, [
                'label' => 'label.blockUntil',
                'required' => false,
                'widget' => 'single_text',
                'html5' => true,
            ]);
    }

    /**
     * Configures the options for this form type.
     *
     * @param OptionsResolver $resolver The options resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Url::class,
        ]);
    }
}
