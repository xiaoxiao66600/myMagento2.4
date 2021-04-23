<?php

namespace Codilar\WebApi\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * CMS page CRUD interface.
 * @api
 * @since 100.0.2
 */
interface PostRepositoryInterface
{

    /**
     * Retrieve pages matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Codilar\WebApi\Api\Data\PostSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

}
