<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\Tns\Controller\Threedsecure;

use Magento\Framework\App\ResponseInterface;

class Response extends \Magento\Framework\App\Action\Action
{
    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        print "<script>window.parent.tnsThreeDSecureClose();</script>";
        die();
    }
}
