<?php

/**
 * Url type.
 */

namespace App\Form\Type;

use App\Entity\Url;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Form\DataTransformer\TagsDataTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

/**
 * Class UrlType.
 */
class UrlType extends AbstractType
{
    /**
     * Constructor.
     *
     * @param TagsDataTransformer $tagsDataTransformer Tags data transformer
     */
    public function __construct(private readonly TagsDataTransformer $tagsDataTransformer)
    {
    }

    /**
     * Builds the form.
     *
     * This method is called for each type in the hierarchy starting from the
     * top most type. Type extensions can further modify the form.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array<string, mixed> $options Form options
     *
     * @see FormTypeExtensionInterface::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('originalUrl', TextType::class, [
            'label' => 'label.originalUrl',
            'required' => true,
            'attr' => ['max_length' => 2048],
        ]);
        $builder->add('shortCode', TextType::class, [
            'label' => 'label.shortCode',
            'required' => false,
        ]);
        $builder->add(
            'tags',
            TextType::class,
            [
                'label' => 'label.tags',
                'required' => false,
                'attr' => ['max_length' => 128],
            ]
        );
        if ($options['is_authenticated']) {
            $builder->add('isPublished', CheckboxType::class, [
                'label' => 'label.isPublished',
                'required' => false,
            ]);
        }

        if (!$options['is_authenticated']) {
            $builder->add('authorEmail', TextType::class, [
                'label' => 'label.email',
                'required' => true,
            ]);
        }

        $builder->get('tags')->addModelTransformer(
            $this->tagsDataTransformer
        );
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Url::class, 'is_authenticated' => false]);
    }

    /**
     * Returns the prefix of the template block name for this type.
     *
     * The block prefix defaults to the underscored short class name with
     * the "Type" suffix removed (e.g. "UserProfileType" => "user_profile").
     *
     * @return string The prefix of the template block name
     */
    public function getBlockPrefix(): string
    {
        return 'url';
    }
}
