<?php

namespace App\Form;

use App\Entity\Currency;
use App\Entity\ExchangeRate;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExchangeRateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rate')
            ->add('date')
            ->add('baseCurrency', EntityType::class, [
                'class' => Currency::class,
                'choice_label' => 'id',
            ])
            ->add('targetCurrency', EntityType::class, [
                'class' => Currency::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ExchangeRate::class,
        ]);
    }
}
