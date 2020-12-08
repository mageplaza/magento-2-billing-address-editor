<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
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
namespace Mageplaza\BillingAddressEditor\Controller\Order;

use Magento\Framework\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Controller\AbstractController\OrderLoaderInterface;
use Magento\Sales\Controller\AbstractController\View;
use Magento\Sales\Controller\OrderInterface;
use Mageplaza\BillingAddressEditor\Helper\Data;

/**
 * Class Billing
 * @package Mageplaza\BillingAddressEditor\Controller\Order\Address
 */
class BillingAddress extends View implements OrderInterface, HttpGetActionInterface
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * BillingAddress constructor.
     *
     * @param Action\Context $context
     * @param OrderLoaderInterface $orderLoader
     * @param PageFactory $resultPageFactory
     * @param Data $helperData
     */
    public function __construct(
        Action\Context $context,
        OrderLoaderInterface $orderLoader,
        PageFactory $resultPageFactory,
        Data $helperData
    ) {
        $this->helperData = $helperData;
        parent::__construct($context, $orderLoader, $resultPageFactory);
    }

    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        if (!$this->helperData->isEnabled()) {
            return $this->_redirect('sales/order/history');
        }

        $this->messageManager->addNoticeMessage(
            __('Changing address information will not recalculate shipping, tax or other order amount.')
        );

        return parent::execute();
    }
}
