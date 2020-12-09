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

declare(strict_types=1);

namespace OnTap\MasterCard\Controller\ThreedsecureV2;

use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Payment\Gateway\Command\CommandPool;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Psr\Log\LoggerInterface;

class AuthenticatePayer extends Action implements HttpPostActionInterface
{
    const COMMAND_NAME = 'authenticate_payer';

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
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Check constructor.
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param JsonFactory $jsonFactory
     * @param CommandPool $commandPool
     * @param Context $context
     * @param Session $checkoutSession
     * @param LoggerInterface $logger
     */
    public function __construct(
        PaymentDataObjectFactory $paymentDataObjectFactory,
        JsonFactory $jsonFactory,
        CommandPool $commandPool,
        Context $context,
        Session $checkoutSession,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->jsonFactory = $jsonFactory;
        $this->commandPool = $commandPool;
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
    }

    /**
     * Dispatch request
     *
     * @return ResultInterface|ResponseInterface
     */
    public function execute()
    {
        $jsonResult = $this->jsonFactory->create();

        try {
            $quote = $this->checkoutSession->getQuote();
            $payment = $quote->getPayment();
            $paymentDataObject = $this->paymentDataObjectFactory->create($payment);

            $this->commandPool
                ->get(self::COMMAND_NAME)
                ->execute([
                    'payment' => $paymentDataObject,
                    'amount' => $quote->getBaseGrandTotal(),
                    'remote_ip' => $quote->getRemoteIp(),
                    'browserDetails' => $this->getRequest()->getParam('browserDetails')
                ]);

            $payment->save();

            $jsonResult->setData([
                'html' => $payment->getAdditionalInformation('auth_redirect_html'),
                'action' => $payment->getAdditionalInformation('auth_payment_interaction') === 'REQUIRED'
                    ? 'challenge'
                    : 'frictionless'
            ]);
        } catch (Exception $e) {
            $this->logger->warning((string)$e);
            $jsonResult
                ->setHttpResponseCode(400)
                ->setData([
                    'message' => __('Transaction declined')
                ]);
        }

        return $jsonResult;
    }
}
