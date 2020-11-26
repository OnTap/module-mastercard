<?php
/**
 * Copyright (c) 2016-2020 Mastercard
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

namespace OnTap\MasterCard\Controller\ThreedsecureV2;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Gateway\Command\CommandPool;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Payment\Operations\AuthorizeOperation;
use OnTap\MasterCard\Model\Service\GetOrderByIncrementId;

class Response extends Action implements CsrfAwareActionInterface
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var RawFactory
     */
    private $rawFactory;

    /**
     * @var CommandPool
     */
    private $commandPool;

    /**
     * @var PaymentDataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var AuthorizeOperation
     */
    private $authorizeOperation;

    /**
     * @var GetOrderByIncrementId
     */
    private $getOrderByIncrementId;

    /**
     * @var Context
     */
    private $context;

    /**
     * Response constructor.
     * @param Context $context
     * @param Session $session
     * @param RawFactory $rawFactory
     * @param CommandPool $commandPool
     * @param PaymentDataObjectFactory $dataObjectFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param AuthorizeOperation $authorizeOperation
     * @param GetOrderByIncrementId $getOrderByIncrementId
     */
    public function __construct(
        Context $context,
        Session $session,
        RawFactory $rawFactory,
        CommandPool $commandPool,
        PaymentDataObjectFactory $dataObjectFactory,
        OrderRepositoryInterface $orderRepository,
        AuthorizeOperation $authorizeOperation,
        GetOrderByIncrementId $getOrderByIncrementId
    ) {
        parent::__construct($context);
        $this->session = $session;
        $this->rawFactory = $rawFactory;
        $this->commandPool = $commandPool;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->orderRepository = $orderRepository;
        $this->authorizeOperation = $authorizeOperation;
        $this->getOrderByIncrementId = $getOrderByIncrementId;
        $this->context = $context;
    }

    /**
     * Dispatch request
     *
     * @return ResultInterface|ResponseInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');

        $order = $this->getOrderByIncrementId->execute($orderId);
        $payment = $order->getPayment();
        $totalDue = $order->getTotalDue();
        $baseTotalDue = $order->getBaseTotalDue();
        $this->authorizeOperation->authorize($payment, true, $baseTotalDue);
        $payment->setAmountAuthorized($totalDue);
        // TODO handle errors
        $this->orderRepository->save($order);

        $resultRaw = $this->rawFactory->create();
        return $resultRaw
            ->setContents("<script>console.log(window.parent); window.parent.treeDS2Completed()</script>");
    }

    /**
     * @inheritdoc
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
