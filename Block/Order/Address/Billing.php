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
 * @package     Mageplaza_EditOrderBillingAddress
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */
namespace Mageplaza\EditOrderBillingAddress\Block\Order\Address;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Block\Widget\Company;
use Magento\Customer\Block\Widget\Fax;
use Magento\Customer\Block\Widget\Name;
use Magento\Customer\Block\Widget\Telephone;
use Magento\Directory\Block\Data;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\OrderFactory;
use Magento\Customer\Helper\Address;
use Magento\Directory\Helper\Data as DirectoryHelperData;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;

/**
 * Class Billing
 * @package Mageplaza\Customize\Block\Order\Address
 */
class Billing extends Data
{
    /**
     * @var null
     */
    protected $_address = null;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var Address
     */
    protected $helperAddress;

    /**
     * @var DirectoryHelperData
     */
    protected $directoryHelperData;

    /**
     * Billing constructor.
     *
     * @param Context $context
     * @param DirectoryHelperData $directoryHelper
     * @param EncoderInterface $jsonEncoder
     * @param Config $configCacheType
     * @param CollectionFactory $regionCollectionFactory
     * @param CountryCollectionFactory $countryCollectionFactory
     * @param OrderFactory $orderFactory
     * @param Address $helperAddress
     * @param DirectoryHelperData $directoryHelperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        DirectoryHelperData $directoryHelper,
        EncoderInterface $jsonEncoder,
        Config $configCacheType,
        CollectionFactory $regionCollectionFactory,
        CountryCollectionFactory $countryCollectionFactory,
        OrderFactory $orderFactory,
        Address $helperAddress,
        DirectoryHelperData $directoryHelperData,
        array $data = []
    ) {
        $this->orderFactory        = $orderFactory;
        $this->helperAddress       = $helperAddress;
        $this->directoryHelperData = $directoryHelperData;
        parent::__construct(
            $context,
            $directoryHelper,
            $jsonEncoder,
            $configCacheType,
            $regionCollectionFactory,
            $countryCollectionFactory,
            $data
        );
    }

    /**
     * Prepare the layout of the address edit block.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->orderFactory->create()->load($orderId);
        if ($order->getId()) {
            $billingAddress = $order->getBillingAddress();
            $this->_address = $billingAddress;
        }

        $this->pageConfig->getTitle()->set($this->getTitle());

        return $this;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getNameBlockHtml()
    {
        $nameBlock = $this->getLayout()
            ->createBlock(Name::class)
            ->setObject($this->getAddress());

        return $nameBlock->toHtml();
    }

    /**
     * Return the associated address.
     *
     * @return AddressInterface
     */
    public function getAddress()
    {
        return $this->_address;
    }

    /**
     * Return the specified numbered street line.
     *
     * @param int $lineNumber
     * @return string
     */
    public function getStreetLine($lineNumber)
    {
        $street = $this->_address->getStreet();
        return isset($street[$lineNumber - 1]) ? $street[$lineNumber - 1] : '';
    }

    /**
     * Return the country Id.
     *
     * @return int|null|string
     */
    public function getCountryId()
    {
        if ($countryId = $this->getAddress()->getCountryId()) {
            return $countryId;
        }

        return parent::getCountryId();
    }

    /**
     * Return the name of the region for the address being edited.
     *
     * @return string region name
     */
    public function getRegion()
    {
        return $this->getAddress()->getRegion();
    }

    /**
     * Return the id of the region being edited.
     *
     * @return int region id
     */
    public function getRegionId()
    {
        return $this->getAddress()->getRegionId();
    }

    /**
     * Return the title, either editing an existing address, or adding a new one.
     *
     * @return string
     */
    public function getTitle()
    {
        return __('Order Billing Address');
    }

    /**
     * Return the Url to go back.
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('sales/order/view', ['order_id' => $this->getRequest()->getParam('order_id')]);
    }

    /**
     * Return the Url for saving.
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->_urlBuilder->getUrl(
            'mageplaza/order/saveBillingAddress',
            [
                '_secure' => true,
                'order_id' => $this->getRequest()->getParam('order_id'),
                'address_id' => $this->getAddress()->getId()
            ]
        );
    }

    /**
     * @return Address
     */
    public function getHelperAddress()
    {
        return $this->helperAddress;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRegionJson()
    {
        return $this->directoryHelperData->getRegionJson();
    }

    /**
     * @return array|string
     */
    public function getCountriesWithOptionalZip()
    {
        return $this->directoryHelperData->getCountriesWithOptionalZip(true);
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCompanyWidget()
    {
        return $this->getLayout()->createBlock(Company::class);
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTelephoneWidget()
    {
        return $this->getLayout()->createBlock(Telephone::class);
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getFaxWidget()
    {
        return $this->getLayout()->createBlock(Fax::class);
    }
}
