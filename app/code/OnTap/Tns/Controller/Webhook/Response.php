<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\Tns\Controller\Webhook;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Payment\Model\Method\LoggerFactory;
use OnTap\Tns\Gateway\Response\PaymentHandler;

/**
 * Class Response
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Response extends \Magento\Framework\App\Action\Action
{
    const X_HEADER_SECRET = 'X-Notification-Secret';

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
     * Response constructor.
     * @param Context $context
     * @param RawFactory $rawFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param TransactionRepositoryInterface $transactionRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param LoggerFactory $loggerFactory
     * @param \OnTap\Tns\Gateway\Config\Config[] $configProviders
     */
    public function __construct(
        Context $context,
        RawFactory $rawFactory,
        OrderRepositoryInterface $orderRepository,
        TransactionRepositoryInterface $transactionRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        LoggerFactory $loggerFactory,
        array $configProviders = []
    ) {
        parent::__construct($context);
        $this->rawFactory = $rawFactory;
        $this->orderRepository = $orderRepository;
        $this->transactionRepository = $transactionRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->loggerFactory = $loggerFactory;
        $this->configProviders = $configProviders;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Exception
     */
    public function execute()
    {
        // @todo: implement tns_webhook.log log for generic logging

        /* @var \Magento\Framework\App\Request\Http $request */
        $request = $this->getRequest();

        $page = $this->rawFactory->create();

        $data = \Zend_Json_Decoder::decode($request->getContent(), \Zend_Json::TYPE_ARRAY);

        try {
            $responseSecret = $request->getHeader(static::X_HEADER_SECRET);
            if ($responseSecret === false) {
                throw new \Exception(__("Not authorized"));
            }

            if (!isset($data['transaction']) || !isset($data['transaction']['id'])) {
                throw new \Exception(__("Invalid data received"));
            }

            $filters[] = $this->filterBuilder
                ->setField('txn_id')
                ->setValue($data['transaction']['id'])
                ->create();

            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilters($filters)
                ->create();

            $txns = $this->transactionRepository
                ->getList($searchCriteria)
                ->getItems();

            if (count($txns) < 1 || count($txns) > 1) {
                throw new NoSuchEntityException(__("Could not find transaction"));
            }

            /* @var \Magento\Sales\Api\Data\TransactionInterface $txn */
            $txn = array_values($txns)[0];

            $order = $this->orderRepository->get($txn->getOrderId());
            $payment = $order->getPayment();

            $config = $this->configProviders[$payment->getMethod()];

            if ($config->getWebhookSecret() !== $responseSecret) {
                throw new \Exception(__("Authorisation failed"));
            }
        } catch (\Exception $e) {
            $page->setStatusHeader(400);
            return $page->setContents($e->getMessage());
        }

        PaymentHandler::importPaymentResponse($payment, $data);

        /* @var \Magento\Sales\Model\Order $order */
        $order->addStatusHistoryComment(__("Order updated by gateway"));
        $order->save();

        $logger = $this->loggerFactory->create(['config' => $config]);
        $log = [
            'type' => 'webhook',
            'data' => $data,
        ];
        $logger->debug($log);

        return $page->setContents('');
    }
}
