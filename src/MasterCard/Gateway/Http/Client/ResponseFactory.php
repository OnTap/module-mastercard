<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Gateway\Http\Client;

use Zend_Http_Response;

/**
 * Class ResponseFactory
 */
class ResponseFactory
{
    /**
     * Create a new Zend_Http_Response object from a string
     *
     * @param string $response
     * @return Zend_Http_Response
     */
    public function create($response)
    {
        return Zend_Http_Response::fromString($response);
    }
}
