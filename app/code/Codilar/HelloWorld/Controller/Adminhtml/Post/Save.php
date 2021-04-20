<?php
/**
 *
 */
declare(strict_types=1);

namespace Codilar\HelloWorld\Controller\Adminhtml\Post;

use Codilar\HelloWorld\Model\Post;
use Magento\Framework\Exception\LocalizedException;

class Save extends \Magento\Backend\App\Action
{

    protected $dataPersistor;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
    ) {
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getParam('post');
        $model = $this->_objectManager->create(Post::class);
        if (isset($data['id']) && $id=$data['id']) {//编辑
            $model = $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This Post no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
            $model = $model->setData($data);
            $this->saveData($model);
            return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
        }else{//新增
            $model = $model->setData($data);
            $this->saveData($model);
        }
        return $resultRedirect->setPath('*/*/index');
    }

    private function saveData($model){
        try {
            $model->save();
            $this->messageManager->addSuccessMessage(__('You saved the Post.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Post.'));
        }
    }
}

