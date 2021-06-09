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


class District extends \Magento\Eav\Model\Entity\Attribute\Source\Table
{
    /**
     * @var \Codilar\CustomerAddressCity\Model\ResourceModel\District\CollectionFactory
     */
    protected $_districtFactory;

    /**
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory
     * @param \Codilar\CustomerAddressCity\Model\ResourceModel\District\CollectionFactory $districtFactory
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory,
        \Codilar\CustomerAddressCity\Model\ResourceModel\District\CollectionFactory $districtFactory
    ) {
        $this->_districtFactory = $districtFactory;
        parent::__construct($attrOptionCollectionFactory, $attrOptionFactory);
    }

    /**
     * @inheritdoc
     */
    public function getAllOptions($withEmpty = true, $defaultValues = false)
    {
        if (!$this->_options) {
            $this->_options = $this->_createDistrictCollection()->load()->toOptionArray();
        }
        return $this->_options;
    }

    /**
     * @return \Codilar\CustomerAddressCity\Model\ResourceModel\District\Collection
     */
    protected function _createDistrictCollection()
    {
        return $this->_districtFactory->create();
    }
}
