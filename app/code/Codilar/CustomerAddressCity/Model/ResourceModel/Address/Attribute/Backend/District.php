<?php

namespace Codilar\CustomerAddressCity\Model\ResourceModel\Address\Attribute\Backend;

class District extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @var \Codilar\CustomerAddressCity\Model\DistrictFactory
     */
    protected $_districtFactory;

    /**
     * @param \Codilar\CustomerAddressCity\Model\DistrictFactory $districtFactory
     */
    public function __construct(\Codilar\CustomerAddressCity\Model\DistrictFactory $districtFactory)
    {
        $this->_districtFactory = $districtFactory;
    }

    /**
     * Prepare object for save
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    public function beforeSave($object)
    {
        $district = $object->getData('district');
        if (is_numeric($district)) {
            $districtModel = $this->_createDistrictInstance();
            $districtModel->load($district);
            if ($districtModel->getId() && $object->getRegionId() == $districtModel->getRegionId()) {
                $object->setData('district_id',$districtModel->getId())->setCity($districtModel->getData('default_name'));
            }
        }
        return $this;
    }

    /**
     * @return \Codilar\CustomerAddressCity\Model\District
     */
    protected function _createDistrictInstance()
    {
        return $this->_districtFactory->create();
    }
}
