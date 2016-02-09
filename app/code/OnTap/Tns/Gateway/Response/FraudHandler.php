<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\Tns\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order\Payment;
use Magento\Payment\Gateway\Helper\ContextHelper;
use OnTap\Tns\Model\Adminhtml\Source\ValidatorBehaviour;
use OnTap\Tns\Gateway\Validator\CscResponseValidatorFactory;
use OnTap\Tns\Gateway\Validator\CscResponseValidator;
use OnTap\Tns\Gateway\Validator\AvsResponseValidatorFactory;
use OnTap\Tns\Gateway\Validator\AvsResponseValidator;
use Magento\Payment\Gateway\ConfigInterface;

class FraudHandler implements HandlerInterface
{
    /**
     * @var CscResponseValidator
     */
    protected $cscValidator;

    /**
     * @var AvsResponseValidator
     */
    protected $avsValidator;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * FraudHandler constructor.
     * @param ConfigInterface $config
     * @param CscResponseValidatorFactory $cscResponseValidatorFactory
     * @param AvsResponseValidatorFactory $avsResponseValidatorFactory
     */
    public function __construct(
        ConfigInterface $config,
        CscResponseValidatorFactory $cscResponseValidatorFactory,
        AvsResponseValidatorFactory $avsResponseValidatorFactory
    ) {
        $this->config = $config;
        $this->cscValidator = $cscResponseValidatorFactory->create([
            'config' => $this->config
        ]);
        $this->avsValidator = $avsResponseValidatorFactory->create([
            'config' => $this->config
        ]);
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

        if ($this->config->getValue('csc_rules') === '1') {
            if ($this->cscValidator->validateGatewayCode($response, ValidatorBehaviour::FRAUD)) {
                $payment->setIsFraudDetected(true);
                $payment->setIsTransactionPending(true);
            }
        }

        if ($this->config->getValue('avs') === '1') {
            if ($this->avsValidator->validateGatewayCode($response, ValidatorBehaviour::FRAUD)) {
                $payment->setIsFraudDetected(true);
                $payment->setIsTransactionPending(true);
            }
        }
    }
}
