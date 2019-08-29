<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
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
     * @param null $storeId
     * @return string
     */
    protected function getRequestUri($request, $storeId = null)
    {
        $orderId = $request['order_id'];
        $txnId = $request['transaction_id'];
        return $this->getGatewayUri($storeId) . 'order/' . $orderId . '/transaction/' . $txnId;
    }

    /**
     * @param array $request
     * @param PaymentDataObjectInterface $payment
     * @return TransferInterface
     */
    public function create(array $request, PaymentDataObjectInterface $payment)
    {
        $storeId = $payment->getOrder()->getStoreId();
        return $this->transferBuilder
            ->setMethod($this->httpMethod)
            ->setHeaders(['Content-Type' => 'application/json;charset=UTF-8'])
            ->setBody((new \stdClass()))
            ->setAuthUsername($this->getMerchantUsername($storeId))
            ->setAuthPassword($this->config->getMerchantPassword($storeId))
            ->setUri($this->getRequestUri($request, $storeId))
            ->build();
    }
}
