<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Gateway\Command\Hosted;

use Magento\Payment\Gateway\Command;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;

class CaptureStrategyCommand implements CommandInterface
{
    const RETRIEVE_ORDER = 'retrieve_order';
    const CAPTURE = 'capture_simple';

    /**
     * @var Command\CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param Command\CommandPoolInterface $commandPool
     * @param ConfigInterface $config $config
     */
    public function __construct(
        Command\CommandPoolInterface $commandPool,
        ConfigInterface $config
    ) {
        $this->commandPool = $commandPool;
        $this->config = $config;
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

        if ($paymentInfo->getAuthorizationTransaction()) {
            return $this->commandPool
                ->get(static::CAPTURE)
                ->execute($commandSubject);
        }

        return $this->commandPool
            ->get(static::RETRIEVE_ORDER)
            ->execute($commandSubject);
    }
}
