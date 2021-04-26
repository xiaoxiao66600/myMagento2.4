<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codilar\WebApi\Model;


use Codilar\WebApi\Api\Data\PostInterface;
use Codilar\WebApi\Api\Data\PostSearchResultsInterfaceFactory;
use Codilar\WebApi\Model\ResourceModel\Post\CollectionFactory;
use Codilar\WebApi\Api\PostRepositoryInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Store\Model\StoreManagerInterface;


class PostRepository implements PostRepositoryInterface
{

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var PostSearchResultsInterfaceFactory
     */
    private $resultsInterfaceFactory;
    /**
     * @var CollectionProcessor|null
     */
    private $collectionProcessor;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var ResourceModel\Post
     */
    private $postResource;

    public function __construct(
        CollectionFactory $collectionFactory,
        PostSearchResultsInterfaceFactory $resultsInterfaceFactory,
        StoreManagerInterface $storeManager,
        \Codilar\WebApi\Model\ResourceModel\Post $postResource,
        CollectionProcessor $collectionProcessor = null
    ) {

        $this->collectionFactory = $collectionFactory;
        $this->resultsInterfaceFactory = $resultsInterfaceFactory;
        $this->collectionProcessor = $collectionProcessor?:$this->getCollectionProcessor();
        $this->storeManager = $storeManager;
        $this->postResource = $postResource;
    }


    /**
     * Load Post data collection by given search criteria
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return Codilar\WebApi\Api\Data\PostSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        /** @var Codilar\WebApi\Model\ResourceModel\Post\Collection $collection */
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($criteria, $collection);
        /** @var Data\PostSearchResultsInterface $searchResults */
        $searchResults = $this->resultsInterfaceFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * Retrieve collection processor
     *
     * @deprecated 102.0.0
     * @return CollectionProcessorInterface
     */
    private function getCollectionProcessor()
    {
        if (!$this->collectionProcessor) {
            $this->collectionProcessor = \Magento\Framework\App\ObjectManager::getInstance()->get(
                'Codilar\WebApi\Model\Api\SearchCriteria\PostCollectionProcessor'
            );
        }
        return $this->collectionProcessor;
    }

    /**
     * Save Post data
     *
     * @param Codilar\WebApi\Api\Data\PostInterface $post
     * @return $post
     * @throws CouldNotSaveException
     */
    public function save(PostInterface $post)
    {
        if(!$post->getUrlKey()){
            throw new CouldNotSaveException(__("url_key required"));
        }
        if(!$post->getTags()){
            throw new CouldNotSaveException(__("tags required"));
        }
        try {
            $this->postResource->save($post);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $post;
    }

}
