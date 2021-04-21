<?php
namespace Codilar\HelloWorld\Model;
use Codilar\Helloworld\Api\Data\PostInterface;
use \Magento\Framework\Model\AbstractModel;
use \Magento\Framework\DataObject\IdentityInterface;
class Post extends AbstractModel implements PostInterface,IdentityInterface
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


    public function getUrlKey()
    {
        return $this->getData('url_key');
    }

    public function getTags()
    {
        return $this->getData('tags');
    }

    public function getUrlContent()
    {
        return $this->getData('post_content');
    }

    public function getCreatedAt()
    {
        return $this->getData('created_at');
    }

    public function getUpdatedAt()
    {
        return $this->getData('updated_at');
    }

    public function setUrlkey($urlKey)
    {
        return $this->setData('url_key', $urlKey);
    }

    public function setTags($tags)
    {
        return $this->setData('tags', $tags);
    }

    public function setUrlContent($urlContent)
    {
        return $this->setData('post_content', $urlContent);
    }

    public function setCreatedAt($creationTime)
    {
        return $this->setData('created_at', $creationTime);
    }

    public function setUpdatedAt($updateTime)
    {
        return $this->setData('updated_at', $updateTime);
    }
}
?>
