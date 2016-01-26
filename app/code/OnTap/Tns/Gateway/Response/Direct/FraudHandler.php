<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\Tns\Gateway\Response\Direct;

use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order\Payment;
use Magento\Payment\Gateway\Helper\ContextHelper;
use OnTap\Tns\Gateway\Validator\Direct\CscResponseValidatorFactory;
use OnTap\Tns\Gateway\Validator\Direct\CscResponseValidator;
use OnTap\Tns\Model\Adminhtml\Source\ValidatorBehaviour;

class FraudHandler implements HandlerInterface
{
    /**
     * @var CscResponseValidator
     */
    protected $validator;

    /**
     * FraudHandler constructor.
     * @param CscResponseValidatorFactory $validator
     */
    public function __construct(CscResponseValidatorFactory $validator)
    {
        $this->validator = $validator->create();
    }

    /**
     * Handles response
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = SubjectReader::readPayment($handlingSubject);

        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);

        $payment->setTransactionId($response['transaction']['id']);
        $payment->setIsTransactionClosed(false);

        if ($this->validator->validateGatewayCode($response, ValidatorBehaviour::FRAUD)) {
            $payment->setIsFraudDetected(true);
            $payment->setIsTransactionPending(true);
        }
    }
}
