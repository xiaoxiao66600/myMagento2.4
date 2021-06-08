<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codilar\CustomerAddressCity\Model\ResourceModel;


/**
 * City Resource Model
 *
 * @api
 * @since 100.0.2
 */
class City extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->_localeResolver = $localeResolver;
    }

    /**
     * Define main and locale region name tables
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('codilar_directory_country_city', 'city_id');
    }

    /**
     * Loads city by city code
     *
     * @param \Codilar\CustomerAddressCity\Model\City $city
     * @param string $cityCode
     * @return $this
     */
    public function loadByCode(\Codilar\CustomerAddressCity\Model\City $city, $cityCode)
    {
        return $this->loadByField($city, (string)$cityCode, 'code');
    }


    /**
     * Load object by code or default name
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param string $value
     * @param string $field
     * @return $this
     */
    private function loadByField($object, $value,$field){
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            ['city' => $this->getMainTable()]
        )->where(
            "city.{$field} = ?",
            $value
        );
        $data = $connection->fetchRow($select);
        if ($data) {
            $object->setData($data);
        }
        $this->_afterLoad($object);
        return $this;
    }

}
