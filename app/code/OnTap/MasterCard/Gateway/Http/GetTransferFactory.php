<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\MasterCard\Gateway\Http;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Http\TransferInterface;

class GetTransferFactory extends TransferFactory
{
    /**
     * @var string
     */
    protected $httpMethod = 'GET';

    /**
     * @param array $request
     * @return string
     */
    protected function getUri($request)
    {
        $orderId = $request['order_id'];
        $txnId = $request['transaction_id'];
        return $this->getGatewayUri() . 'order/'.$orderId.'/transaction/'.$txnId;
    }

    /**
     * @param array $request
     * @param PaymentDataObjectInterface $payment
     * @return TransferInterface
     */
    public function create(array $request, PaymentDataObjectInterface $payment)
    {
        return $this->transferBuilder
            ->setMethod($this->httpMethod)
            ->setHeaders(['Content-Type' => 'application/json;charset=UTF-8'])
            ->setBody((new \stdClass))
            ->setAuthUsername($this->getMerchantUsername())
            ->setAuthPassword($this->config->getMerchantPassword())
            ->setUri($this->getUri($request))
            ->build();
    }
}
