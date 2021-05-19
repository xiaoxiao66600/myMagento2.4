<?php
/**
 * Copyright 2020  Walter
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
declare(strict_types=1);

namespace Codilar\Email\Model\Api;
use Codilar\Email\Api\SendManagementInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class SendManagement implements SendManagementInterface
{

    /**
     * @var Http
     */
    private $request;
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var SenderResolverInterface
     */
    private $senderResolver;
    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    public function __construct(
        Http $request,
        SerializerInterface $serializer,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        SenderResolverInterface $senderResolver = null
    )
    {

        $this->request = $request;
        $this->serializer = $serializer;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->senderResolver = $senderResolver ?? ObjectManager::getInstance()->get(SenderResolverInterface::class);
        $this->transportBuilder = $transportBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function postSend()
    {
        $body = $this->serializer->unserialize($this->request->getContent());
        $email = $body['email'];
        $storeId = $this->storeManager->getStore()->getId();
        $templateId = $this->scopeConfig->getValue('customer/account_information/change_email_template', ScopeInterface::SCOPE_STORE, $storeId);

        /** @var array $from */
        $from = $this->senderResolver->resolve(
            $this->scopeConfig->getValue('customer/create_account/email_identity', ScopeInterface::SCOPE_STORE, $storeId),
            $storeId
        );
        $emailMsg = 'This is test email content';
        $emailTempVariables['emailMsg'] = $emailMsg;
        $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
            ->setTemplateOptions(['area' => 'frontend', 'store' => $storeId])
            ->setTemplateVars($emailTempVariables)
            ->setFrom($from)
            ->addTo($email)
            ->getTransport();

        $transport->sendMessage();
    }

}

