<?php

namespace App\Form;

use App\Entity\AllowedIp;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class AllowedIpType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ipOrSubnet', TextType::class, [
                'label' => 'IP / Subnet',
                'required' => true,
            ])
            ->add('description', TextType::class, [
                'label' => 'Description',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AllowedIp::class,
        ]);
    }
}