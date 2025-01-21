<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\Size;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ArrayType;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ProductFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du produit',
            ])
            ->add('image', TextType::class, [
                'label' => 'Image du produit',
                'required' => false,
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Prix',
                'currency' => 'EUR',
            ])
            ->add('sizes', EntityType::class, [
                'class' => Size::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true, 
                'label' => 'Tailles disponibles',
                'attr' => [
                    'class' => 'form-check',
                ],
            ])
            ->add('stock', IntegerType::class, [
                'label' => 'Stock disponible',
                'attr' => [
                    'min' => 0,
                    'class' => 'form-control',
                ],
            ])
            ->add('highlighted', CheckboxType::class, [
                'label' => 'Produit mis en avant',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}

?>