<?php


namespace Codilar\HelloWorld\Controller\Adminhtml\Post;
use Magento\Backend\App\Action;
use Codilar\HelloWorld\Model\Post;

class NewAction extends Action
{
    //新增
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
        $contactDatas = $this->getRequest()->getParam('post');
        if(is_array($contactDatas)) {
            $contact = $this->_objectManager->create(Post::class);
            $contact->setData($contactDatas)->save();
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/index');
        }
    }
}
