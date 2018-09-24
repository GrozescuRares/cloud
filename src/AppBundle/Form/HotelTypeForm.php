<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 07.09.2018
 * Time: 08:45
 */

namespace AppBundle\Form;

use AppBundle\Dto\HotelDto;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class HotelFormType
 */
class HotelTypeForm extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'label' => 'form.label.hotel_name',
                ]
            )
            ->add(
                'location',
                TextType::class,
                [
                    'label' => 'form.label.location',
                ]
            )
            ->add(
                'description',
                TextareaType::class,
                [
                    'label' => 'form.label.description',
                ]
            )
            ->add(
                'facilities',
                TextareaType::class,
                [
                    'label' => 'form.label.facilities',
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => HotelDto::class,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_hotelDto';
    }
}
