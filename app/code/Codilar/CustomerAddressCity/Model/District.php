<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codilar\CustomerAddressCity\Model;

class District extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Codilar\CustomerAddressCity\Model\ResourceModel\District::class);
    }

    /**
     * Load city by code
     *
     * @param string $code
     * @return $this
     */
    public function loadByCode($code)
    {
        if ($code) {
            $this->_getResource()->loadByCode($this, $code);
        }
        return $this;
    }

}
