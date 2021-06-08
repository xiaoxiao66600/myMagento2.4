<?php

namespace Codilar\CustomerAddressCity\Model\ResourceModel\Address\Attribute\Backend;

class City extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @var \Codilar\CustomerAddressCity\Model\CityFactory
     */
    protected $_cityFactory;

    /**
     * @param \Codilar\CustomerAddressCity\Model\CityFactory $cityFactory
     */
    public function __construct(\Codilar\CustomerAddressCity\Model\CityFactory $cityFactory)
    {
        $this->_cityFactory = $cityFactory;
    }

    /**
     * Prepare object for save
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    public function beforeSave($object)
    {
        $city = $object->getData('city');
        if (is_numeric($city)) {
            $cityModel = $this->_createCityInstance();
            $cityModel->load($city);
            if ($cityModel->getId() && $object->getRegionId() == $cityModel->getRegionId()) {
                $object->setData('city_id',$cityModel->getId())->setCity($cityModel->getData('default_name'));
            }
        }
        return $this;
    }

    /**
     * @return \Codilar\CustomerAddressCity\Model\City
     */
    protected function _createCityInstance()
    {
        return $this->_cityFactory->create();
    }
}
