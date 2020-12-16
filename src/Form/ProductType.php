<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, array('label' => false ))
            ->add('category')
            ->add('cost', TextType::class, array('label' => false ))
            ->add('description', CKEditorType::class, array(
                'label' => false,
                'config' => array(
                    'uiColor' => '#ffffff',
                    'enterMode' => 'CKEDITOR.ENTER_BR',
                    //...
                ),
            ))
            ->add('image', FileType::class, array('label' => 'Image (PNG/JPG file)', 'data_class' => null))
            ->add('availability', ChoiceType::class, array(
                    'label' => false,
                    'choices'  => array(
                        'Available' => 'available',
                        'Not Available' => 'not_available',
                    ),
                ))            
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
