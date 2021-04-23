<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codilar\HelloWorld\Model;

use Codilar\Helloworld\Api\Data\PostInterfaceFactory;
use Codilar\HelloWorld\Api\PostRepositoryInterface;
use Codilar\HelloWorld\Api\Data;
use Codilar\HelloWorld\Model\ResourceModel\Post as ResourceBlock;
use Codilar\HelloWorld\Model\ResourceModel\Post\CollectionFactory as PostCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class BlockRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PostRepository implements PostRepositoryInterface
{
    /**
     * @var ResourceBlock
     */
    protected $resource;

    /**
     * @var PostFactory
     */
    protected $postFactory;

    /**
     * @var PostCollectionFactory
     */
    protected $postCollectionFactory;

    /**
     * @var Data\PostSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var Codilar\HelloWorld\Api\Data\PostInterfaceFactory
     */
    protected $postInterfaceFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param ResourceBlock $resource
     * @param PostFactory $postFactory
     * @param Data\PostInterfaceFactory $postInterfaceFactory
     * @param PostCollectionFactory $postCollectionFactory
     * @param Data\PostSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceBlock $resource,
        PostFactory $postFactory,
        PostInterfaceFactory $postInterfaceFactory,
        PostCollectionFactory $postCollectionFactory,
        Data\PostSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->resource = $resource;
        $this->postFactory = $postFactory;
        $this->postCollectionFactory = $postCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->postInterfaceFactory = $postInterfaceFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
    }


    /**
     * Load Block data collection by given search criteria
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return Codilar\HelloWorld\Api\Data\PostSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        /** @var Codilar\HelloWorld\Model\ResourceModel\Post\Collection $collection */
        $collection = $this->postCollectionFactory->create();
        $this->collectionProcessor->process($criteria, $collection);

        /** @var Data\PostSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
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
                'Codilar\HelloWorld\Model\Api\SearchCriteria\PostCollectionProcessor'
            );
        }
        return $this->collectionProcessor;
    }

}
