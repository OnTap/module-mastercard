<?php
/**
 * Copyright (c) 2016-2019 Mastercard
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

namespace OnTap\MasterCard\Controller\Threedsecure;

use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Payment\Gateway\Command\CommandPoolFactory;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use OnTap\MasterCard\Gateway\Response\ThreeDSecure\CheckHandler;

/**
 * Class Check
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Check extends Action
{
    const CHECK_ENROLMENT = '3ds_enrollment';
    const CHECK_ENROLMENT_TYPE_DIRECT = 'TnsThreeDSecureEnrollmentCommand';
    const CHECK_ENROLMENT_TYPE_HPF = 'TnsHpfThreeDSecureEnrollmentCommand';

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
     * @var CommandPoolFactory
     */
    private $commandPoolFactory;

    /**
     * Check constructor.
     * @param CommandPoolFactory $commandPoolFactory
     * @param Session $checkoutSession
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param JsonFactory $jsonFactory
     * @param Context $context
     */
    public function __construct(
        CommandPoolFactory $commandPoolFactory,
        Session $checkoutSession,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        JsonFactory $jsonFactory,
        Context $context
    ) {
        parent::__construct($context);
        $this->commandPoolFactory = $commandPoolFactory;
        $this->checkoutSession = $checkoutSession;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->jsonFactory = $jsonFactory;
    }

    /**
     * Dispatch request
     *
     * @return ResultInterface|ResponseInterface
     * @throws NotFoundException
     */
    public function execute()
    {
        $quote = $this->checkoutSession->getQuote();
        $jsonResult = $this->jsonFactory->create();
        try {
            // @todo: Commands require specific config, so they need to be defined separately in the di.xml
            $commandPool = $this->commandPoolFactory->create([
                'commands' => [
                    'hpf' => static::CHECK_ENROLMENT_TYPE_HPF,
                    'direct' => static::CHECK_ENROLMENT_TYPE_DIRECT,
                ]
            ]);

            $paymentDataObject = $this->paymentDataObjectFactory->create($quote->getPayment());

            $commandPool
                ->get($this->getRequest()->getParam('method'))
                ->execute([
                    'payment' => $paymentDataObject,
                    'amount' => $quote->getBaseGrandTotal(),
                ]);

            $checkData = $paymentDataObject
                ->getPayment()
                ->getAdditionalInformation(CheckHandler::THREEDSECURE_CHECK);

            $jsonResult->setData([
                'result' => $checkData['veResEnrolled']
            ]);
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
