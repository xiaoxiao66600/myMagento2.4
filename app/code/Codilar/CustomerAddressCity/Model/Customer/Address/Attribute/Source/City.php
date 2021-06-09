<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Customer region attribute source
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Codilar\CustomerAddressCity\Model\Customer\Address\Attribute\Source;


class City extends \Magento\Eav\Model\Entity\Attribute\Source\Table
{
    /**
     * @var \Codilar\CustomerAddressCity\Model\ResourceModel\City\CollectionFactory
     */
    protected $_cityFactory;

    /**
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory
     * @param \Codilar\CustomerAddressCity\Model\ResourceModel\City\CollectionFactory $cityFactory
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory,
        \Codilar\CustomerAddressCity\Model\ResourceModel\City\CollectionFactory $cityFactory
    ) {
        $this->_cityFactory = $cityFactory;
        parent::__construct($attrOptionCollectionFactory, $attrOptionFactory);
    }

    /**
     * @inheritdoc
     */
    public function getAllOptions($withEmpty = true, $defaultValues = false)
    {
        if (!$this->_options) {
            $this->_options = $this->_createCityCollection()->load()->toOptionArray();
        }
        return $this->_options;
    }

    /**
     * @return \Codilar\CustomerAddressCity\Model\ResourceModel\City\Collection
     */
    protected function _createCityCollection()
    {
        return $this->_cityFactory->create();
    }
}
