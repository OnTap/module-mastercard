<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\Tns\Gateway\Http;

use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Gateway\ConfigInterface;
use OnTap\Tns\Gateway\Http\Client\Rest;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;

use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;

class TransferFactory implements TransferFactoryInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var TransferBuilder
     */
    private $transferBuilder;

    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param ConfigInterface $config
     * @param TransferBuilder $transferBuilder
     */
    public function __construct(
        ConfigInterface $config,
        TransferBuilder $transferBuilder,
        TransactionRepositoryInterface $repository,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->config = $config;
        $this->transferBuilder = $transferBuilder;
        $this->transactionRepository = $repository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @return string
     */
    protected function getMerchantUsername()
    {
        return 'merchant.' . $this->config->getValue('api_test_username');
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
        return 'merchant/' . $this->config->getValue('api_test_username') . '/';
    }

    /**
     * Generate a new transactionId
     * @param PaymentDataObjectInterface $payment
     * @return string
     */
    protected function createTxnId(PaymentDataObjectInterface $payment)
    {
        $orderId = (string) $payment->getOrder()->getOrderIncrementId();

        $filters[] = $this->filterBuilder->setField('payment_id')
            ->setValue($payment->getPayment()->getId())
            ->create();

        $searchCriteria = $this->searchCriteriaBuilder->addFilters($filters)
            ->create();

        $count = $this->transactionRepository->getList($searchCriteria)->getTotalCount();
        $count++;

        return $orderId . '-' . (string) $count;
    }

    /**
     * @param array $request
     * @param PaymentDataObjectInterface $payment
     * @return TransferInterface
     */
    public function create(array $request, PaymentDataObjectInterface $payment)
    {
        $gatewayUrl = $this->config->getValue('api_test_url');
        $orderId = $payment->getOrder()->getOrderIncrementId();
        $txnId = $this->createTxnId($payment);

        return $this->transferBuilder
            ->setMethod(Rest::PUT)
            ->setHeaders(['Content-Type' => 'application/json;charset=UTF-8'])
            ->setBody($request)
            ->setAuthUsername($this->getMerchantUsername())
            ->setAuthPassword($this->config->getValue('api_test_password'))
            ->setUri($gatewayUrl . $this->apiVersionUri() . $this->merchantUri() . 'order/'.$orderId.'/transaction/'.$txnId)
            ->build();
    }
}
