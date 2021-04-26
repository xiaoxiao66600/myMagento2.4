<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codilar\WebApi\Api\Data;

/**
 * CMS block interface.
 * @api
 * @since 100.0.2
 */
interface PostInterface
{

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get url_key
     *
     * @return string
     */
    public function getUrlKey();

    /**
     * Get tags
     *
     * @return string|null
     */
    public function getTags();

    /**
     * Get post_content
     *
     * @return string|null
     */
    public function getPostContent();

    /**
     * Get creation time
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Get update time
     *
     * @return string|null
     */
    public function getUpdatedAt();



    /**
     * Set ID
     *
     * @param int $id
     * @return PostInterface
     */
    public function setId($id);

    /**
     * Set url_key
     *
     * @param string $urlKey
     * @return PostInterface
     */
    public function setUrlKey($urlKey);


    /**
     * Set tag
     *
     * @param string $tags
     * @return PostInterface
     */
    public function setTags($tags);

    /**
     * Set post_content
     *
     * @param string $postContent
     * @return PostInterface
     */
    public function setPostContent($postContent);

    /**
     * Set creation time
     *
     * @param string $createdAt
     * @return PostInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Set update time
     *
     * @param string $updatedAt
     * @return PostInterface
     */
    public function setUpdatedAt($updatedAt);

}
