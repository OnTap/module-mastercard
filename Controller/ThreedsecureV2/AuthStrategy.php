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
use Magento\Framework\UrlInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use OnTap\MasterCard\Model\Ui\Hpf\ConfigProvider;

class AuthStrategy extends Action implements HttpPostActionInterface
{
    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var UrlInterface
     */
    private $url;
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * Check constructor.
     * @param JsonFactory $jsonFactory
     * @param Context $context
     * @param OrderRepositoryInterface $orderRepository
     * @param UrlInterface $url
     */
    public function __construct(
        JsonFactory $jsonFactory,
        Context $context,
        OrderRepositoryInterface $orderRepository,
        UrlInterface $url
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->orderRepository = $orderRepository;
        $this->url = $url;
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
        $orderId = (int)$this->getRequest()->getParam('order_id');

        $jsonResult = $this->jsonFactory->create();
        try {
            $order = $this->orderRepository->get($orderId);

            $payment = $order->getPayment();

            if ($payment->getMethod() !== ConfigProvider::METHOD_CODE) {
                throw new InputException(__('Payment method is invalid'));
            }

            if ($this->isNone3DSSupportedMethods($order)) {
                $jsonResult->setData([
                    'version' => 'NONE',
                    'action' => 'redirectOnSuccess'
                ]);
            }
        } catch (Exception $e) {
            $jsonResult
                ->setHttpResponseCode(400)
                ->setData([
                    'message' => $e->getMessage()
                ]);
        }

        return $jsonResult;
    }

    /**
     * @param OrderInterface $order
     * @return bool
     */
    private function isNone3DSSupportedMethods(OrderInterface $order): bool
    {
        $payment = $order->getPayment();

        $paymentInformation = $payment->getAdditionalInformation();
        $authVersion = $paymentInformation['auth_version'] ?? '';
        $gatewayRecommendation = $paymentInformation['response_gateway_recommendation'] ?? '';
        if ($authVersion !== 'NONE' || $gatewayRecommendation !== 'PROCEED') {
            return false;
        }

        if ($order->getState() !== Order::STATE_PROCESSING) {
            return false;
        }

        return true;
    }
}
