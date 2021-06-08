<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Codilar\CustomerAddressCity\Setup\Patch\Data;

use Codilar\CustomerAddressCity\Model\ResourceModel\Address\Attribute\Backend\District;
use Magento\Customer\Model\Indexer\Address\AttributeProvider;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Eav\Model\Entity\Attribute\SetFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Codilar\CustomerAddressCity\Model\ResourceModel\Address\Attribute\Backend\City;

class ModifyCitiesCustomerAddressAttribute implements DataPatchInterface, PatchRevertableInterface
{

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * @var CustomerSetup
     */
    private $customerSetupFactory;
    /**
     * @var SetFactory
     */
    private $attributeSetFactory;

    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory $customerSetupFactory
     * @param SetFactory $attributeSetFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory,
        SetFactory $attributeSetFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $customerEntity = $customerSetup->getEavConfig()->getEntityType(AttributeProvider::ENTITY);
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        /** @var $attributeSet Set */
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $customerSetup->updateAttribute(
            AttributeProvider::ENTITY,
            'city',
            'frontend_input',
            'text',
            110
        );
        $customerSetup->updateAttribute(
            AttributeProvider::ENTITY,
            'city',
            'frontend_label',
            'City',
            110
        );
        $customerSetup->updateAttribute(
            AttributeProvider::ENTITY,
            'city',
            'backend_model',
            City::class
        );
        $customerSetup->updateAttribute(
            AttributeProvider::ENTITY,
            'city',
            'is_required',
            false,
            110
        );

        $customerSetup->updateAttribute(
            AttributeProvider::ENTITY,
            'city_id',
            'frontend_input',
            'hidden',
            110
        );
        $customerSetup->updateAttribute(
            AttributeProvider::ENTITY,
            'city_id',
            'frontend_label',
            'City',
            110
        );
        $customerSetup->updateAttribute(
            AttributeProvider::ENTITY,
            'city_id',
            'source_model',
            \Codilar\CustomerAddressCity\Model\Customer\Address\Attribute\Source\City::class,
            110
        );
        $customerSetup->updateAttribute(
            AttributeProvider::ENTITY,
            'city_id',
            'is_required',
            false,
            110
        );
        //district
        $customerSetup->updateAttribute(
            AttributeProvider::ENTITY,
            'district',
            'frontend_input',
            'text',
            111
        );
        $customerSetup->updateAttribute(
            AttributeProvider::ENTITY,
            'district',
            'frontend_label',
            'District',
            111
        );
        $customerSetup->updateAttribute(
            AttributeProvider::ENTITY,
            'district',
            'backend_model',
            District::class
        );
        $customerSetup->updateAttribute(
            AttributeProvider::ENTITY,
            'district',
            'is_required',
            false,
            111
        );
        $customerSetup->updateAttribute(
            AttributeProvider::ENTITY,
            'district_id',
            'frontend_input',
            'hidden',
            111
        );
        $customerSetup->updateAttribute(
            AttributeProvider::ENTITY,
            'district_id',
            'frontend_label',
            'District',
            110
        );
        $customerSetup->updateAttribute(
            AttributeProvider::ENTITY,
            'district_id',
            'source_model',
            \Codilar\CustomerAddressCity\Model\Customer\Address\Attribute\Source\District::class,
            111
        );
        $customerSetup->updateAttribute(
            AttributeProvider::ENTITY,
            'district_id',
            'is_required',
            false,
            111
        );



        $this->moduleDataSetup->getConnection()->endSetup();
    }


    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [

        ];
    }

    /**
     * @inheritDoc
     */
    public function revert()
    {
        // TODO: Implement revert() method.
    }
}

