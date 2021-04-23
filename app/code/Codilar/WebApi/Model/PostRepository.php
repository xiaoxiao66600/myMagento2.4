<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codilar\WebApi\Model;


use Codilar\WebApi\Api\Data\PostSearchResultsInterfaceFactory;
use Codilar\WebApi\Model\ResourceModel\Post\CollectionFactory;
use Codilar\WebApi\Api\PostRepositoryInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor;


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

    public function __construct(
        CollectionFactory $collectionFactory,
        PostSearchResultsInterfaceFactory $resultsInterfaceFactory,
        CollectionProcessor $collectionProcessor = null
    ) {

        $this->collectionFactory = $collectionFactory;
        $this->resultsInterfaceFactory = $resultsInterfaceFactory;
        $this->collectionProcessor = $collectionProcessor?:$this->getCollectionProcessor();
    }


    /**
     * Load Block data collection by given search criteria
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return Codilar\WeApi\Api\Data\PostSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        /** @var Codilar\WeApi\Model\ResourceModel\Post\Collection $collection */
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
                'Codilar\WeApi\Model\Api\SearchCriteria\PostCollectionProcessor'
            );
        }
        return $this->collectionProcessor;
    }

}
