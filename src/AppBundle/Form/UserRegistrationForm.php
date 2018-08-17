<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 14.08.2018
 * Time: 09:18
 */

namespace AppBundle\Form;

use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MemberType
 * @package AppBundle\Form
 */
class UserRegistrationForm extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        $builder
            ->add('username', TextType::class, [
                    'label' => 'Username',
            ])
            ->add('firstName', TextType::class, [
                'label' => 'First Name',
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Last Name',
            ])
            ->add('plainPassword', RepeatedType::class, [
                    'type' => PasswordType::class,
                    'first_options' => [
                        'label' => 'Password',
                    ],
                    'second_options' => [
                        'label' => 'Confirm password',
                    ],
            ])
            ->add('email', EmailType::class, [
                    'label' => 'Email',
            ])
            ->add('address', TextType::class, [
                    'label' => 'Address',
                    'required' => false,
            ])
            ->add('dateOfBirth', DateType::class, [
                    'placeholder' => [
                        'year' => 'Year',
                        'month' => 'Month',
                        'day' => 'Day',
                    ],
                    'years' => range(1930, date('Y')),
                    'input' => 'string',
            ])
            ->add('gender', ChoiceType::class, [
                    'choices' => [
                        'Male' => 'Male',
                        'Female' => 'Female',
                    ],
                    'data' => 'Male',
                    'expanded' => true,
                    'multiple' => false,
                    'attr' => [
                        'class' => 'col-md-3 col-sm-3 col-xs-6 no-lr-padding',
                    ],
            ])
            ->add('bio', TextareaType::class, [
                    'required' => false,
            ])
            ->add('image', FileType::class, [
                    'label' => 'Profile Picture',
                    'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                    'attr' => [
                        'class' => 'btn submit pull-right',
                    ],
            ]);
    }


    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => User::class,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_user';
    }
}
