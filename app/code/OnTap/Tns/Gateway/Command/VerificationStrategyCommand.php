<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\Tns\Gateway\Command;

use Magento\Payment\Gateway\Command;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;

class VerificationStrategyCommand implements CommandInterface
{
    const VERIFY_AVS_CSC = 'verify';
    const VERIFY_3DSECURE = '3ds_enrollment';

    /**
     * @var Command\CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var string
     */
    private $successCommand;

    /**
     * @param Command\CommandPoolInterface $commandPool
     * @param ConfigInterface $config $config
     * @param string $successCommand
     */
    public function __construct(
        Command\CommandPoolInterface $commandPool,
        ConfigInterface $config,
        $successCommand = ''
    ) {
        $this->commandPool = $commandPool;
        $this->config = $config;
        $this->successCommand = $successCommand;
    }

    /**
     * Executes command basing on business object
     *
     * @param array $commandSubject
     * @return null|Command\ResultInterface
     */
    public function execute(array $commandSubject)
    {
        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = SubjectReader::readPayment($commandSubject);

        /** @var Payment $paymentInfo */
        $paymentInfo = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($paymentInfo);

        if (!$paymentInfo->getAuthorizationTransaction()) { // Only verify when auth transaction does not exist
            if (
                $this->config->getValue('avs') === '1' ||
                $this->config->getValue('csc_rules') === '1'
            ) {
                $this->commandPool
                    ->get(self::VERIFY_AVS_CSC)
                    ->execute($commandSubject);
            }
        }

        if ($paymentInfo->getIsFraudDetected()) {
            return null;
        }

        return $this->commandPool
            ->get($this->successCommand)
            ->execute($commandSubject);
    }
}
