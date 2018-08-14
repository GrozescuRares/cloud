<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 14.08.2018
 * Time: 09:18
 */

namespace AppBundle\Form;

use AppBundle\Entity\User;
use SebastianBergmann\CodeCoverage\Report\Text;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
            ->add(
                'username',
                TextType::class,
                [
                    'label' => 'Username',
                ]
            )
            ->add(
                'plainPassword',
                RepeatedType::class,
                [
                    'type' => PasswordType::class,
                    'first_options' => [
                        'label' => 'Password',
                    ],
                    'second_options' => [
                        'label' => 'Repeat password',
                    ],
                ]
            )
            ->add(
                'email',
                EmailType::class,
                [
                    'label' => 'Email',
                ]
            )
            ->add(
                'address',
                TextType::class,
                [
                    'label' => 'Address',
                    'required' => false,
                ]
            )
            ->add(
                'dateOfBirth',
                DateType::class,
                [
                    'placeholder' => [
                        'year' => 'Year',
                        'month' => 'Month',
                        'day' => 'Day',
                    ],
                    'widget' => 'single_text',
                    'attr' => ['class' => 'selectpicker'],
                ]
            )
            ->add(
                'gender',
                ChoiceType::class,
                [
                    'choices' => [
                        'Male' => 'Male',
                        'Female' => 'Female',
                    ],

                ]
            )
            ->add(
                'bio',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'profilePicture',
                FileType::class,
                ['label' => 'Profile Picture']
            )
            ->add('register', SubmitType::class);
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

