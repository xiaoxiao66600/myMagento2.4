<?php

declare(strict_types=1);

namespace Codilar\Email\Api;

interface SendManagementInterface
{

    /**
     * @return mixed
     */
    public function postSend();
}

