<?php

namespace App\Form;

use App\Entity\Trick;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class TrickType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom*'
            ])
            ->add('images', CollectionType::class, [
                'entry_type' => ImageType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'label' => false,
                'required' => false,
                'entry_options' => [
                    'label' => false,
                ],
            ])
            ->add('videos', CollectionType::class, [
                'entry_type' => VideoType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'label' => false,
                'required' => false,
                'entry_options' => [
                    'label' => false,
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description*',
                'attr' => [
                    'rows' => '6',
                ]
            ])
            ->add('featuredImageFile', FileType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Image à la une',
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/png',
                            'image/jpeg',
                        ],
                        'mimeTypesMessage' => "Veuillez ajouter une image ayant l'extension .png, .jpg ou .jpeg",
                    ])
                ]
            ])
            ->add('deleteFeaturedImage', CheckboxType::class, [
                'mapped' => false,
                'label' => 'Confirmer la suppression',
                'required' => false,
            ])
            ->add('categories', null, [
                'label' => 'Groupe',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Trick::class,
        ]);
    }
}
