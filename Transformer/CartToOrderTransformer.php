<?php
/**
 * CoreShop.
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) 2015-2017 Dominik Pfaffenbauer (https://www.pfaffenbauer.at)
 * @license    https://www.coreshop.org/license     GNU General Public License version 3 (GPLv3)
*/

namespace CoreShop\Bundle\OrderBundle\Transformer;

use Carbon\Carbon;
use CoreShop\Component\Locale\Context\LocaleContextInterface;
use CoreShop\Component\Shipping\Model\CarrierInterface;
use CoreShop\Component\Resource\Pimcore\ObjectServiceInterface;
use CoreShop\Component\Currency\Context\CurrencyContextInterface;
use CoreShop\Component\Order\Cart\Rule\CartPriceRuleOrderProcessorInterface;
use CoreShop\Component\Order\Model\CartInterface;
use CoreShop\Component\Order\Model\CartItemInterface;
use CoreShop\Component\Order\Model\OrderInterface;
use CoreShop\Component\Order\Model\ProposalCartPriceRuleItemInterface;
use CoreShop\Component\Order\Model\ProposalInterface;
use CoreShop\Component\Order\NumberGenerator\NumberGeneratorInterface;
use CoreShop\Component\Order\Transformer\ProposalItemTransformerInterface;
use CoreShop\Component\Order\Transformer\ProposalTransformerInterface;
use CoreShop\Component\Resource\Factory\PimcoreFactoryInterface;
use CoreShop\Component\Resource\Transformer\ItemKeyTransformerInterface;
use CoreShop\Component\Store\Context\StoreContextInterface;
use Pimcore\Model\Object\Fieldcollection;
use Webmozart\Assert\Assert;

class CartToOrderTransformer implements ProposalTransformerInterface
{
    /**
     * @var ProposalItemTransformerInterface
     */
    protected $cartItemToOrderItemTransformer;

    /**
     * @var ItemKeyTransformerInterface
     */
    protected $keyTransformer;

    /**
     * @var NumberGeneratorInterface
     */
    protected $numberGenerator;

    /**
     * @var string
     */
    protected $orderFolderPath;

    /**
     * @var ObjectServiceInterface
     */
    protected $objectService;

    /**
     * @var LocaleContextInterface
     */
    protected $localeContext;

    /**
     * @var CurrencyContextInterface
     */
    protected $currencyContext;

    /**
     * @var StoreContextInterface
     */
    protected $storeContext;

    /**
     * @var PimcoreFactoryInterface
     */
    protected $orderItemFactory;

    /**
     * @var CartPriceRuleOrderProcessorInterface
     */
    protected $cartPriceRuleOrderProcessor;

    /**
     * @var TransformerEventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param ProposalItemTransformerInterface     $cartItemToOrderItemTransformer
     * @param ItemKeyTransformerInterface          $keyTransformer
     * @param NumberGeneratorInterface             $numberGenerator
     * @param string                               $orderFolderPath
     * @param ObjectServiceInterface               $objectService
     * @param LocaleContextInterface               $localeContext
     * @param PimcoreFactoryInterface              $orderItemFactory
     * @param CurrencyContextInterface             $currencyContext
     * @param StoreContextInterface                $storeContext
     * @param CartPriceRuleOrderProcessorInterface $cartPriceRuleOrderProcessor
     * @param TransformerEventDispatcherInterface  $eventDispatcher
     */
    public function __construct(
        ProposalItemTransformerInterface $cartItemToOrderItemTransformer,
        ItemKeyTransformerInterface $keyTransformer,
        NumberGeneratorInterface $numberGenerator,
        $orderFolderPath,
        ObjectServiceInterface $objectService,
        LocaleContextInterface $localeContext,
        PimcoreFactoryInterface $orderItemFactory,
        CurrencyContextInterface $currencyContext,
        StoreContextInterface $storeContext,
        CartPriceRuleOrderProcessorInterface $cartPriceRuleOrderProcessor,
        TransformerEventDispatcherInterface $eventDispatcher
    ) {
        $this->cartItemToOrderItemTransformer = $cartItemToOrderItemTransformer;
        $this->keyTransformer = $keyTransformer;
        $this->numberGenerator = $numberGenerator;
        $this->orderFolderPath = $orderFolderPath;
        $this->objectService = $objectService;
        $this->localeContext = $localeContext;
        $this->orderItemFactory = $orderItemFactory;
        $this->currencyContext = $currencyContext;
        $this->storeContext = $storeContext;
        $this->cartPriceRuleOrderProcessor = $cartPriceRuleOrderProcessor;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function transform(ProposalInterface $cart, ProposalInterface $order)
    {
        /**
         * @var $cart CartInterface
         */
        Assert::isInstanceOf($cart, CartInterface::class);
        Assert::isInstanceOf($order, OrderInterface::class);

        $this->eventDispatcher->dispatchPreEvent('order', $order, ['cart' => $cart]);

        $orderFolder = $this->objectService->createFolderByPath(sprintf('%s/%s', $this->orderFolderPath, date('Y/m/d')));

        $orderNumber = $this->numberGenerator->generate($order);
        /**
         * @var $order OrderInterface
         */
        $order->setKey($this->keyTransformer->transform($orderNumber));
        $order->setOrderNumber($orderNumber);
        $order->setParent($orderFolder);
        $order->setPublished(true);
        $order->setCustomer($cart->getCustomer());
        $order->setOrderLanguage($this->localeContext->getLocaleCode());
        $order->setOrderDate(Carbon::now());
        $order->setCurrency($this->currencyContext->getCurrency());
        $order->setStore($this->storeContext->getStore());
        $order->setPaymentFee($cart->getPaymentFee(true), true);
        $order->setPaymentFee($cart->getPaymentFee(false), false);
        $order->setPaymentFeeTaxRate($cart->getPaymentFeeTaxRate());
        $order->setTotal($cart->getTotal(true), true);
        $order->setTotal($cart->getTotal(false), false);
        $order->setTotalTax($cart->getTotalTax());
        $order->setSubtotal($cart->getSubtotal(true), true);
        $order->setSubtotal($cart->getSubtotal(false), false);
        $order->setSubtotalTax($cart->getSubtotalTax());
        $order->setDiscount($cart->getDiscount(true), true);
        $order->setDiscount($cart->getDiscount(false), false);
        $order->setShippingAddress($cart->getShippingAddress());
        $order->setInvoiceAddress($cart->getInvoiceAddress());
        $order->setWeight($cart->getWeight());

        if ($cart->getPriceRuleItems() instanceof Fieldcollection) {
            foreach ($cart->getPriceRuleItems() as $priceRule) {
                if ($priceRule instanceof ProposalCartPriceRuleItemInterface) {
                    $this->cartPriceRuleOrderProcessor->process($priceRule->getCartPriceRule(), $priceRule->getVoucherCode(), $cart, $order);
                }
            }
        }

        /*
         * We need to save the order twice in order to create the object in the tree for pimcore
         */
        $order->save();

        /**
         * @var CartItemInterface
         */
        foreach ($cart->getItems() as $cartItem) {
            $orderItem = $this->orderItemFactory->createNew();

            $order->addItem($this->cartItemToOrderItemTransformer->transform($order, $cartItem, $orderItem));
        }

        //TODO: Collect taxes

        $this->eventDispatcher->dispatchPostEvent('order', $order, ['cart' => $cart]);

        $order->save();

        $cart->setOrder($order);
        $cart->save();

        return $order;
    }
}
