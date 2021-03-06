<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 14.08.2018
 * Time: 09:18
 */

namespace AppBundle\Form;

use AppBundle\Entity\User;
use AppBundle\Enum\UserConfig;

use Ivory\CKEditorBundle\Form\Type\CKEditorType;
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
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MemberType
 * @package AppBundle\Form
 */
class UserTypeForm extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $form = $event->getForm();
                $loggedUser = $event->getForm()->getConfig()->getOptions()['loggedUser'];
                if (!empty($loggedUser)) {
                    if ($loggedUser->getRoles() === [UserConfig::ROLE_OWNER]) {
                        $hotels = $event->getForm()->getConfig()->getOptions()['hotels'];
                        $form->add(
                            'hotel',
                            ChoiceType::class,
                            [
                                'choices' => $hotels,
                            ]
                        );
                    }
                }
            }
        );

        if (!empty($options['roles']) && !empty($options['type']) && $options['type'] === 'add-user') {
            $builder->add(
                'role',
                ChoiceType::class,
                [
                    'choices' => $options['roles'],
                ]
            );
        }

        if (!empty($options['type']) && $options['type'] !== 'my-account') {
            $builder->
            add(
                'username',
                TextType::class,
                [
                    'label' => 'form.label.username',
                ]
            );
        }
        $builder
            ->add(
                'firstName',
                TextType::class,
                [
                    'label' => 'form.label.firstName',
                ]
            )
            ->add(
                'lastName',
                TextType::class,
                [
                    'label' => 'form.label.lastName',
                ]
            )
            ->add(
                'plainPassword',
                RepeatedType::class,
                [
                    'type' => PasswordType::class,
                    'first_options' => [
                        'label' => 'form.label.password',
                    ],
                    'second_options' => [
                        'label' => 'form.label.confirmPassword',
                    ],
                    'required' => false,
                ]
            )
            ->add(
                'email',
                EmailType::class,
                [
                    'label' => 'form.label.email',
                ]
            )
            ->add(
                'address',
                TextType::class,
                [
                    'label' => 'form.label.address',
                    'required' => false,
                ]
            )
            ->add(
                'dateOfBirth',
                DateType::class,
                [
                    'placeholder' => [
                        'year' => 'form.label.year',
                        'month' => 'form.label.month',
                        'day' => 'form.label.day',
                    ],
                    'years' => range(1930, date('Y')),
                    'input' => 'string',
                ]
            )
            ->add(
                'gender',
                ChoiceType::class,
                [
                    'choices' => [
                        'form.label.male' => 'Male',
                        'form.label.female' => 'Female',
                    ],
                    'data' => 'Male',
                    'expanded' => true,
                    'multiple' => false,
                    'attr' => [
                        'class' => 'col-md-3 col-sm-3 col-xs-6 no-lr-padding',
                    ],
                ]
            )
            ->add(
                'bio',
                CKEditorType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'image',
                FileType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'submit',
                SubmitType::class,
                [
                    'attr' => [
                        'class' => 'btn submit pull-right',
                    ],
                    'label' => 'form.label.submit',
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
                'data_class' => User::class,
                'type'       => null,
                'loggedUser' => null,
                'roles'      => null,
                'hotels'     => null,
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
