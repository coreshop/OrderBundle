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

namespace CoreShop\Bundle\OrderBundle\EventListener\Grid;

use CoreShop\Component\Pimcore\DataObject\Grid\GridFilterInterface;
use CoreShop\Component\Registry\ServiceRegistryInterface;
use Pimcore\Model\DataObject;
use Symfony\Component\EventDispatcher\GenericEvent;

final class CartFilterListener
{
    /**
     * @param GenericEvent $event
     */
    public function checkObjectList(GenericEvent $event)
    {
        $list = $event->getArgument('list');
        $context = $event->getArgument('context');

        if (!isset($context['coreshop_cart'])) {
            return;
        }

        if ($list instanceof DataObject\Listing) {
            $list->addConditionParam('customer__id IS NOT NULL');

            $event->setArgument('list', $list);
        }
    }
}
