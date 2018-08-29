<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 29.08.2018
 * Time: 11:03
 */

namespace AppBundle\Form;

use AppBundle\Dto\RoomDto;
use AppBundle\Enum\RoomConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RoomTypeForm
 * @package AppBundle\Form
 */
class RoomTypeForm extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'hotel',
                ChoiceType::class,
                [
                    'choices' => $options['hotels'],
                ]
            )
            ->add(
                'capacity',
                ChoiceType::class,
                [
                    'choices' => RoomConfig::ROOM_CAPACITIES,
                ]
            )
            ->add(
                'smoking',
                ChoiceType::class,
                [
                    'choices' => [
                        'form.label.yes' => true,
                        'form.label.no' => false,
                    ],
                    'data' => true,
                    'expanded' => true,
                    'multiple' => false,
                    'attr' => [
                        'class' => 'col-md-3 col-sm-3 col-xs-6 no-lr-padding',
                    ],
                ]
            )
            ->add(
                'pet',
                ChoiceType::class,
                [
                    'choices' => [
                        'form.label.yes' => true,
                        'form.label.no' => false,
                    ],
                    'data' => true,
                    'expanded' => true,
                    'multiple' => false,
                    'attr' => [
                        'class' => 'col-md-3 col-sm-3 col-xs-6 no-lr-padding',
                    ],
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
                'data_class' => RoomDto::class,
                'hotels'     => null,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_roomDto';
    }
}
