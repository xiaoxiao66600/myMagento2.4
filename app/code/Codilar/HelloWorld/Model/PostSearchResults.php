<?php


namespace Codilar\HelloWorld\Model;

use Codilar\HelloWorld\Api\Data\PostSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

/**
 * Service Data Object with Block search results.
 */
class PostSearchResults extends SearchResults implements PostSearchResultsInterface
{
}
