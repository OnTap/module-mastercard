<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Gateway\Http;

use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Gateway\ConfigInterface;
use OnTap\MasterCard\Gateway\Http\Client\Rest;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;

/**
 * Class TransferFactory
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TransferFactory implements TransferFactoryInterface
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var string
     */
    protected $httpMethod = Rest::PUT;

    /**
     * @var TransferBuilder
     */
    protected $transferBuilder;

    /**
     * TransferFactory constructor.
     * @param ConfigInterface $config
     * @param TransferBuilder $transferBuilder
     */
    public function __construct(
        ConfigInterface $config,
        TransferBuilder $transferBuilder
    ) {
        $this->config = $config;
        $this->transferBuilder = $transferBuilder;
    }

    /**
     * @return string
     */
    protected function getMerchantUsername()
    {
        return 'merchant.' . $this->config->getMerchantId();
    }

    /**
     * @return string
     */
    protected function apiVersionUri()
    {
        return 'version/' . $this->config->getValue('api_version') . '/';
    }

    /**
     * @return string
     */
    protected function merchantUri()
    {
        return 'merchant/' . $this->config->getMerchantId() . '/';
    }

    /**
     * Generate a new transactionId
     * @param PaymentDataObjectInterface $payment
     * @return string
     */
    protected function createTxnId(PaymentDataObjectInterface $payment)
    {
        return uniqid(sprintf('%s-', (string) $payment->getOrder()->getOrderIncrementId()));
    }

    /**
     * @return mixed
     */
    protected function getGatewayUri()
    {
        return $this->config->getApiUrl() . $this->apiVersionUri() . $this->merchantUri();
    }

    /**
     * @param PaymentDataObjectInterface|array $payment
     * @return string
     */
    protected function getUri(PaymentDataObjectInterface $payment)
    {
        $orderId = $payment->getOrder()->getOrderIncrementId();
        $txnId = $this->createTxnId($payment);

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
            ->setBody($request)
            ->setAuthUsername($this->getMerchantUsername())
            ->setAuthPassword($this->config->getMerchantPassword())
            ->setUri($this->getUri($payment))
            ->build();
    }
}
