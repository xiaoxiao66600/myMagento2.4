<?php


namespace Codilar\WeApi\Model\ResourceModel\Post;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Codilar\WeApi\Model\Post', 'Codilar\WeApi\Model\ResourceModel\Post');
    }

}
