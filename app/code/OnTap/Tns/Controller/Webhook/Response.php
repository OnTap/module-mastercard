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
     * @param array $configProviders
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
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $page = $this->rawFactory->create();

        /* @var \Magento\Framework\App\Request\Http $request */
        $request = $this->getRequest();
        $data = \Zend_Json_Decoder::decode($request->getContent(), \Zend_Json::TYPE_ARRAY);

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

        try {
            if (count($txns) < 1 || count($txns) > 1) {
                throw new NoSuchEntityException("Could not find transaction");
            }
        } catch (NoSuchEntityException $e) {
            $page->setStatusHeader(400);
            $page->setContents($e->getMessage());
        }

        /* @var \Magento\Sales\Api\Data\TransactionInterface $txn */
        $txn = array_values($txns)[0];

        $order = $this->orderRepository->get($txn->getOrderId());
        $payment = $order->getPayment();
        $config = $this->configProviders[$payment->getMethod()];

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
