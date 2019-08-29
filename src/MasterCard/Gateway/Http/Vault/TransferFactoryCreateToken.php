<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
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
        $storeId = $payment->getOrder()->getStoreId();
        return $this->getGatewayUri($storeId) . 'token/';
    }
}
