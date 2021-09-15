<?php
/**
 * Copyright (c) 2016-2021 Mastercard
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OnTap\MasterCard\Controller\Webhook;

use Exception;
use InvalidArgumentException;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\LoggerFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use OnTap\MasterCard\Gateway\Config\Config;
use Psr\Log\LoggerInterface;

class Response extends Action implements CsrfAwareActionInterface
{
    const X_HEADER_SECRET = 'X-Notification-Secret';
    const X_HEADER_ATTEMPT = 'X-Notification-Attempt';
    const X_HEADER_ID = 'X-Notification-Id';
    const LOG_TYPE = 'webhook';

    /**
     * @var RawFactory
     */
    protected $rawFactory;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var TransactionRepositoryInterface
     */
    protected $transactionRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var LoggerFactory
     */
    protected $loggerFactory;

    /**
     * @var array
     */
    protected $configProviders;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var PaymentDataObjectFactory
     */
    protected $paymentDataObjectFactory;

    /**
     * @var CommandPoolInterface
     */
    protected $commandPool;

    /**
     * @var Json
     */
    protected $json;

    /**
     * Response constructor.
     * @param Context $context
     * @param RawFactory $rawFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param TransactionRepositoryInterface $transactionRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param LoggerFactory $loggerFactory
     * @param LoggerInterface $logger
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param CommandPoolInterface $commandPool
     * @param Json $json
     * @param Config[] $configProviders
     */
    public function __construct(
        Context $context,
        RawFactory $rawFactory,
        OrderRepositoryInterface $orderRepository,
        TransactionRepositoryInterface $transactionRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        LoggerFactory $loggerFactory,
        LoggerInterface $logger,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        CommandPoolInterface $commandPool,
        Json $json,
        array $configProviders = []
    ) {
        parent::__construct($context);
        $this->rawFactory = $rawFactory;
        $this->orderRepository = $orderRepository;
        $this->transactionRepository = $transactionRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->loggerFactory = $loggerFactory;
        $this->logger = $logger;
        $this->configProviders = $configProviders;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->json = $json;
        $this->commandPool = $commandPool;
    }

    /**
     * Fetch a order object by transaction ID
     *
     * @param string $txnId
     * @param string $orderId
     * @return OrderInterface
     * @throws NoSuchEntityException
     */
    protected function getOrderByTransactionId($txnId, $orderId)
    {
        // Find the order
        $orderFilters[] = $this->filterBuilder
            ->setField('increment_id')
            ->setValue($orderId)
            ->create();

        $orderSearchCriteria = $this->searchCriteriaBuilder
            ->addFilters($orderFilters)
            ->create();

        $orders = $this->orderRepository
            ->getList($orderSearchCriteria)
            ->getItems();

        if (count($orders) < 1 || count($orders) > 1) {
            throw new NoSuchEntityException(__("Could not find order"));
        }

        $order = array_values($orders)[0];

        // Find the transaction
        $filters[] = $this->filterBuilder
            ->setField('txn_id')
            ->setValue($txnId)
            ->create();

        $filters[] = $this->filterBuilder
            ->setField('order_id')
            ->setValue((string) $order->getEntityId())
            ->create();

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilters($filters)
            ->create();

        $transactionsCount = $this->transactionRepository
            ->getList($searchCriteria)
            ->getTotalCount();

        if ($transactionsCount < 1 || $transactionsCount > 1) {
            throw new NoSuchEntityException(__("Could not find transaction"));
        }

        return $order;
    }

    /**
     * @param string $message
     * @param string $callable
     * @return void
     */
    protected function logWebHookRequest($message, $callable = 'info')
    {
        /* @var Http $request */
        $request = $this->getRequest();

        $logData = [
            'type' => static::LOG_TYPE,
        ];
        if ($callable == 'critical') {
            $logData['headers'] = $request->getHeaders()->toArray();
            $logData['data'] = $this->getData();
        }

        // @codingStandardsIgnoreLine
        call_user_func_array([$this->logger, $callable], [$message, $logData]);
    }

    /**
     * @return array
     * @throws InvalidArgumentException
     */
    protected function getData()
    {
        return $this->json->unserialize($this->getRequest()->getContent());
    }

    /**
     * Dispatch request
     *
     * @return ResultInterface|ResponseInterface
     * @throws Exception
     */
    public function execute()
    {
        /* @var Http $request */
        $request = $this->getRequest();

        $page = $this->rawFactory->create();

        $responseSecret = $request->getHeader(static::X_HEADER_SECRET);
        $responseAttempt = $request->getHeader(static::X_HEADER_ATTEMPT);
        $responseId = $request->getHeader(static::X_HEADER_ID);

        $data = $this->getData();

        try {
            if (!$request->isSecure()) {
                throw new LocalizedException(__("Failed - Connection not secure"));
            }

            if ($responseSecret === false) {
                throw new LocalizedException(__("Authorization not provided"));
            }

            if (!isset($data['transaction']) || !isset($data['transaction']['id'])) {
                throw new LocalizedException(__("Invalid data received (Transaction ID)"));
            }

            if (!isset($data['order']) || !isset($data['order']['id'])) {
                throw new LocalizedException(__("Invalid data received (Order ID)"));
            }

            $order = $this->getOrderByTransactionId($data['transaction']['id'], $data['order']['id']);

            $config = $this->configProviders[$order->getPayment()->getMethod()];

            $storeId = $order->getStoreId();

            if ($config->getWebhookSecret($storeId) !== $responseSecret) {
                throw new LocalizedException(__("Authorization failed"));
            }

            /** @var InfoInterface $payment */
            $payment = $order->getPayment();
            $paymentData = $this->paymentDataObjectFactory->create($payment);

            $this->commandPool
                ->get($config->getMethod())
                ->execute([
                    'payment' => $paymentData,
                    'transaction_id' => $data['transaction']['id'],
                    'order_id' => $data['order']['id']
                ]);
        } catch (Exception $e) {
            $errorMessage = sprintf(
                __("MasterCard Payment Gateway Services WebHook Exception: '%s'"),
                $e->getMessage()
            );
            $this->logWebHookRequest($errorMessage, 'critical');
            $page->setStatusHeader(400);
            return $page->setContents($e->getMessage());
        }

        $logger = $this->loggerFactory->create(['config' => $config]);
        $log = [
            'type' => static::LOG_TYPE,
            'attempt' => $responseAttempt,
            'id' => $responseId,
            'data' => $data,
        ];
        $logger->debug($log);

        $logMessage = __("MasterCard Payment Gateway Services WebHook Success");
        $this->logWebHookRequest($logMessage, 'info');

        $page->setStatusHeader(200);
        return $page->setContents('');
    }

    /**
     * Create exception in case CSRF validation failed.
     * Return null if default exception will suffice.
     *
     * @param RequestInterface $request
     *
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * Perform custom request validation.
     * Return null if default validation is needed.
     *
     * @param RequestInterface $request
     *
     * @return bool|null
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
