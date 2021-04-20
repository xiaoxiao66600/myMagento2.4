<?php


namespace Codilar\HelloWorld\Model\ResourceModel\Post;


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
        $this->_init('Codilar\HelloWorld\Model\Post', 'Codilar\HelloWorld\Model\ResourceModel\Post');
    }

}
