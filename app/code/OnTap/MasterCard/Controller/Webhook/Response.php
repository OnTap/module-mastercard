<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Controller\Webhook;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Payment\Model\Method\LoggerFactory;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Payment\Gateway\Command\CommandPoolInterface;

/**
 * Class Response
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Response extends \Magento\Framework\App\Action\Action
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
     * @var \Psr\Log\LoggerInterface
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
     * Response constructor.
     * @param Context $context
     * @param RawFactory $rawFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param TransactionRepositoryInterface $transactionRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param LoggerFactory $loggerFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param CommandPoolInterface $commandPool
     * @param \OnTap\MasterCard\Gateway\Config\Config[] $configProviders
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        RawFactory $rawFactory,
        OrderRepositoryInterface $orderRepository,
        TransactionRepositoryInterface $transactionRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        LoggerFactory $loggerFactory,
        \Psr\Log\LoggerInterface $logger,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        CommandPoolInterface $commandPool,
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
        $this->commandPool = $commandPool;
    }

    /**
     * Fetch a order object by transaction ID
     *
     * @param string $txnId
     * @param string $orderId
     * @return \Magento\Sales\Api\Data\OrderInterface
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
            ->setValue($order->getEntityId())
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
        /* @var \Magento\Framework\App\Request\Http $request */
        $request = $this->getRequest();

        $logData = [
            'type' => static::LOG_TYPE,
        ];
        if ($callable == 'critical') {
            $logData['headers'] = $request->getHeaders()->toArray();
            $logData['data'] = $this->getData();
        }

        call_user_func_array([$this->logger, $callable], [$message, $logData]);
    }

    /**
     * @return array
     * @throws \Zend_Json_Exception
     */
    protected function getData()
    {
        return \Zend_Json_Decoder::decode($this->getRequest()->getContent(), \Zend_Json::TYPE_ARRAY);
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        /* @var \Magento\Framework\App\Request\Http $request */
        $request = $this->getRequest();

        $page = $this->rawFactory->create();

        $responseSecret = $request->getHeader(static::X_HEADER_SECRET);
        $responseAttempt = $request->getHeader(static::X_HEADER_ATTEMPT);
        $responseId = $request->getHeader(static::X_HEADER_ID);

        $data = $this->getData();

        try {
            if (!$request->isSecure()) {
                throw new \Exception(__("Failed - Connection not secure"));
            }

            if ($responseSecret === false) {
                throw new \Exception(__("Authorization not provided"));
            }

            if (!isset($data['transaction']) || !isset($data['transaction']['id'])) {
                throw new \Exception(__("Invalid data received (Transaction ID)"));
            }

            if (!isset($data['order']) || !isset($data['order']['id'])) {
                throw new \Exception(__("Invalid data received (Order ID)"));
            }

            $order = $this->getOrderByTransactionId($data['transaction']['id'], $data['order']['id']);

            $config = $this->configProviders[$order->getPayment()->getMethod()];

            if ($config->getWebhookSecret() !== $responseSecret) {
                throw new \Exception(__("Authorization failed"));
            }

            $paymentData = $this->paymentDataObjectFactory->create($order->getPayment());

            $this->commandPool
                ->get($config->getMethod())
                ->execute([
                    'payment' => $paymentData,
                    'transaction_id' => $data['transaction']['id'],
                    'order_id' => $data['order']['id']
                ]);

        } catch (\Exception $e) {
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
}
