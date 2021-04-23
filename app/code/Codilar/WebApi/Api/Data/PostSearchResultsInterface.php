<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codilar\WeApi\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for cms block search results.
 * @api
 * @since 100.0.2
 */
interface PostSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get posts list.
     *
     * @return \Codilar\WeApi\Api\Data\PostInterface[]
     */
    public function getItems();

    /**
     * Set posts list.
     *
     * @param \Codilar\WeApi\Api\Data\PostInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
