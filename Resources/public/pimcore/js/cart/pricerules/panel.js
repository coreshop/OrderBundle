/*
 * CoreShop.
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) CoreShop GmbH (https://www.coreshop.org)
 * @license    https://www.coreshop.org/license     GPLv3 and CCL
 *
 */

pimcore.registerNS('coreshop.cart.pricerules.panel');

coreshop.cart.pricerules.panel = Class.create(coreshop.rules.panel, {

    /**
     * @var string
     */
    layoutId: 'coreshop_price_rules_panel',
    storeId: 'coreshop_cart_price_rules',
    iconCls: 'coreshop_icon_price_rule',
    type: 'coreshop_cart_pricerules',

    routing: {
        add: 'coreshop_cart_price_rule_add',
        delete: 'coreshop_cart_price_rule_delete',
        get: 'coreshop_cart_price_rule_get',
        list: 'coreshop_cart_price_rule_list',
        config: 'coreshop_cart_price_rule_getConfig'
    },

    getItemClass: function () {
        return coreshop.cart.pricerules.item;
    }
});
