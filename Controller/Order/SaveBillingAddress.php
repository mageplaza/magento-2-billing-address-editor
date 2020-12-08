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
 * @package     Mageplaza_EditOrderBillingAddress
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */
namespace Mageplaza\EditOrderBillingAddress\Controller\Order;

use Exception;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\Action;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Controller\AbstractController\OrderViewAuthorizationInterface;
use Magento\Sales\Model\Order\Address as AddressModel;
use Magento\Sales\Model\OrderFactory;
use Mageplaza\EditOrderBillingAddress\Helper\Data;

/**
 * Class SaveBillingAddress
 * @package Mageplaza\EditOrderBillingAddress\Controller\Order\Address
 */
class SaveBillingAddress extends Action\Action
{
    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var OrderViewAuthorizationInterface
     */
    protected $orderAuthorization;

    /**
     * @var RegionFactory
     */
    private $regionFactory;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * EditAddress constructor.
     *
     * @param Action\Context $context
     * @param OrderFactory $orderFactory
     * @param OrderViewAuthorizationInterface $orderAuthorization
     * @param Data $helperData
     * @param RegionFactory|null $regionFactory
     */
    public function __construct(
        Action\Context $context,
        OrderFactory $orderFactory,
        OrderViewAuthorizationInterface $orderAuthorization,
        Data $helperData,
        RegionFactory $regionFactory = null
    ) {
        $this->orderFactory       = $orderFactory;
        $this->orderAuthorization = $orderAuthorization;
        $this->helperData         = $helperData;
        $this->regionFactory      = $regionFactory ?: ObjectManager::getInstance()->get(RegionFactory::class);

        parent::__construct($context);
    }

    /**
     * @return bool|ResponseInterface|Redirect|ResultInterface|Page
     */
    public function execute()
    {
        $orderId = (int)$this->getRequest()->getParam('order_id');
        if ($this->helperData->isEnabled() && $orderId) {
            $order = $this->orderFactory->create()->load($orderId);

            if ($this->orderAuthorization->canView($order)) {
                $billingAddress = $order->getBillingAddress();
                $addressId = $billingAddress->getEntityId();
                /** @var $address OrderAddressInterface|AddressModel */
                $address = $this->_objectManager->create(
                    OrderAddressInterface::class
                )->load($addressId);
                $data = $this->getRequest()->getPostValue();
                $data = $this->updateRegionData($data);
                $resultRedirect = $this->resultRedirectFactory->create();
                if ($data && $address->getId()) {
                    $address->addData($data);
                    try {
                        $address->save();
                        $this->_eventManager->dispatch(
                            'admin_sales_order_address_update',
                            [
                                'order_id' => $address->getParentId()
                            ]
                        );
                        $this->messageManager->addSuccessMessage(__('You updated the order address.'));
                        return $resultRedirect->setPath('sales/order/billingAddress', ['order_id' => $order->getId()]);
                    } catch (Exception $e) {
                        $this->messageManager->addErrorMessage(
                            __('We can\'t update the order address right now.%1', $e->getMessage())
                        );
                    }
                    return $resultRedirect->setPath('sales/order/billingAddress', ['order_id' => $order->getId()]);
                } else {
                    return $resultRedirect->setPath('sales/order/view', ['order_id' => $order->getId()]);
                }
            } else {
                $this->messageManager->addErrorMessage(__('Invalid order'));
            }
        }

        return $this->_redirect('sales/order/history');
    }

    /**
     * Update region data
     *
     * @param array $attributeValues
     * @return array
     */
    private function updateRegionData($attributeValues)
    {
        if (!empty($attributeValues['region_id'])) {
            $newRegion = $this->regionFactory->create()->load($attributeValues['region_id']);
            $attributeValues['region_code'] = $newRegion->getCode();
            $attributeValues['region'] = $newRegion->getDefaultName();
        }

        return $attributeValues;
    }
}
