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
use Magento\Sales\Model\Order\Payment\Transaction\ManagerInterface;

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
     * @var ManagerInterface
     */
    private $transactionManager;

    /**
     * TransferFactory constructor.
     * @param ConfigInterface $config
     * @param TransferBuilder $transferBuilder
     * @param TransactionRepositoryInterface $repository
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ManagerInterface $managerInterface
     */
    public function __construct(
        ConfigInterface $config,
        TransferBuilder $transferBuilder,
        TransactionRepositoryInterface $repository,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ManagerInterface $managerInterface
    ) {
        $this->config = $config;
        $this->transferBuilder = $transferBuilder;
        $this->transactionRepository = $repository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->transactionManager = $managerInterface;
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
        return uniqid(sprintf('%s-', (string) $payment->getOrder()->getOrderIncrementId()));
    }

    /**
     * @return mixed
     */
    protected function getGatewayUri()
    {
        return $this->config->getValue('api_test_url') . $this->apiVersionUri() . $this->merchantUri();
    }

    /**
     * @param PaymentDataObjectInterface $payment
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
            ->setMethod(Rest::PUT)
            ->setHeaders(['Content-Type' => 'application/json;charset=UTF-8'])
            ->setBody($request)
            ->setAuthUsername($this->getMerchantUsername())
            ->setAuthPassword($this->config->getValue('api_test_password'))
            ->setUri($this->getUri($payment))
            ->build();
    }
}
