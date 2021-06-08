<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Codilar\CustomerAddressCity\Setup\Patch\Data;

use \Codilar\CustomerAddressCity\Setup\DataInstaller;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Add China States
 */
class AddAllRegionNamesForChina implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var \Codilar\CustomerAddressCity\Setup\DataInstallerFactory
    private $dataInstallerFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param \Codilar\CustomerAddressCity\Setup\DataInstallerFactory $dataInstallerFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Codilar\CustomerAddressCity\Setup\DataInstallerFactory $dataInstallerFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->dataInstallerFactory = $dataInstallerFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        /** @var DataInstaller $dataInstaller */
        $dataInstaller = $this->dataInstallerFactory->create();
        $dataInstaller->addCountryRegions(
            $this->moduleDataSetup->getConnection(),
            $this->getDataForChina(),
            'CN'
        );
    }

    /**
     * China states data.
     *
     * @return array
     */
    private function getDataForChina()
    {
        return [
            ['zh_Hans_CN','CN-SH','上海市'],
            ['zh_Hans_CN','CN-JS','江苏省'],
            ['zh_Hans_CN','CN-ZJ','浙江省'],
            ['zh_Hans_CN','CN-AH','安徽省'],
            ['zh_Hans_CN','CN-FJ','福建省'],
            ['zh_Hans_CN','CN-JX','江西省'],
            ['zh_Hans_CN','CN-SD','山东省'],
            ['zh_Hans_CN','CN-HA','河南省'],
            ['zh_Hans_CN','CN-HB','湖北省'],
            ['zh_Hans_CN','CN-HN','湖南省'],
            ['zh_Hans_CN','CN-BJ','北京市'],
            ['zh_Hans_CN','CN-GD','广东省'],
            ['zh_Hans_CN','CN-GX','广西壮族自治区'],
            ['zh_Hans_CN','CN-HI','海南省'],
            ['zh_Hans_CN','CN-CQ','重庆市'],
            ['zh_Hans_CN','CN-SC','四川省'],
            ['zh_Hans_CN','CN-GZ','贵州省'],
            ['zh_Hans_CN','CN-YN','云南省'],
            ['zh_Hans_CN','CN-XZ','西藏自治区'],
            ['zh_Hans_CN','CN-SX','陕西省'],
            ['zh_Hans_CN','CN-GS','甘肃省'],
            ['zh_Hans_CN','CN-TJ','天津市'],
            ['zh_Hans_CN','CN-QH','青海省'],
            ['zh_Hans_CN','CN-NX','宁夏回族自治区'],
            ['zh_Hans_CN','CN-XJ','新疆维吾尔自治区'],
            ['zh_Hans_CN','CN-HE','河北省'],
            ['zh_Hans_CN','CN-SN','山西省'],
            ['zh_Hans_CN','CN-NM','内蒙古自治区'],
            ['zh_Hans_CN','CN-LN','辽宁省'],
            ['zh_Hans_CN','CN-JL','吉林省'],
            ['zh_Hans_CN','CN-HL','黑龙江省'],
            ['zh_Hans_CN','CN-HK','香港特别行政区'],
            ['zh_Hans_CN','CN-MO','澳门特别行政区'],
            ['zh_Hans_CN','CN-TW','台湾省'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
