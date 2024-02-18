<?php

declare(strict_types=1);

/*
 * CoreShop
 *
 * This source file is available under two different licenses:
 *  - GNU General Public License version 3 (GPLv3)
 *  - CoreShop Commercial License (CCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) CoreShop GmbH (https://www.coreshop.org)
 * @license    https://www.coreshop.org/license     GPLv3 and CCL
 *
 */

namespace CoreShop\Bundle\OrderBundle\DependencyInjection;

use CoreShop\Bundle\OrderBundle\Attribute\AsCartPriceRuleActionProcessor;
use CoreShop\Bundle\OrderBundle\Attribute\AsCartPriceRuleConditionChecker;
use CoreShop\Bundle\OrderBundle\Attribute\AsPurchasableCustomAttributesCalculator;
use CoreShop\Bundle\OrderBundle\Attribute\AsPurchasableDiscountCalculator;
use CoreShop\Bundle\OrderBundle\Attribute\AsPurchasableDiscountPriceCalculator;
use CoreShop\Bundle\OrderBundle\Attribute\AsPurchasablePriceCalculator;
use CoreShop\Bundle\OrderBundle\Attribute\AsPurchasableRetailPriceCalculator;
use CoreShop\Bundle\OrderBundle\Attribute\AsPurchasableWholesalePriceCalculator;
use CoreShop\Bundle\OrderBundle\DependencyInjection\Compiler\CartPriceRuleActionPass;
use CoreShop\Bundle\OrderBundle\DependencyInjection\Compiler\CartPriceRuleConditionPass;
use CoreShop\Bundle\OrderBundle\DependencyInjection\Compiler\PurchasableCustomAttributesCalculatorsPass;
use CoreShop\Bundle\OrderBundle\DependencyInjection\Compiler\PurchasableDiscountCalculatorsPass;
use CoreShop\Bundle\OrderBundle\DependencyInjection\Compiler\PurchasableDiscountPriceCalculatorsPass;
use CoreShop\Bundle\OrderBundle\DependencyInjection\Compiler\PurchasablePriceCalculatorsPass;
use CoreShop\Bundle\OrderBundle\DependencyInjection\Compiler\PurchasableRetailPriceCalculatorsPass;
use CoreShop\Bundle\OrderBundle\DependencyInjection\Compiler\PurchasableWholesalePriceCalculatorsPass;
use CoreShop\Bundle\ResourceBundle\CoreShopResourceBundle;
use CoreShop\Bundle\ResourceBundle\DependencyInjection\Extension\AbstractModelExtension;
use CoreShop\Component\Order\Calculator\PurchasableCustomAttributesCalculatorInterface;
use CoreShop\Component\Order\Calculator\PurchasableDiscountCalculatorInterface;
use CoreShop\Component\Order\Calculator\PurchasableDiscountPriceCalculatorInterface;
use CoreShop\Component\Order\Calculator\PurchasablePriceCalculatorInterface;
use CoreShop\Component\Order\Calculator\PurchasableRetailPriceCalculatorInterface;
use CoreShop\Component\Order\Calculator\PurchasableWholesalePriceCalculatorInterface;
use CoreShop\Component\Order\Cart\Rule\Action\CartPriceRuleActionProcessorInterface;
use CoreShop\Component\Order\Cart\Rule\Condition\CartRuleConditionCheckerInterface;
use CoreShop\Component\Registry\Autoconfiguration;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class CoreShopOrderExtension extends AbstractModelExtension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configs = $this->processConfiguration($this->getConfiguration([], $container), $configs);
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('services.yml');

        $container->setParameter('coreshop.order.allow_edit', $configs['allow_order_edit']);

        $this->registerResources('coreshop', CoreShopResourceBundle::DRIVER_DOCTRINE_ORM, $configs['resources'], $container);
        $this->registerPimcoreModels('coreshop', $configs['pimcore'], $container);

//        if (class_exists(PimcoreDataHubBundle::class)) {
//            $this->registerDependantBundles('coreshop', [PimcoreDataHubBundle::class], $container);
//        }

        if (array_key_exists('pimcore_admin', $configs)) {
            $this->registerPimcoreResources('coreshop', $configs['pimcore_admin'], $container);
        }

        if (array_key_exists('stack', $configs)) {
            $this->registerStack('coreshop', $configs['stack'], $container);
        }

        $bundles = $container->getParameter('kernel.bundles');

        if (array_key_exists('PimcoreDataHubBundle', $bundles)) {
            $loader->load('services/data_hub.yml');
        }

        $container->setParameter('coreshop.order.legacy_serialization', $configs['legacy_serialization']);

        $loader->load('services.yml');

        Autoconfiguration::registerForAutoConfiguration(
            $container,
            CartPriceRuleActionProcessorInterface::class,
            CartPriceRuleActionPass::CART_PRICE_RULE_ACTION_TAG,
            AsCartPriceRuleActionProcessor::class,
            $configs['autoconfigure_with_attributes'],
        );

        Autoconfiguration::registerForAutoConfiguration(
            $container,
            CartRuleConditionCheckerInterface::class,
            CartPriceRuleConditionPass::CART_PRICE_RULE_CONDITION_TAG,
            AsCartPriceRuleConditionChecker::class,
            $configs['autoconfigure_with_attributes'],
        );

        Autoconfiguration::registerForAutoConfiguration(
            $container,
            PurchasableDiscountCalculatorInterface::class,
            PurchasableDiscountCalculatorsPass::PURCHASABLE_DISCOUNT_CALCULATOR_TAG,
            AsPurchasableDiscountCalculator::class,
            $configs['autoconfigure_with_attributes'],
        );

        Autoconfiguration::registerForAutoConfiguration(
            $container,
            PurchasableDiscountPriceCalculatorInterface::class,
            PurchasableDiscountPriceCalculatorsPass::PURCHASABLE_DISCOUNT_PRICE_CALCULATOR_TAG,
            AsPurchasableDiscountPriceCalculator::class,
            $configs['autoconfigure_with_attributes'],
        );

        Autoconfiguration::registerForAutoConfiguration(
            $container,
            PurchasableCustomAttributesCalculatorInterface::class,
            PurchasableCustomAttributesCalculatorsPass::PURCHASABLE_CUSTOM_ATTRIBUTES__CALCULATOR_TAG,
            AsPurchasableCustomAttributesCalculator::class,
            $configs['autoconfigure_with_attributes'],
        );

        Autoconfiguration::registerForAutoConfiguration(
            $container,
            PurchasablePriceCalculatorInterface::class,
            PurchasablePriceCalculatorsPass::PURCHASABLE_PRICE_CALCULATOR_TAG,
            AsPurchasablePriceCalculator::class,
            $configs['autoconfigure_with_attributes'],
        );

        Autoconfiguration::registerForAutoConfiguration(
            $container,
            PurchasableRetailPriceCalculatorInterface::class,
            PurchasableRetailPriceCalculatorsPass::PURCHASABLE_RETAIL_PRICE_CALCULATOR_TAG,
            AsPurchasableRetailPriceCalculator::class,
            $configs['autoconfigure_with_attributes'],
        );

        Autoconfiguration::registerForAutoConfiguration(
            $container,
            PurchasableWholesalePriceCalculatorInterface::class,
            PurchasableWholesalePriceCalculatorsPass::PURCHASABLE_WHOLESALE_PRICE_CALCULATOR_TAG,
            AsPurchasableWholesalePriceCalculator::class,
            $configs['autoconfigure_with_attributes'],
        );
    }
}
