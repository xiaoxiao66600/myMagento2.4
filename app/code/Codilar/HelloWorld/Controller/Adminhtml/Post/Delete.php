<?php


namespace Codilar\HelloWorld\Controller\Adminhtml\Post;

use Codilar\HelloWorld\Model\Post;
use Magento\Backend\App\Action;

class Delete extends \Magento\Backend\App\Action
{
    public function execute()
    {

        $id = $this->getRequest()->getParam('id');
        if (!($contact = $this->_objectManager->create(Post::class)->load($id))) {
            $this->messageManager->addError(__('Unable to proceed. Please, try again.'));
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/index', array('_current' => true));
        }
        try{
            $contact->delete();
            $this->messageManager->addSuccess(__('Your post has been deleted !'));
        } catch (Exception $e) {
            $this->messageManager->addError(__('Error while trying to delete contact: '));
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/index', array('_current' => true));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/index', array('_current' => true));
    }
}
