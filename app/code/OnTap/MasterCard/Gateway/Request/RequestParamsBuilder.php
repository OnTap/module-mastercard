<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\MasterCard\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

class RequestParamsBuilder implements BuilderInterface
{
    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        return [
            'order_id' => $buildSubject['order_id'],
            'transaction_id' => $buildSubject['transaction_id']
        ];
    }
}
