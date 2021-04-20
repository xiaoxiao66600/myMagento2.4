<?php
namespace Codilar\HelloWorld\Model;
use \Magento\Framework\Model\AbstractModel;
use \Magento\Framework\DataObject\IdentityInterface;
class Post extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'codilar_helloworld_post';
    protected function _construct()
    {
        $this->_init('Codilar\HelloWorld\Model\ResourceModel\Post');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getDefaultValues()
    {
        $values = [];

        return $values;
    }
}
?>
