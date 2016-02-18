<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\Tns\Gateway\Http\Vault;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use OnTap\Tns\Gateway\Http\Client\Rest;
use OnTap\Tns\Gateway\Http\TransferFactory;

class TransferFactoryCreateToken extends TransferFactory
{
    /**
     * @var string
     */
    protected $httpMethod = Rest::POST;

    /**
     * @param PaymentDataObjectInterface $payment
     * @return string
     */
    protected function getUri(PaymentDataObjectInterface $payment)
    {
        return $this->getGatewayUri() . 'token/';
    }
}
