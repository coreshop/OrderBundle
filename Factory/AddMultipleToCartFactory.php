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

namespace CoreShop\Bundle\OrderBundle\Factory;

use CoreShop\Bundle\OrderBundle\DTO\AddMultipleToCartInterface;

class AddMultipleToCartFactory implements AddMultipleToCartFactoryInterface
{
    /**
     * @psalm-param class-string $addMultipleToCartClass
     */
    public function __construct(
        protected string $addMultipleToCartClass,
    ) {
    }

    public function createWithMultipleAddToCarts(array $addToCarts): AddMultipleToCartInterface
    {
        $class = new $this->addMultipleToCartClass($addToCarts);

        if (!in_array(AddMultipleToCartInterface::class, class_implements($class), true)) {
            throw new \InvalidArgumentException(
                sprintf('%s needs to implement "%s".', $class::class, AddMultipleToCartInterface::class),
            );
        }

        return $class;
    }
}
