<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_BillingAddressEditor
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */
namespace Mageplaza\BillingAddressEditor\Block\Order\Address;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order;
use Mageplaza\BillingAddressEditor\Helper\Data;

/**
 * Class Link
 * @package Mageplaza\BillingAddressEditor\Block\Order\Address
 */
class Link extends Template
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry = null;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Link constructor.
     *
     * @param TemplateContext $context
     * @param Registry $registry
     * @param Data $helperData
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        Registry $registry,
        Data $helperData,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->helperData = $helperData;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve current order model instance
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }

    /**
     * @return string
     */
    public function getEditBillingAddressUrl()
    {
        $order = $this->getOrder();

        return $this->getUrl('sales/order/billingAddress', ['order_id' => $order->getId()]);
    }

    /**
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->helperData->isEnabled()) {
            return '';
        }

        return parent::_toHtml();
    }
}
