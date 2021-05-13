<?php


namespace Codilar\Events\Controller\Index;


class test extends \Magento\Framework\App\Action\Action
{
    public function execute()
    {
        $textDisplay = new \Magento\Framework\DataObject(array('text' => 'Hello World'));
        $this->_eventManager->dispatch('Codilar_Events_display_text', ['mp_text' => $textDisplay]);
        echo $textDisplay->getText();
        exit;
    }
}
