<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 31.08.2018
 * Time: 14:51
 */

namespace AppBundle\Form;

use AppBundle\Dto\ReservationDto;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
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
            )
            ->add(
                'hotel',
                ChoiceType::class,
                [
                    'choices' => $options['hotels'],
                    'label' => 'form.label.hotel',
                    'attr' => [
                        'class' => 'selectpicker',
                    ],
                ]
            )
            ->add(
                'room',
                ChoiceType::class,
                [
                    'choices' => $options['rooms'],
                    'label' => 'form.label.room',
                    'attr' => [
                        'class' => 'selectpicker',
                    ],
                ]
            )
            ->add(
                'save',
                SubmitType::class,
                [
                    'attr' => [
                        'class' => 'btn submit pull-right margin-top-large',
                    ],
                    'label' => 'form.label.save',
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
                'data_class' => ReservationDto::class,
                'hotels'     => null,
                'rooms'      => null,
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
