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
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Model\Method\AbstractMethod;

class ReviewStrategyCommand implements CommandInterface
{
    const CAPTURE = 'capture_simple';
    const AUTHORIZE = 'authorize_simple';

    /**
     * @var Command\CommandPoolInterface
     */
    protected $commandPool;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var string
     */
    protected $solution;

    /**
     * @param Command\CommandPoolInterface $commandPool
     * @param ConfigInterface $config
     * @param string $solution
     */
    public function __construct(
        Command\CommandPoolInterface $commandPool,
        ConfigInterface $config,
        $solution = ''
    ) {
        $this->commandPool = $commandPool;
        $this->config = $config;
        $this->solution = $solution;
    }

    /**
     * Executes command basing on business object
     *
     * @param array $commandSubject
     * @return null|Command\ResultInterface
     */
    public function execute(array $commandSubject)
    {
        $paymentDO = SubjectReader::readPayment($commandSubject);

        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);
        $commandSubject['amount'] = $payment->getAmountOrdered();

        return $this->{$this->solution}($commandSubject);
    }

    /**
     * @param array $commandSubject
     * @return Command\ResultInterface|null
     */
    protected function accept(array $commandSubject)
    {
        if ($this->config->getValue('payment_action') == AbstractMethod::ACTION_AUTHORIZE) {

            return $this->commandPool
                ->get(self::AUTHORIZE)
                ->execute($commandSubject);

        } else if ($this->config->getValue('payment_action') == AbstractMethod::ACTION_AUTHORIZE_CAPTURE) {

            return $this->commandPool
                ->get(self::CAPTURE)
                ->execute($commandSubject);
        }
    }

    /**
     * @param array $commandSubject
     * @return null
     */
    protected function deny(array $commandSubject)
    {
        $paymentDO = SubjectReader::readPayment($commandSubject);

        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        $payment->setIsTransactionClosed(true);

        return null;
    }
}
