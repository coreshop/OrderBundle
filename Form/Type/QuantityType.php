<?php
/**
 * CoreShop.
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) CoreShop GmbH (https://www.coreshop.org)
 * @license    https://www.coreshop.org/license     GNU General Public License version 3 (GPLv3)
 */

declare(strict_types=1);

namespace CoreShop\Bundle\OrderBundle\Form\Type;

use CoreShop\Component\Product\Model\ProductUnitDefinitionInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class QuantityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addViewTransformer(new CallbackTransformer(
            function (mixed $value): mixed {
                return is_string($value) ? str_replace(',', '.', $value) : $value;
            },
            function (mixed $value): mixed {
                return $value;
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'html5' => true,
            'unit_definition' => null,
            'attr' => [
                'min' => 0,
                'step' => 1,
                'data-cs-unit-precision' => 0,
                'autocomplete' => 'off',
            ],
        ]);

        $resolver->setAllowedTypes('html5', 'bool');
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if ($options['unit_definition'] instanceof ProductUnitDefinitionInterface) {
            $precision = $options['unit_definition']->getPrecision();
            $view->vars['attr']['data-cs-unit-precision'] = $precision;

            if ($precision > 0) {
                $view->vars['attr']['step'] = sprintf('0.%s1', str_repeat('0', $precision - 1));
            }
        }

        if (true === $options['html5']) {
            $view->vars['type'] = 'number';
        }
    }

    public function getParent(): string
    {
        return NumberType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'coreshop_quantity';
    }
}
