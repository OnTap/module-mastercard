<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Gateway\Command;

use Magento\Payment\Gateway\Command;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Sales\Model\Order;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;

class SaleStrategyCommand implements CommandInterface
{
    const PRE_AUTH_CAPTURE = 'pre_auth_capture';
    const SALE = 'sale';

    /**
     * @var Command\CommandPoolInterface
     */
    private $commandPool;

    /**
     * @param Command\CommandPoolInterface $commandPool
     */
    public function __construct(
        Command\CommandPoolInterface $commandPool
    ) {
        $this->commandPool = $commandPool;
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

        /** @var Order\Payment $paymentInfo */
        $paymentInfo = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($paymentInfo);

        if (
            $paymentInfo instanceof Order\Payment
            && $paymentInfo->getAuthorizationTransaction()
        ) {
            // Capture an already authorized payment
            return $this->commandPool
                ->get(self::PRE_AUTH_CAPTURE)
                ->execute($commandSubject);
        }

        // Perform a auth+capture with a single call
        return $this->commandPool
            ->get(self::SALE)
            ->execute($commandSubject);
    }
}
