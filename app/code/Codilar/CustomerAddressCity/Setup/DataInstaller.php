<?php

namespace Codilar\CustomerAddressCity\Setup;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Directory\Model\RegionFactory;
use Magento\Directory\Model\CountryFactory;
use Codilar\CustomerAddressCity\Model\CityFactory;
use Codilar\CustomerAddressCity\Model\DistrictFactory;

/**
 * Class DataInstaller
 * @package Codilar\CustomerAddressCity\Setup
 */
class DataInstaller
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;
    /**
     * @var RegionFactory
     */
    private $regionFactory;
    /**
     * @var CountryFactory
     */
    private $countryFactory;
    /**
     * @var CityFactory
     */
    private $cityFactory;
    /**
     * @var DistrictFactory
     */
    private $districtFactory;

    /**
     * DatInstaller constructor.
     * @param ResourceConnection $resourceConnection
     * @param RegionFactory $regionFactory
     * @param CountryFactory $countryFactory
     * @param CityFactory $cityFactory
     * @param DistrictFactory $districtFactory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        RegionFactory $regionFactory,
        CountryFactory $countryFactory,
        CityFactory $cityFactory,
        DistrictFactory $districtFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->regionFactory = $regionFactory;
        $this->countryFactory = $countryFactory;
        $this->cityFactory = $cityFactory;
        $this->districtFactory = $districtFactory;
    }

    /**
     * @param AdapterInterface $adapter
     * @param array $data
     * @param $locale
     */
    public function addCountryRegions(AdapterInterface $adapter, array $data,$country)
    {
        /**
         * Fill table directory/country_region_name for locale
         */
        foreach ($data as $row) {
            $countryId = $this->countryFactory->create()->loadByCode($country)->getId();//查询国家
            $code = $row[1];
            $regionId = $this->regionFactory->create()->loadByCode($code,$countryId)->getId();
            $bind = ['locale' => $row[0], 'region_id' => $regionId, 'name' => $row[2]];
            $adapter->insert($this->resourceConnection->getTableName('directory_country_region_name'), $bind);
        }
    }

    /**
     * 添加城市
     * @param AdapterInterface $adapter
     * @param array $data
     */
    public function addCountryCity(AdapterInterface $adapter, array $data,$country){
        foreach($data as $row){
            $regionId = $this->regionFactory->create()->loadByCode($row[1],$country)->getId();
            $cityBind = ['region_id'=>$regionId,'code'=>$row[0],'default_name'=>$row[2]];
            $adapter->insert($this->resourceConnection->getTableName('codilar_directory_country_city'), $cityBind);
        }
    }

    /**
     * 添加城市多语言
     * @param AdapterInterface $adapter
     * @param array $data
     */
    public function addCountryCityNames(AdapterInterface $adapter, array $data){
        foreach($data as $row){
            $cityId = $this->cityFactory->create()->loadByCode($row[0])->getId();
            $cityNameBind = ['city_id'=>$cityId,'locale'=>$row[1],'name'=>$row[2]];
            $adapter->insert($this->resourceConnection->getTableName('codilar_directory_country_city_name'), $cityNameBind);
        }
    }

    /**
     * 添加地区
     * @param AdapterInterface $adapter
     * @param array $data
     */
    public function addCountryDistrict(AdapterInterface $adapter, array $data){
        foreach($data as $row){
            $cityId = $this->cityFactory->create()->loadByCode($row[0])->getId();
            $districtBind = ['city_id'=>$cityId,'code'=>$row[1],'default_name'=>$row[2]];
            $adapter->insert($this->resourceConnection->getTableName('codilar_directory_country_district'), $districtBind);
        }
    }
    /**
     * 添加地区多语言
     * @param AdapterInterface $adapter
     * @param array $data
     */
    public function addCountryDistrictNames(AdapterInterface $adapter, array $data){
        foreach($data as $row){
            $districtId =  $this->districtFactory->create()->loadByCode($row[0])->getId();
            $districtBind = ['district_id'=>$districtId,'locale'=>$row[1],'name'=>$row[2]];
            $adapter->insert($this->resourceConnection->getTableName('codilar_directory_country_district_name'), $districtBind);
        }
    }
}
