<?php
/**
 * CoreShop.
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) 2015-2019 Dominik Pfaffenbauer (https://www.pfaffenbauer.at)
 * @license    https://www.coreshop.org/license     GNU General Public License version 3 (GPLv3)
 */

namespace CoreShop\Bundle\OrderBundle\Factory;

use CoreShop\Bundle\OrderBundle\Controller\AddToCartCommandInterface;
use CoreShop\Component\Order\Model\CartInterface;
use CoreShop\Component\Order\Model\PurchasableInterface;

interface AddToCartCommandFactoryInterface
{
    /**
     * @param CartInterface $cart
     * @param PurchasableInterface $purchasable
     * @param int $quantity
     * @return AddToCartCommandInterface
     */
    public function createWithCartAndPurchasableAndQuantity(
        CartInterface $cart,
        PurchasableInterface $purchasable,
        int $quantity
    );
}
