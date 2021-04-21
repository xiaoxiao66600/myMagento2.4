<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codilar\HelloWorld\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for cms block search results.
 * @api
 * @since 100.0.2
 */
interface PostSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get blocks list.
     *
     * @return \Codilar\HelloWorld\Api\Data\PostInterface[]
     */
    public function getItems();

    /**
     * Set blocks list.
     *
     * @param \Codilar\HelloWorld\Api\Data\PostInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
