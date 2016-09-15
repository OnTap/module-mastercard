<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\MasterCard\Gateway\Http\Vault;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use OnTap\MasterCard\Gateway\Http\Client\Rest;
use OnTap\MasterCard\Gateway\Http\TransferFactory;

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
