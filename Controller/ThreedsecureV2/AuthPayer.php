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

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Gateway\Command\CommandPool;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use OnTap\MasterCard\Model\Ui\Hpf\ConfigProvider;

class AuthPayer extends Action implements HttpPostActionInterface
{
    /**
     * @var PaymentDataObjectFactory
     */
    private $paymentDataObjectFactory;

    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var CommandPool
     */
    private $commandPool;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * Check constructor.
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param JsonFactory $jsonFactory
     * @param CommandPool $commandPool
     * @param Context $context
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        PaymentDataObjectFactory $paymentDataObjectFactory,
        JsonFactory $jsonFactory,
        CommandPool $commandPool,
        Context $context,
        OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($context);
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->jsonFactory = $jsonFactory;
        $this->commandPool = $commandPool;
        $this->orderRepository = $orderRepository;
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
        $jsonResult = $this->jsonFactory->create();
        $orderId = $this->getRequest()->getParam('order_id');
        // TODO extract from payment method additional information and put in response
        try {
            $order = $this->orderRepository->get($orderId);
            /** @var InfoInterface $payment */
            $payment = $order->getPayment();

            if ($payment->getMethod() !== ConfigProvider::METHOD_CODE) {
                throw new InputException(__('Payment method is invalid'));
            }

            $paymentDataObject = $this->paymentDataObjectFactory->create($payment);

            $this->commandPool
                ->get('auth_pay')
                ->execute([
                    'payment' => $paymentDataObject,
                    'browserDetails' => $this->getRequest()->getParam('browserDetails')
                ]);

            $order->getPayment()->save();

            // TODO take html code from payment additional info instead of response
            $jsonResult->setData([
                'html' => $payment->getAdditionalInformation('auth_redirect_html'),
                'action' => $payment->getAdditionalInformation('result') === 'PROCEED'
                    ? 'challenge'
                    : 'frictionless'
            ]);
        } catch (Exception $e) {
            // Todo handle error, cancel order and restore active quote
            $jsonResult
                ->setHttpResponseCode(400)
                ->setData([
                    'message' => $e->getMessage()
                ]);
        }

        return $jsonResult;
    }
}
