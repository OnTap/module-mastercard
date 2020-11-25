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
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Gateway\Command\CommandPool;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;

class InitiateAuth extends Action
{
    /**
     * @var Session
     */
    private $checkoutSession;

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
     * Check constructor.
     * @param Session $checkoutSession
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param JsonFactory $jsonFactory
     * @param CommandPool $commandPool
     * @param Context $context
     */
    public function __construct(
        Session $checkoutSession,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        JsonFactory $jsonFactory,
        CommandPool $commandPool,
        Context $context
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->jsonFactory = $jsonFactory;
        $this->commandPool = $commandPool;
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
        $order = $this->checkoutSession->getLastRealOrder();
        // TODO extract from payment method additional information and put in response
        $jsonResult->setData([
            'order' => $this->checkoutSession->getLastRealOrder()->getId()
        ]);
        try {
            $paymentDataObject = $this->paymentDataObjectFactory->create($order->getPayment());

            $result = $this->commandPool
                ->get('init_auth')
                ->execute([
                    'payment' => $paymentDataObject
                ]);

            $response2 = $this->commandPool
                ->get('auth_pay')
                ->execute([
                    'payment' => $paymentDataObject,
                    'browserDetails' => $this->getRequest()->getParam('browserDetails')
                ]);

            $order->getPayment()->save();

            if ($result && $response2) {
                // TODO take html code from payment additional info instead of response
                $jsonResult->setData($response2->get()['response']['authentication']);
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
}
