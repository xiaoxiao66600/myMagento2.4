<?php


namespace Codilar\WebApi\Model\ResourceModel\Post;


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
        $this->_init('Codilar\WebApi\Model\Post', 'Codilar\WebApi\Model\ResourceModel\Post');
    }

}
