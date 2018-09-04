<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 31.08.2018
 * Time: 14:51
 */

namespace AppBundle\Form;

use AppBundle\Dto\ReservationDto;

use AppBundle\Enum\RoutesConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ReservationTypeForm
 * @package AppBundle\Form
 */
class ReservationTypeForm extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'startDate',
                DateType::class,
                [
                    'widget' => 'single_text',
                    'attr' => [
                        'class' => 'form-control',
                    ],
                ]
            )
            ->add(
                'endDate',
                DateType::class,
                [
                    'widget' => 'single_text',
                    'attr' => [
                        'class' => 'form-control',
                    ],
                ]
            );
        if (!empty($options['hotels'])) {
            $builder->add(
                'hotel',
                ChoiceType::class,
                [
                    'placeholder' => 'Please choose Hotel',
                    'choices' => $options['hotels'],
                    'label' => 'form.label.hotel',
                    'required' => false,
                ]
            );
        }
        if (!empty($options['rooms'])) {
            $builder->add(
                'room',
                ChoiceType::class,
                [
                    'placeholder' => 'Please choose Room',
                    'choices' => $options['rooms'],
                    'label' => 'form.label.room',
                    'attr' => [
                        'class' => 'selectpicker',
                    ],
                    'required' => false,
                ]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => ReservationDto::class,
                'hotels' => null,
                'rooms' => null,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_reservationDto';
    }
}
